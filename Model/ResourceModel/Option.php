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
namespace BluePrint3D\SharedProductOptions\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Option extends AbstractDb
{
    protected function _construct()
    {
        // 🚨 UPDATED: Using the exact table and primary key from your db_schema.xml
        $this->_init('blueprint3d_shared_option_item', 'item_id');
    }
}
