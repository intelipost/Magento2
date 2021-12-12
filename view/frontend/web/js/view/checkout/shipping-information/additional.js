/**
 * @package Intelipost\Shipping
 * @copyright Copyright (c) 2021 Intelipost
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */


define([
    'uiComponent'
], function (Component) {
    'use strict';
    return Component.extend({
        defaults: {
            template: 'Intelipost_Shipping/checkout/shipping-information/additional'
        },
        getShippingInformationAdditional: function () {
            var result = 'blah';

            jQuery.ajax({
                url: window.intelipost_schedule_status_url,
                async: false,
                showLoader: true, // enable loader

                success: function (data) {
                    result = data;
                },
            });

            return result;
        },
    });
});

