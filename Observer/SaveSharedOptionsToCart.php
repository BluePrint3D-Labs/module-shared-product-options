<?php
/**
 * Copyright (c) 2026 BluePrint3D Ltd. All rights reserved.
 * 
 * This software is provided free of charge for personal or commercial use.
 * Resale, redistribution, or sublicensing of this source code, modified or 
 * unmodified, for direct financial gain is strictly prohibited.
 *
 * @author    BluePrint3D Ltd <support@blueprint3d.dev>
 * @copyright 2026 BluePrint3D Ltd (Company No. 13473806)
 * @license   Custom Proprietary EULA (See LICENSE.txt)
 */
namespace BluePrint3D\SharedProductOptions\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\App\RequestInterface;
use Psr\Log\LoggerInterface;
// Swapped to your real Option row factory
use BluePrint3D\SharedProductOptions\Model\OptionFactory;
// Added junction table factory to check for active product mappings
use BluePrint3D\SharedProductOptions\Model\ResourceModel\SharedOptionProduct\CollectionFactory as LinkCollectionFactory;

class SaveSharedOptionsToCart implements ObserverInterface
{
    /** @var Json */
    protected $serializer;

    /** @var RequestInterface */
    protected $request;

    /** @var LoggerInterface */
    protected $logger;

    /** @var OptionFactory */
    protected $optionFactory;

    /** @var LinkCollectionFactory */
    protected $linkCollectionFactory;

    public function __construct(
        Json $serializer,
        RequestInterface $request,
        LoggerInterface $logger,
        OptionFactory $optionFactory,
        LinkCollectionFactory $linkCollectionFactory
    ) {
        $this->serializer = $serializer;
        $this->request = $request;
        $this->logger = $logger;
        $this->optionFactory = $optionFactory;
        $this->linkCollectionFactory = $linkCollectionFactory;
    }

    public function execute(Observer $observer)
    {
        $this->logger->info('=== SaveSharedOptionsToCart START ===');

        /** @var \Magento\Quote\Model\Quote\Item $quoteItem */
        $quoteItem = $observer->getEvent()->getQuoteItem();
        if (!$quoteItem || !$quoteItem->getProduct()) {
            return;
        }

        // Deal with configurable child/parent mapping layers
        if ($quoteItem->getParentItem()) {
            $quoteItem = $quoteItem->getParentItem();
        }

        $product = $quoteItem->getProduct();

        // CHECK MAPPINGS: Ensure this product has at least one mapped Shared Option Group
        $linkCollection = $this->linkCollectionFactory->create()
            ->addFieldToFilter('product_id', (int)$product->getId());

        if ($linkCollection->getSize() === 0) {
            return; // No shared options assigned to this product, ignore.
        }

        $sharedOptionsData = $this->request->getPost('shared_options', []);
        if (empty($sharedOptionsData)) {
            return;
        }

        // Filter out empty options
        $sharedOptionsData = array_filter($sharedOptionsData, function($value) {
            return $value !== '' && $value !== null;
        });

        if (empty($sharedOptionsData)) {
            return;
        }

        try {
            // ----------------------------------------------------
            // 1. SAVE RAW DATA (For your custom block structures)
            // ----------------------------------------------------
            $serializedData = $this->serializer->serialize($sharedOptionsData);

            $customOption = $quoteItem->getOptionByCode('blueprint3d_shared_options');
            if (!$customOption) {
                $customOption = \Magento\Framework\App\ObjectManager::getInstance()
                    ->create(\Magento\Quote\Model\Quote\Item\Option::class);
                $customOption->setCode('blueprint3d_shared_options');
                $customOption->setProduct($quoteItem->getProduct());
            }
            $customOption->setValue($serializedData);
            $quoteItem->addOption($customOption);
            $quoteItem->setData('blueprint3d_shared_options', $serializedData);

            // ----------------------------------------------------
            // 2. FORMAT AND SAVE FOR NATIVE CHECKOUT DISPLAY
            // ----------------------------------------------------
            $nativeCheckoutOptions = [];
            $additionalPrice = 0.00;

            foreach ($sharedOptionsData as $optionId => $value) {
                if (empty($value)) {
                    continue;
                }

                // Load the Parent Option using your true OptionFactory
                $parentOption = $this->optionFactory->create()->load($optionId);
                if (!$parentOption->getId()) {
                    continue;
                }

                $displayValue = $value;

                // Handle select strings labels natively using database structure
                if (in_array($parentOption->getType(), ['drop_down', 'multiple'])) {
                    // Normalize singular selections and multi arrays into a clean list loop
                    $valueArray = is_array($value) ? $value : [$value];
                    $labels = [];

                    foreach ($valueArray as $childValueId) {
                        if (empty($childValueId)) {
                            continue;
                        }

                        // Load the specific child row choice using OptionFactory
                        $childOption = $this->optionFactory->create()->load($childValueId);
                        if ($childOption->getId()) {
                            $labels[] = $childOption->getTitle();

                            // 3. APPLY PRICING ADJUSTMENTS (Dropdowns/Multi-selects)
                            $childPrice = (float)$childOption->getPriceModifier();
                            if ($childPrice > 0) {
                                $additionalPrice += $childPrice;
                            }
                        }
                    }
                    $displayValue = implode(', ', $labels);
                } else {
                    // 3. APPLY PRICING ADJUSTMENTS (Fields, Areas, Dates)
                    $parentPrice = (float)$parentOption->getPriceModifier();
                    if ($parentPrice > 0) {
                        $additionalPrice += $parentPrice;
                    }
                }

                // Add to standard text dictionary for checkout summaries
                if (!empty($displayValue)) {
                    $nativeCheckoutOptions[] = [
                        'label' => $parentOption->getTitle(),
                        'value' => $displayValue
                    ];
                }
            }

            // Bind the text strings to Magento's native checkout layout option hook
            if (!empty($nativeCheckoutOptions)) {
                $checkoutOption = $quoteItem->getOptionByCode('additional_options');
                if (!$checkoutOption) {
                    $checkoutOption = \Magento\Framework\App\ObjectManager::getInstance()
                        ->create(\Magento\Quote\Model\Quote\Item\Option::class);
                    $checkoutOption->setCode('additional_options');
                    $checkoutOption->setProduct($quoteItem->getProduct());
                }
                $checkoutOption->setValue($this->serializer->serialize($nativeCheckoutOptions));
                $quoteItem->addOption($checkoutOption);
            }

            // Finalize structural custom price modifications natively
            if ($additionalPrice > 0) {
                $originalPrice = (float)$product->getFinalPrice();
                $newPrice = $originalPrice + $additionalPrice;

                $quoteItem->setCustomPrice($newPrice);
                $quoteItem->setOriginalCustomPrice($newPrice);
                $quoteItem->getProduct()->setIsSuperMode(true);
            }

            // Flag item as requiring save validation triggers
            $quoteItem->setIsOptionsSaved(false);

            $this->logger->info('Successfully stored raw options, native checkout options, and prices!');

        } catch (\Exception $e) {
            $this->logger->error('Exception in Unified SaveSharedOptionsToCart: ' . $e->getMessage());
        }
    }
}