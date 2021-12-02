    /*
     * @package     Intelipost_Shipping
     * @copyright   Copyright (c) 2021 - Intelipost (https://intelipost.com.br)
     * @author      Intelipost Team
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

