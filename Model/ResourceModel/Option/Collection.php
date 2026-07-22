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
namespace BluePrint3D\SharedProductOptions\Model\ResourceModel\Option;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use BluePrint3D\SharedProductOptions\Model\Option as ModelOption;
use BluePrint3D\SharedProductOptions\Model\ResourceModel\Option as ResourceOption;

class Collection extends AbstractCollection
{
    // 🚨 UPDATED: Changed from option_id to item_id
    protected $_idFieldName = 'item_id';

    protected function _construct()
    {
        $this->_init(ModelOption::class, ResourceOption::class);
    }
}
