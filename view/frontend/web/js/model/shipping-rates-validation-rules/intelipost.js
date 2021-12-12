/**
 * @package Intelipost\Shipping
 * @copyright Copyright (c) 2021 Intelipost
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

define([], function () {
    'use strict';

    return {
        /**
         * @return {Object}
         */
        getRules: function () {
            return {
                'postcode': {
                    'required': true
                }
            };
        }
    };
});
