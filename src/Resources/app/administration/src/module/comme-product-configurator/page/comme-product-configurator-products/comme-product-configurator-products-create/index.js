import template from '../comme-product-configurator-products-detail/comme-product-configurator-products-detail.html.twig';

Shopware.Component.extend('comme-product-configurator-products-create', 'comme-product-configurator-products-detail', {
    template,

    methods: {
        createdComponent() {
            this.getProduct();
            this.getProducts();
            this.selectedChildProducts = []
            this.childProducts = []
            this.disabledInput = true;
            this.disabledParentProduct = false
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
            const syncService = Shopware.Service('syncService');
            const httpClient = syncService.httpClient;
            const authorizationHeaders = syncService.getBasicHeaders();

            if(this.selectedParentProduct !== "" &&  this.selectedParentProduct !== null && this.selectedChildProducts.length > 0) {
                httpClient.get(
                    `/_action/validate-parent-product/${this.selectedParentProduct}`,
                    {
                        headers: authorizationHeaders
                    }
                ).then((response) => {
                    this.isLoading = false;
                    if(response.data > 0) {
                        let msg = `Chosen parent product already exists!`;
                        this.createNotificationError({
                            title: "Error: Product not saved properly",
                            message: msg
                        });
                    } else {
                        this.selectedChildProducts.forEach(product => {
                            this.childProducts = [...this.childProducts, product.id]
                        })
                        const childProducts = this.childProducts;

                        httpClient.post(
                            `/_action/save-product/${this.selectedParentProduct}`,
                            {
                                headers: authorizationHeaders,
                                childProducts: childProducts
                            }
                        ).then((response) => {
                            httpClient.post(
                                `/_action/product-seo-create/${this.selectedParentProduct}`,
                                {
                                    headers: authorizationHeaders
                                }
                            ).then((response) => {
                                this.createNotificationSuccess({
                                    title: this.$tc('global.default.success'),
                                    message: this.$tc('comme-product-configurator-products.comme-product-configurator-products-save-success.message'),
                                });
                                this.$router.replace({
                                    path: `/comme/product/configurator/index`
                                });
                            });
                        });
                    }
                })
            } else {
                this.createNotificationError({
                    title: "Error while creating a new product",
                    message: "All fields must be entered"
                });
            }
            this.isLoading = false;
            this.processSuccess = true;
        }
    }
});
