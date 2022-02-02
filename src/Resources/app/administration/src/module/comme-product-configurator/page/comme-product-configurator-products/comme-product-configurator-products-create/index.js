import template from '../comme-product-configurator-products-detail/comme-product-configurator-products-detail.html.twig';

const {Component} = Shopware;

Shopware.Component.extend('comme-product-configurator-products-create', 'comme-product-configurator-products-detail', {
    template,

    methods: {
        createdComponent() {
            this.getProduct();
            this.getProducts();
            this.getChildProducts();
            this.selectedChildProducts = []
        },

        getProduct() {
            this.product = this.productConfiguratorProductsRepository.create('comme_product_configurator_products');
        },

        loadAddedProduct() {
            this.productConfiguratorProductsRepository
                .get(this.$route.params.id, Shopware.Context.api, new Criteria())
                .then((entity) => {
                    this.product = entity;
                });
        },

        onClickSave() {
            this.isLoading = true;
            if(this.selectedChildProducts.length > 0) {
                this.selectedChildProducts.forEach(product => {

                    this.product.childProductId = product.id;
                    console.log(this.product)
                    // this.productConfiguratorProductsRepository
                    //     .save(this.product, Shopware.Context.api)
                    //     .then(() => {
                    //         this.isLoading = false;
                    //         this.$router.replace({
                    //             path: `/comme/product/configurator/index`
                    //         });
                    //         this.createNotificationSuccess({
                    //             title: this.$tc('global.default.success'),
                    //             message: this.$tc('comme-product-configurator-products-save-success.message'),
                    //         });
                    //     }).catch((exception) => {
                    //     this.isLoading = false;
                    //     if (exception.response.data && exception.response.data.errors) {
                    //         exception.response.data.errors.forEach((error) => {
                    //             this.createNotificationWarning({
                    //                 title: "Error while creating a new product",
                    //                 message: error.detail
                    //             });
                    //         });
                    //     }
                    // });
                })
            }

        }
    }
});
