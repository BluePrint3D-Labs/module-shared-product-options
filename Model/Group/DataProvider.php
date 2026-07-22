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
namespace BluePrint3D\SharedProductOptions\Model\Group;

use Magento\Ui\DataProvider\AbstractDataProvider;
use BluePrint3D\SharedProductOptions\Model\ResourceModel\Group\CollectionFactory;
// Updated reference to use your true surviving row collection factory
use BluePrint3D\SharedProductOptions\Model\ResourceModel\Option\CollectionFactory as OptionCollectionFactory;

class DataProvider extends AbstractDataProvider
{
    protected $loadedData;
    protected $optionCollectionFactory;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        OptionCollectionFactory $optionCollectionFactory,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        $this->optionCollectionFactory = $optionCollectionFactory;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }

        $this->loadedData = [];
        $items = $this->collection->getItems();

        foreach ($items as $item) {
            $groupId = $item->getId();

            // Load master option rows belonging to this group using our clean collection
            $optionCollection = $this->optionCollectionFactory->create();
            $optionCollection->addFieldToFilter('group_id', $groupId)
                ->addFieldToFilter('parent_id', 0)
                ->setOrder('sort_order', 'ASC');

            $optionRows = [];
            foreach ($optionCollection as $option) {
                $optionId = $option->getId();
                $optionType = $option->getData('type') ?: 'field';

                $optionData = [
                    'item_id'     => (int)$optionId,
                    'type'        => $optionType,
                    'title'       => $option->getData('title'),
                    'price'       => $option->getData('price_modifier'),
                    'sku'         => $option->getData('sku_modifier'),
                    'position'    => (int)$option->getData('sort_order'),
                    'is_required' => (int)$option->getData('is_required'),
                    'placeholder' => $option->getData('placeholder'), // <-- THE FIX: Loads database value into UI component
                    'values'      => []
                ];

                // Query sub-options for both dropdown lists and multi-select fields
                if (in_array($optionType, ['drop_down', 'multiple'])) {
                    $subCollection = $this->optionCollectionFactory->create();
                    $subCollection->addFieldToFilter('parent_id', $optionId)
                        ->setOrder('sort_order', 'ASC');

                    $subRows = [];
                    foreach ($subCollection as $subRow) {
                        $subRows[] = [
                            'value_id' => (int)$subRow->getId(),
                            'title'    => $subRow->getData('title'),
                            'price'    => $subRow->getData('price_modifier')
                        ];
                    }
                    // Retain specific nest format so layout engine matches your configuration layout requirements
                    $optionData['values'] = ['values' => $subRows];
                }

                $optionRows[] = $optionData;
            }

            // Bind the formatted data back to the UI component mapping
            $this->loadedData[$groupId] = [
                'group_id' => $groupId,
                'title'    => $item->getData('title'),
                'general'  => [
                    'group_id' => $groupId,
                    'title'    => $item->getData('title')
                ],
                'option_values' => $optionRows
            ];
        }

        return $this->loadedData;
    }
}