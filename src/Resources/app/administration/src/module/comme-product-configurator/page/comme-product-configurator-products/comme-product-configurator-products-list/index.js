import template from './comme-product-configurator-products-list.html.twig';
import './comme-product-configurator-products-list.scss'

const { Component } = Shopware;
const {Criteria} = Shopware.Data;
const { hasOwnProperty } = Shopware.Utils.object;

Shopware.Component.register('comme-product-configurator-products-list', {
    template,

    data() {
        return {
            isLoading: false,
            total: 0,
            productConfiguratorProducts: null,
            products: null
        }
    },

    inject: [
        'repositoryFactory'
    ],

    mixins: [
        'notification',
        'listing',
        'placeholder'
    ],

    computed: {
        columns() {
            return [
                {
                    property: 'id',
                    label: 'Product ID',
                    allowResize: true,
                    routerLink: 'comme.product.configurator.commeProductConfiguratorProductsDetail',
                    primary: true,
                },
                {
                    property: 'translated.name',
                    label: 'Parent product',
                    allowResize: true
                },
                {
                    property: 'price[0].net',
                    label: 'Price',
                    allowResize: true
                }
            ];
        },
        productConfiguratorProductsRepository() {
            return this.repositoryFactory.create('comme_product_configurator_products');
        },
        productRepository() {
            return this.repositoryFactory.create('product');
        }
    },

    created() {
        this.productConfiguratorProductsRepository
            .search(new Criteria(), Shopware.Context.api)
            .then((result) => {
                this.productConfiguratorProducts = this.uniqueElements(result, 'parentProductId')
                const productIds = []
                this.productConfiguratorProducts.forEach(product => {
                    productIds.push(product.parentProductId)
                })
                if(productIds.length > 0) {
                    const criteria = new Criteria();
                    criteria.setIds(productIds);
                    this.productRepository
                        .search(criteria, Shopware.Context.api)
                        .then(result => {
                            this.products = result;
                        });
                } else this.products = [];

            });

    },

    methods: {
        uniqueElements(a, param){
            return a.filter(function(item, pos, array){
                return array.map(function(mapItem){ return mapItem[param]; }).indexOf(item[param]) === pos;
            })
        },

        onlyUnique(value, index, self) {
            return self.indexOf(value) === self;
        },

        onChangeLanguage(languageId) {
            Shopware.State.commit('context/setApiLanguageId', languageId);
            this.getList();
        },

        async getList() {
            this.isLoading = true;

            try {
                this.productConfiguratorProductsRepository
                    .search(new Criteria(), Shopware.Context.api)
                    .then((result) => {
                        this.productConfiguratorProducts = this.uniqueElements(result, 'parentProductId')
                    });
            } catch {
                this.isLoading = false;
            }
        },
    },
});
