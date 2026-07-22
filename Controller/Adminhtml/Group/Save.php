<?php
/**
 * Copyright (c) 2026 BluePrint3D Ltd. All rights reserved.
 *
 * This software is provided free of charge for personal or commercial use.
 * Resale, redistribution, or sublicensing of this source code, modified or
 * unmodified, for direct financial gain is strictly prohibited.
 *
 * @author    BluePrint3D Ltd <support@blueprint3d.co.uk>
 * @copyright 2026 BluePrint3D Ltd (Company No. 13473806)
 * @license   Custom Proprietary EULA (See LICENSE.txt)
 */
namespace BluePrint3D\SharedProductOptions\Controller\Adminhtml\Group;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use BluePrint3D\SharedProductOptions\Model\GroupFactory;
use BluePrint3D\SharedProductOptions\Model\ResourceModel\Group\CollectionFactory as GroupCollectionFactory;
use BluePrint3D\SharedProductOptions\Model\OptionFactory;
use BluePrint3D\SharedProductOptions\Model\ResourceModel\Option\CollectionFactory as OptionCollectionFactory;

class Save extends Action
{
    const ADMIN_RESOURCE = 'BluePrint3D_SharedProductOptions::group';

    protected $groupFactory;
    protected $groupCollectionFactory;
    protected $optionFactory;
    protected $optionCollectionFactory;

    public function __construct(
        Context $context,
        GroupFactory $groupFactory,
        GroupCollectionFactory $groupCollectionFactory,
        OptionFactory $optionFactory,
        OptionCollectionFactory $optionCollectionFactory
    ) {
        parent::__construct($context);
        $this->groupFactory = $groupFactory;
        $this->groupCollectionFactory = $groupCollectionFactory;
        $this->optionFactory = $optionFactory;
        $this->optionCollectionFactory = $optionCollectionFactory;
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();

        if ($data) {
            $model = $this->groupFactory->create();
            $id = $this->getRequest()->getParam('group_id');

            if (!empty($data['group_id']) && is_numeric($data['group_id']) && $data['group_id'] > 0) {
                $id = $data['group_id'];
                $model->load($id);
            } else {
                unset($data['group_id']);
            }

            // --- FREE TIER LIMITATION CHECK ---
            // If creating a NEW group (not editing an existing group), enforce the 6-group limit.
            if (!$id) {
                $groupCollection = $this->groupCollectionFactory->create();
                if ($groupCollection->getSize() >= 6) {
                    $this->messageManager->addErrorMessage(
                        __('Free Version Limit Reached: You can create up to 6 shared option groups in the free edition. Upgrade to BluePrint3D Shared Options Pro to create unlimited groups.')
                    );
                    return $resultRedirect->setPath('*/*/');
                }
            }

            // Map base group fields safely
            $model->setData('title', !empty($data['title']) ? $data['title'] : '');

            try {
                $model->save();
                $groupId = $model->getId();

                if (isset($data['option_values']) && is_array($data['option_values'])) {

                    // Clear existing rows out using our clean collection
                    $existingItems = $this->optionCollectionFactory->create()->addFieldToFilter('group_id', $groupId);
                    foreach ($existingItems as $existingItem) {
                        $existingItem->delete();
                    }

                    usort($data['option_values'], function ($a, $b) {
                        return ((int)($a['position'] ?? 0)) <=> ((int)($b['position'] ?? 0));
                    });

                    $position = 0;

                    foreach ($data['option_values'] as $row) {
                        if (empty($row['title'])) {
                            continue;
                        }

                        $optionType = !empty($row['type']) ? $row['type'] : 'field';

                        // Save option row container using OptionFactory
                        $optionModel = $this->optionFactory->create();
                        $optionModel->setData([
                            'group_id'       => $groupId,
                            'parent_id'      => 0,
                            'type'           => $optionType,
                            'title'          => $row['title'],
                            'price_modifier' => !empty($row['price']) ? (float)$row['price'] : 0.0000,
                            'sku_modifier'   => !empty($row['sku']) ? $row['sku'] : null,
                            'is_required'    => !empty($row['is_required']) ? (int)$row['is_required'] : 0,
                            'placeholder'    => !empty($row['placeholder']) ? $row['placeholder'] : null,
                            'sort_order'     => $position++
                        ]);
                        $optionModel->save();
                        $parentOptionId = $optionModel->getId();

                        // FIX DOUBLE-NESTED ARRAY STRUCTURE
                        $subValues = [];
                        if (isset($row['values']['values']) && is_array($row['values']['values'])) {
                            $subValues = $row['values']['values'];
                        } elseif (isset($row['values']) && is_array($row['values'])) {
                            $subValues = $row['values'];
                        }

                        // Expanded processing logic to support both dropdowns and multi-select sub-options
                        if (in_array($optionType, ['drop_down', 'multiple']) && !empty($subValues)) {
                            $subPosition = 0;
                            foreach ($subValues as $valueRow) {
                                if (empty($valueRow['title'])) {
                                    continue;
                                }

                                // Sets matching option values type mapping to match parent variant grouping
                                $childType = ($optionType === 'multiple') ? 'multiple_value' : 'drop_down_value';

                                $valueModel = $this->optionFactory->create();
                                $valueModel->setData([
                                    'group_id'       => $groupId,
                                    'parent_id'      => $parentOptionId,
                                    'type'           => $childType,
                                    'title'          => $valueRow['title'],
                                    'price_modifier' => !empty($valueRow['price']) ? (float)$valueRow['price'] : 0.0000,
                                    'sku_modifier'   => null,
                                    'sort_order'     => $subPosition++
                                ]);
                                $valueModel->save();
                            }
                        }
                    }
                }

                $this->messageManager->addSuccessMessage(__('The shared option rules have been preserved.'));
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('Save Error: %1', $e->getMessage()));
            }

            return $resultRedirect->setPath('*/*/edit', ['id' => $id]);
        }

        return $resultRedirect->setPath('*/*/');
    }
}