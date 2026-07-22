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
namespace BluePrint3D\SharedProductOptions\Plugin;

use Magento\Catalog\Model\Product\Type\AbstractType;
use Magento\Framework\DataObject;
use Magento\Catalog\Model\Product;

class PreHydrateCartOptions
{
    /**
     * Force option model hydration before Magento evaluates the has_options flag during add-to-cart execution
     */
    public function beforePrepareForCartAdvanced(
        AbstractType $subject,
        DataObject $buyRequest,
        Product $product,
                     $processMode = null
    ) {
        if (!$product || !$product->getId()) {
            return [$buyRequest, $product, $processMode];
        }

        // Trigger our primary option injection pipeline to build the models and set has_options to true
        $product->getOptions();

        return [$buyRequest, $product, $processMode];
    }
}