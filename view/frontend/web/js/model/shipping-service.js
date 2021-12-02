/*
 * @package     Intelipost_Basic
 * @copyright   Copyright (c) 2021 - Intelipost (https://intelipost.com.br)
 * @author      Intelipost Team
 */

/*global define*/
define(
    [
        'ko',
        'Magento_Checkout/js/model/checkout-data-resolver'
    ],
    function (ko, checkoutDataResolver) {
        "use strict";
        var shippingRates = ko.observableArray([]);
        return {
            isLoading: ko.observable(false),
            /**
             * Set shipping rates
             *
             * @param ratesData
             */
            setShippingRates: function (ratesData) {
                shippingRates(ratesData);
                shippingRates.valueHasMutated();
                checkoutDataResolver.resolveShippingRates(ratesData);

                // Intelipost_Pickup
                jQuery('input:radio[id^="s_method_pickup_"]').each(function (index, element) {
                    jQuery(element).click(function () {
                        jQuery("#intelipost-pickup-map").remove(); // google maps
                        jQuery("#intelipost-quote-calendar").remove(); // calendar

                        var parent = jQuery(element).parents('.item-options'); // cart
                        if (!parent.length) {
                            parent = jQuery("#onepage-checkout-shipping-method-additional-load"); // checkout
                        }

                        jQuery.ajax({
                            url: INTELIPOST_PICKUP_STORE_URL + "?info=" + this.value,
                            showLoader: true, // enable loader

                            success: function (data) {
                                jQuery(parent).append(data);
                            },
                        });
                    });
                });

                // Intelipost_Quote
                jQuery('input:radio[id^="s_method_intelipost_"]').each(function (index, element) {
                    jQuery(element).click(function () {
                        jQuery("#intelipost-quote-calendar").remove(); // calendar
                        jQuery("#intelipost-pickup-map").remove(); // google maps

                        var parent = jQuery(element).parents('.item-options');
                        if (!parent.length) parent = jQuery("#onepage-checkout-shipping-method-additional-load"); // checkout

                        jQuery.ajax({
                            url: window.intelipost_calendar_url + "?info=" + this.value,
                            showLoader: true, // enable loader

                            success: function (data) {
                                jQuery(parent).append(data);
                            },
                        });
                    });
                });


                //Intelipost presales
                jQuery('input:radio[id^="s_method_presales_"]').each(function (index, element) {
                    jQuery(element).click(function () {
                        jQuery("#intelipost-quote-calendar").remove(); // calendar
                        jQuery("#intelipost-pickup-map").remove(); // google maps

                        var parent = jQuery(element).parents('.item-options');
                        if (!parent.length) parent = jQuery("#onepage-checkout-shipping-method-additional-load"); // checkout

                        jQuery.ajax({
                            url: window.intelipost_calendar_url + "?info=" + this.value,
                            showLoader: true, // enable loader

                            success: function (data) {
                                jQuery(parent).append(data);
                            },
                        });
                    });
                });
            },

            /**
             * Get shipping rates
             *
             * @returns {*}
             */
            getShippingRates: function () {
                return shippingRates;
            }
        };
    }
);

