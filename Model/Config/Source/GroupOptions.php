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
namespace BluePrint3D\SharedProductOptions\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\App\ResourceConnection;

class GroupOptions implements OptionSourceInterface
{
    protected $resourceConnection;

    public function __construct(ResourceConnection $resourceConnection) {
        $this->resourceConnection = $resourceConnection;
    }

    public function toOptionArray()
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $connection->getTableName('blueprint3d_shared_option_group');

        $select = $connection->select()->from($tableName, ['group_id', 'title']);
        $groups = $connection->fetchAll($select);

        $options = [];
        foreach ($groups as $group) {
            $options[] = [
                'value' => $group['group_id'],
                'label' => $group['title']
            ];
        }
        return $options;
    }
}