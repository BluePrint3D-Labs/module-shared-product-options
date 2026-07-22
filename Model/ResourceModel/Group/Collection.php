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
namespace BluePrint3D\SharedProductOptions\Model\ResourceModel\Group;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use BluePrint3D\SharedProductOptions\Model\Group as ModelGroup;
use BluePrint3D\SharedProductOptions\Model\ResourceModel\Group as ResourceGroup;

class Collection extends AbstractCollection
{
    /**
     * Define primary field identification keys
     */
    protected $_idFieldName = 'group_id';

    /**
     * Link the collection to the Model and ResourceModel definitions
     */
    protected function _construct()
    {
        $this->_init(
            ModelGroup::class,
            ResourceGroup::class
        );
    }
}
