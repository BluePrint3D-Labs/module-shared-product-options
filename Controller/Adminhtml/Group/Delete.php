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

class Delete extends Action
{
    const ADMIN_RESOURCE = 'BluePrint3D_SharedProductOptions::group';

    protected $groupFactory;

    public function __construct(
        Context $context,
        GroupFactory $groupFactory
    ) {
        parent::__construct($context);
        $this->groupFactory = $groupFactory;
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('group_id');

        if ($id) {
            try {
                $model = $this->groupFactory->create();
                $model->load($id);

                // Nuke the parent record out of blueprint3d_shared_option_group
                $model->delete();

                $this->messageManager->addSuccessMessage(__('The shared product option group has been successfully deleted.'));
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $resultRedirect->setPath('*/*/edit', ['id' => $id]);
            }
        }

        $this->messageManager->addErrorMessage(__('We could not find a group record to delete.'));
        return $resultRedirect->setPath('*/*/');
    }
}
