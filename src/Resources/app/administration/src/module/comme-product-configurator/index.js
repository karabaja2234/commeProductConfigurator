import deDE from './snippet/de-DE.json';
import enGB from './snippet/en-GB.json';


/**
 * Comme Product Configurator Products
 */
import './page/comme-product-configurator-products/comme-product-configurator-products-list'
import './page/comme-product-configurator-products/comme-product-configurator-products-create'
import './page/comme-product-configurator-products/comme-product-configurator-products-detail'

import './component/comme-product-configurator-vertical-main-tabs';

Shopware.Module.register('comme-product-configurator', {
    type: 'plugin',
    name: 'comme product configurator',
    color: '#ff3d58',
    icon: 'default-object-lab-flask',

    snippets: {
        'de-DE': deDE,
        'en-GB': enGB
    },

    routes:{
        index:{
            component:'comme-product-configurator-products-list',
            path:'index'
        },

        commeProductConfiguratorProductsDetail:{
            component: 'comme-product-configurator-products-detail',
            path:'commeProductConfiguratorProductsDetail/:id',
            props: {
                default(route) {
                    return {commeProductConfiguratorProductsId: route.params.id};
                }
            },
            meta:{
                parentPath:'comme.product.configurator.index'
            }
        },

        commeProductConfiguratorProductsCreate:{
            component:'comme-product-configurator-products-create',
            path:'commeProductConfiguratorProductsCreate',
            meta:{
                parentPath:'comme.product.configurator.index'
            }
        },
    },

    navigation: [{
        id:'comme-product-configurator',
        label: 'comme-product-configurator.title',
        color: '#ff3d58',
        path: 'comme.product.configurator.index',
        icon: 'default-object-lab-flask',
        parent: 'sw-extension',
        position: 90,
    }]
});
