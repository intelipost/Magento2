/**
 * @package Intelipost\Shipping
 * @copyright Copyright (c) 2021 Intelipost
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

define([
    'uiComponent',
    'Magento_Checkout/js/model/shipping-rates-validator',
    'Magento_Checkout/js/model/shipping-rates-validation-rules',
    '../model/shipping-rates-validator/intelipost',
    '../model/shipping-rates-validation-rules/intelipost'
], function (
    Component,
    defaultShippingRatesValidator,
    defaultShippingRatesValidationRules,
    intelipostShippingRatesValidator,
    intelipostShippingRatesValidationRules
) {
    'use strict';

    defaultShippingRatesValidator.registerValidator('intelipost', intelipostShippingRatesValidator);
    defaultShippingRatesValidationRules.registerRules('intelipost', intelipostShippingRatesValidationRules);

    return Component;
});
