define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'tapsys',
                component: 'Tapsys_Checkout/js/view/payment/method-renderer/tapsys-method'
            }
        );
        return Component.extend({});
    }
);