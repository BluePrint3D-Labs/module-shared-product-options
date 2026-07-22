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
namespace BluePrint3D\SharedProductOptions\Block\Product\View;

use Magento\Framework\View\Element\Template;
use Magento\Catalog\Block\Product\Context;
// Imported your true surviving entry option factory
use BluePrint3D\SharedProductOptions\Model\ResourceModel\Option\CollectionFactory as OptionCollectionFactory;
// Imported your many-to-many junction table factory
use BluePrint3D\SharedProductOptions\Model\ResourceModel\SharedOptionProduct\CollectionFactory as LinkCollectionFactory;

class SharedOptions extends Template
{
    /**
     * @var OptionCollectionFactory
     */
    protected $optionCollectionFactory;

    /**
     * @var LinkCollectionFactory
     */
    protected $linkCollectionFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @param Context $context
     * @param OptionCollectionFactory $optionCollectionFactory
     * @param LinkCollectionFactory $linkCollectionFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        OptionCollectionFactory $optionCollectionFactory,
        LinkCollectionFactory $linkCollectionFactory,
        array $data = []
    ) {
        $this->optionCollectionFactory = $optionCollectionFactory;
        $this->linkCollectionFactory = $linkCollectionFactory;
        $this->coreRegistry = $context->getRegistry();
        parent::__construct($context, $data);
    }

    /**
     * Fetch active context registry product
     *
     * @return \Magento\Catalog\Model\Product|null
     */
    public function getProduct()
    {
        return $this->coreRegistry->registry('product');
    }

    /**
     * Fetch top level structural fields across all assigned groups
     *
     * @return array|\Magento\Framework\DataObject[]
     */
    public function getSharedOptions()
    {
        $product = $this->getProduct();
        if (!$product || !$product->getId()) {
            return [];
        }

        // 1. Extract all mapping records linking this product to option groups
        $linkCollection = $this->linkCollectionFactory->create();
        $linkCollection->addFieldToFilter('product_id', (int)$product->getId());

        if ($linkCollection->getSize() === 0) {
            return [];
        }

        // 2. Map out group IDs into an array loop
        $groupIds = [];
        foreach ($linkCollection as $link) {
            $groupIds[] = (int)$link->getGroupId();
        }

        if (empty($groupIds)) {
            return [];
        }

        // 3. Query parent rows across all assigned groups simultaneously
        return $this->optionCollectionFactory->create()
            ->addFieldToFilter('group_id', ['in' => $groupIds])
            ->addFieldToFilter('parent_id', 0)
            ->setOrder('sort_order', 'ASC');
    }

    /**
     * Fetch children datasets for sub-selections
     *
     * @param int $parentId
     * @return \BluePrint3D\SharedProductOptions\Model\ResourceModel\Option\Collection
     */
    public function getChildValues($parentId)
    {
        return $this->optionCollectionFactory->create()
            ->addFieldToFilter('parent_id', (int)$parentId)
            ->setOrder('sort_order', 'ASC');
    }
}