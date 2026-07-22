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

use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResourceConnection;
use Psr\Log\LoggerInterface;

class ProductSavePlugin
{
    protected $request;
    protected $resourceConnection;
    protected $logger;

    public function __construct(
        RequestInterface $request,
        ResourceConnection $resourceConnection,
        LoggerInterface $logger
    ) {
        $this->request = $request;
        $this->resourceConnection = $resourceConnection;
        $this->logger = $logger;
    }

    /**
     * Intercept the database resource model save operation directly
     *
     * @param ProductResource $subject
     * @param ProductResource $result
     * @param AbstractModel $product
     * @return ProductResource
     */
    public function afterSave(ProductResource $subject, $result, AbstractModel $product)
    {
        $productId = (int)$product->getId();
        $postData = $this->request->getPostValue();

        $this->logger->info('=== SharedOptions Save Plugin Executing for Product ID: ' . $productId . ' ===');

        // Look everywhere for our data key inside unpredictable nested structures
        $submittedGroupIds = $this->findKeyRecursively($postData, 'shared_option_groups');

        $this->logger->info('Raw Post Payload Collected: ' . json_encode($submittedGroupIds));

        if ($submittedGroupIds === null) {
            $this->logger->info('Data key missing entirely from POST array. Skipping save processing.');
            return $result;
        }

        if (!is_array($submittedGroupIds)) {
            $submittedGroupIds = !empty($submittedGroupIds) ? explode(',', (string)$submittedGroupIds) : [];
        }

        $connection = $this->resourceConnection->getConnection();
        $tableName = $connection->getTableName('blueprint3d_shared_option_product');

        // 1. Wipe out stale relationships
        $connection->delete($tableName, ['product_id = ?' => $productId]);

        // 2. Insert records sequentially
        $rowsToInsert = [];
        foreach ($submittedGroupIds as $groupId) {
            $groupId = trim((string)$groupId);
            if ($groupId === '') { continue; }
            $rowsToInsert[] = [
                'group_id'   => (int)$groupId,
                'product_id' => $productId
            ];
        }

        if (!empty($rowsToInsert)) {
            $connection->insertMultiple($tableName, $rowsToInsert);
            $this->logger->info('Successfully stored rows in DB: ' . json_encode($rowsToInsert));
        } else {
            $this->logger->info('No groups selected. Database cleared for product.');
        }

        return $result;
    }

    /**
     * Deep search helper to find a data key inside unpredictable nested array structures
     *
     * @param array $array
     * @param string $searchKey
     * @return mixed|null
     */
    private function findKeyRecursively(array $array, string $searchKey)
    {
        if (array_key_exists($searchKey, $array)) {
            return $array[$searchKey];
        }
        foreach ($array as $value) {
            if (is_array($value)) {
                $result = $this->findKeyRecursively($value, $searchKey);
                if ($result !== null) {
                    return $result;
                }
            }
        }
        return null;
    }
}