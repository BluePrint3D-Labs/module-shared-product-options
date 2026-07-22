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
define([
    'jquery',
    'domReady!'
], function ($) {
    'use strict';

    // State lock to guarantee code setup executes exactly once
    var isSetup = false;

    return function (config) {
        function initImageSwitcher() {
            if (isSetup) return true;

            var galleryElement = $('[data-gallery-role="gallery-placeholder"]');
            var galleryWidget = galleryElement.data('gallery');
            var fotorama = null;

            // Target the active image engine slider instance
            if (galleryWidget && galleryWidget.fotorama) {
                fotorama = galleryWidget.fotorama;
            } else {
                fotorama = $('.fotorama').data('fotorama') || galleryElement.data('fotorama');
            }

            if (!fotorama) {
                return false;
            }

            // Activate lock immediately
            isSetup = true;
            console.log("STU DEBUG: Switcher successfully connected to Fotorama gallery engine!", fotorama.data);

            var defaultIndex = fotorama.activeframe ? fotorama.data.indexOf(fotorama.activeframe) : 0;
            if (defaultIndex < 0) defaultIndex = 0;

            // Target the custom option elements directly
            var $options = $('.product-custom-option');

            // DEFEAT PROPAGATION STOP: Direct binding with an explicit event namespace
            $options.off('change.sharedOptionsImage');
            $options.on('change.sharedOptionsImage', function () {
                var targetLabel = '';

                if ($(this).is('select')) {
                    targetLabel = $(this).find('option:selected').data('option-label');
                } else if ($(this).is(':checked')) {
                    targetLabel = $(this).data('option-label');
                }

                console.log("STU DEBUG: Option changed directly! Label text identified:", targetLabel);

                // If they select back to the empty option prompt, return to base image
                if (!targetLabel) {
                    fotorama.show(defaultIndex);
                    return;
                }

                var matchedIndex = -1;
                var cleanTarget = targetLabel.toString().toLowerCase().trim();

                // Cross-match targeted label text inside image caption properties
                fotorama.data.forEach(function (imageFrame, index) {
                    if (imageFrame.caption) {
                        var cleanCaption = imageFrame.caption.toString().toLowerCase().trim();
                        if (cleanCaption === cleanTarget) {
                            matchedIndex = index;
                        }
                    }
                });

                console.log("STU DEBUG: Gallery scanning index output:", matchedIndex);

                // Command the slider frame to slide to the matched index
                if (matchedIndex !== -1) {
                    fotorama.show(matchedIndex);
                }
            });

            return true;
        }

        // Initialize execution matrix loop
        if (!initImageSwitcher()) {
            $(document).on('gallery:loaded', function () {
                initImageSwitcher();
            });

            var checkAttempts = 0;
            var pollingId = setInterval(function () {
                checkAttempts++;
                if (initImageSwitcher() || checkAttempts > 30) {
                    clearInterval(pollingId);
                }
            }, 250);
        }
    };
});