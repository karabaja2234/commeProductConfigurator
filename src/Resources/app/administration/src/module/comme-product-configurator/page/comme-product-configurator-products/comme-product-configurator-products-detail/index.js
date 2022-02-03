import template from './comme-product-configurator-products-detail.html.twig';
import './comme-product-configurator-products-detail.scss'

const { Mixin, StateDeprecated } = Shopware;
const { Criteria } = Shopware.Data;

Shopware.Component.register('comme-product-configurator-products-detail', {
    template,
    inject: [
        'repositoryFactory'
    ],

    mixins: [
        Mixin.getByName('notification')
    ],

    metaInfo() {
        return {
            title: this.$createTitle()
        };
    },

    data() {
        return {
            product: null,
            products: [],
            selectedParentProduct: null,
            selectedChildProducts: [],
            productIds: [],
            existingChildProducts: [],
            childProducts: [],
            languageId: Shopware.Context.api.languageId,
            isLoading: false,
            processSuccess: false,
            httpClient: null,                               //Client for sending HTTP requests
            syncService: null,                              //Client for using shopware's request properties (headers etc.)
            entity: null,                                   //Object for storing info from the GET requests
            limit: 25,
            seoUrl: {
                seoPathInfo: ""
            },
            existingSeoUrl: {
                seoPathInfo: ""
            },
            disabledInput: false,
            disabledParentProduct: true
        };
    },

    computed: {
        productConfiguratorProductsRepository() {
            return this.repositoryFactory.create('comme_product_configurator_products');
        },

        productRepository() {
            return this.repositoryFactory.create('product');
        },

        seoUrlRepository() {
            return this.repositoryFactory.create('seo_url');
        },

        languageRepository() {
            return this.repositoryFactory.create('language');
        },

        languageStore() {
            return StateDeprecated.getStore('language');
        },
    },

    created() {
        this.createdComponent();
    },

    methods: {
        createdComponent() {
            this.getProduct();
            this.getProducts();
            this.syncService = Shopware.Service('syncService');
            this.httpClient = this.syncService.httpClient;
        },

        getProduct() {
            const criteria = new Criteria();
            criteria.addFilter(
                Criteria.equals('parentProductId', this.$route.params.id)
            );
            this.productConfiguratorProductsRepository
                .search(criteria, Shopware.Context.api, new Criteria())
                .then((entity) => {
                    this.product = entity[0];
                    this.selectedParentProduct = entity[0].parentProductId;
                    this.getExistingChildProducts();
                    this.getSeoUrl();
                    this.getSeoUrlBackup();
                });
        },

        getExistingChildProducts(){
            this.productConfiguratorProductsRepository.search(new Criteria(), Shopware.Context.api)
                .then((result) => {
                    this.products = []
                    this.existingChildProducts = []
                    result.forEach((product) => {
                        if(product.parentProductId == this.selectedParentProduct)
                        this.existingChildProducts.push(product.childProductId)
                    })
                    if(this.existingChildProducts.length > 0) {
                        const criteria = new Criteria();
                        criteria.setIds(this.existingChildProducts)
                        this.productRepository.search(criteria, Shopware.Context.api)
                            .then((result) => {
                                this.products = []
                                this.selectedChildProducts = []
                                result.forEach((product) => {
                                    this.selectedChildProducts.push(product)
                                })
                            });
                    }
                });
        },

        getProducts(){
            this.productRepository.search(new Criteria(), Shopware.Context.api)
                .then((result) => {
                    this.products = []
                    result.forEach((product) => {
                        this.products.push(product)
                    })
                });
        },

        getSeoUrl() {
            this.httpClient.get(
                `/_action/get-seo-url/${this.selectedParentProduct}`,
                {
                    headers: this.syncService.getBasicHeaders()
                }
            ).then((response) => {
                if(response.data != null)
                this.seoUrl = response.data;
                else this.seoUrl = this.seoUrlRepository.create('seo_url');
            }).catch((exception) => {
                this.isLoading = false;
                this.createNotificationError({
                    title: "Error while saving the Category",
                    message: exception
                });
            });
        },

        getSeoUrlBackup() {
            this.httpClient.get(
                `/_action/get-seo-url/${this.selectedParentProduct}`,
                {
                    headers: this.syncService.getBasicHeaders()
                }
            ).then((response) => {
                if(response.data != null)
                this.existingSeoUrl = response.data;
                else this.existingSeoUrl = this.seoUrlRepository.create('seo_url');
            }).catch((exception) => {
                this.isLoading = false;
                this.createNotificationError({
                    title: "Error while saving the Category",
                    message: exception
                });
            });
        },

        setSelectedParentProduct(product){
            this.selectedParentProduct = product
        },

        setSelectedChildProducts(value) {
            this.selectedChildProducts = value
        },

        saveProductChanges() {
            this.selectedChildProducts.forEach(product => {
                this.childProducts = [...this.childProducts, product.id]
            })
            const childProducts = this.childProducts;

            this.httpClient.post(
                `/_action/save-product/${this.selectedParentProduct}`,
                {
                    headers: this.syncService.getBasicHeaders(),
                    childProducts: childProducts
                }
            ).then((response) => {
                this.createNotificationSuccess({
                    title: this.$tc('global.default.success'),
                    message: this.$tc('comme-product-configurator-products.comme-product-configurator-products-update-success.message'),
                });
                this.$router.replace({
                    path: `/comme/product/configurator/index`
                });
            });
        },

        onClickSave() {
            this.isLoading = true;

            if(this.selectedParentProduct !== "" &&  this.selectedParentProduct !== null && this.selectedChildProducts.length > 0 && this.seoUrl.seoPathInfo !== "") {
                this.seoUrl.seoPathInfo = this.seoUrl.seoPathInfo.replace(/ /g,'')
                if(this.seoUrl.seoPathInfo !== this.existingSeoUrl.seoPathInfo) {
                    this.httpClient.get(
                        `/_action/validate-seo-url/${this.seoUrl.seoPathInfo}`,
                        {
                            headers: this.syncService.getBasicHeaders()
                        }
                    ).then((response) => {
                        this.isLoading = false;
                        if(response.data > 0) {
                            let msg = `Entered seo url already exists`;
                            this.createNotificationError({
                                title: "Error: Product not saved properly",
                                message: msg
                            });
                            this.getSeoUrl();
                            this.getSeoUrlBackup();
                        } else {
                            this.seoUrlRepository
                                .get(this.seoUrl.id, Shopware.Context.api)
                                .then(entity => {
                                    this.entity = entity;
                                    this.entity.seoPathInfo = this.seoUrl.seoPathInfo
                                    this.seoUrlRepository
                                        .save(this.entity, Shopware.Context.api)
                                        .then(() => {
                                            this.saveProductChanges();
                                        });
                                });
                        }
                    })
                } else this.saveProductChanges();
            } else {
                this.createNotificationError({
                    title: "Error while saving product",
                    message: "All fields must be entered"
                });
            }
            this.isLoading = false;
            this.processSuccess = true;
        },

        saveFinish() {
            this.processSuccess = false;
        },
    }
});
