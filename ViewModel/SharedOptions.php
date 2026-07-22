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
namespace BluePrint3D\SharedProductOptions\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
// Swapped out the old ghost collection factory for your real surviving entry factory
use BluePrint3D\SharedProductOptions\Model\ResourceModel\Option\CollectionFactory as OptionCollectionFactory;

class SharedOptions implements ArgumentInterface
{
    protected $optionCollectionFactory;

    public function __construct(OptionCollectionFactory $optionCollectionFactory) {
        $this->optionCollectionFactory = $optionCollectionFactory;
    }

    /**
     * Fetch complete tree structures for multiple Option Groups at once
     */
    public function getOptionsByGroupIds(array $groupIds): array
    {
        if (empty($groupIds)) {
            return [];
        }

        // 1. Fetch all top-level parent options across ALL assigned groups at once
        $optionCollection = $this->optionCollectionFactory->create();
        $optionCollection->addFieldToFilter('group_id', ['in' => $groupIds])
            ->addFieldToFilter('parent_id', 0)
            ->setOrder('sort_order', 'ASC');

        $result = [];
        foreach ($optionCollection as $option) {
            $optionId = (int)$option->getId();
            $type = $option->getType();

            $optionData = [
                'id'           => $optionId,
                'group_id'     => (int)$option->getGroupId(),
                'type'         => $type,
                'title'        => $option->getTitle(),
                'price'        => (float)$option->getPriceModifier(),
                'sku'          => $option->getSkuModifier(),
                'is_required'  => (bool)$option->getIsRequired(),
                'values'       => []
            ];

            // 2. If it's a dropdown or multi-select component, query its child selection options
            if (in_array($type, ['drop_down', 'multiple'])) {
                $subCollection = $this->optionCollectionFactory->create();
                $subCollection->addFieldToFilter('parent_id', $optionId)
                    ->setOrder('sort_order', 'ASC');

                foreach ($subCollection as $subRow) {
                    $optionData['values'][] = [
                        'id'    => (int)$subRow->getId(),
                        'title' => $subRow->getTitle(),
                        'price' => (float)$subRow->getPriceModifier()
                    ];
                }
            }
            $result[] = $optionData;
        }
        return $result;
    }
}