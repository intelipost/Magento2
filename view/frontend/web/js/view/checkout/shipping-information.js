/**
 * @package Intelipost\Shipping
 * @copyright Copyright (c) 2021 Intelipost
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

/*jshint browser:true jquery:true*/
/*global alert*/

define(
    [
        'jquery',
        'uiComponent',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/step-navigator',
        'Magento_Checkout/js/model/sidebar'
    ],
    function ($, Component, quote, stepNavigator, sidebarModel) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'Intelipost_Shipping/checkout/shipping-information'
            },

            isVisible: function () {
                return !quote.isVirtual() && stepNavigator.isProcessed('shipping');
            },

            getShippingMethodTitle: function () {
                var shippingMethod = quote.shippingMethod();
                return shippingMethod ? shippingMethod.carrier_title + " - " + shippingMethod.method_title : '';
            },

            back: function () {
                sidebarModel.hide();
                stepNavigator.navigateTo('shipping');
            },

            backToShippingMethod: function () {
                sidebarModel.hide();
                stepNavigator.navigateTo('shipping', 'opc-shipping_method');
            }
        });
    }
);

