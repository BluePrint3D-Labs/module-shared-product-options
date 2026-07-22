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
namespace BluePrint3D\SharedProductOptions\Plugin;

use Magento\Catalog\Ui\DataProvider\Product\Form\ProductDataProvider;
use Magento\Framework\App\ResourceConnection;
use Psr\Log\LoggerInterface;

class ProductDataProviderPlugin
{
    protected $resourceConnection;
    protected $logger;

    public function __construct(ResourceConnection $resourceConnection, LoggerInterface $logger) {
        $this->resourceConnection = $resourceConnection;
        $this->logger = $logger;
    }

    public function afterGetData(ProductDataProvider $subject, $result)
    {
        if (empty($result)) {
            return $result;
        }

        $connection = $this->resourceConnection->getConnection();
        $tableName = $connection->getTableName('blueprint3d_shared_option_product');

        foreach ($result as $productId => $productData) {
            // CRITICAL FIX: Skip the 'config' key. Only process numeric product IDs!
            if (!is_numeric($productId)) {
                continue;
            }

            $select = $connection->select()
                ->from($tableName, 'group_id')
                ->where('product_id = ?', (int)$productId);

            $selectedIds = $connection->fetchCol($select);

            // ui-select with multiple=true expects an array of strings
            $selectedIds = array_map('strval', $selectedIds);

            $this->logger->info("=== DataProvider Injecting for Product ID {$productId}: " . json_encode($selectedIds) . " ===");

            if (!isset($result[$productId]['product'])) {
                $result[$productId]['product'] = [];
            }

            // Map data directly into the product scope defined by your XML
            $result[$productId]['product']['shared_option_groups'] = $selectedIds;
        }

        return $result;
    }
}