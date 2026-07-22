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
define([
    'jquery',
    'Magento_Catalog/js/price-box'
], function ($) {
    'use strict';

    return function (config) {
        var customPrices = config.customPrices || {};

        if (!Object.keys(customPrices).length) {
            return;
        }

        // Helper function to find and patch the active Magento priceBox widget config cache data structures
        function locateAndPatchPriceBox() {
            var $priceBox = $('[data-role="priceBox"], .price-box, [data-price-box="product-id"]');

            if ($priceBox.length) {
                var widget = $priceBox.data('magePriceBox');

                if (widget && widget.options && widget.options.priceConfig) {
                    // Inject our custom pricing values directly into the active calculation matrix
                    $.extend(true, widget.options.priceConfig.optionPrices, customPrices);

                    // Trigger a forced layout price check evaluation recalculation sweep instantly
                    $priceBox.trigger('updatePrice');
                    return true;
                }
            }
            return false;
        }

        // Attempt an immediate match hook execution run sweep
        if (!locateAndPatchPriceBox()) {
            // Fallback lightweight interval check loop sequence tracker chain
            var searchInterval = setInterval(function () {
                if (locateAndPatchPriceBox()) {
                    clearInterval(searchInterval);
                }
            }, 100);

            // Safety limit sequence time-kill to prevent endless overhead if widget elements disappear
            setTimeout(function () {
                clearInterval(searchInterval);
            }, 5000);
        }

        // Global delegate input changes handler track hook capture across option classes
        $(document).on('change input', '.shared-option-trigger', function () {
            var $priceBox = $('[data-role="priceBox"], .price-box, [data-price-box="product-id"]');
            if ($priceBox.length) {
                $priceBox.trigger('updatePrice');
            }
        });
    };
});