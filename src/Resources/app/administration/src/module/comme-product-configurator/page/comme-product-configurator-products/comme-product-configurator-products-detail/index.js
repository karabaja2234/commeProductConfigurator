import template from './comme-product-configurator-products-detail.html.twig';
import './comme-product-configurator-products-detail.scss'

const {Component, Mixin, StateDeprecated, Context} = Shopware;
const {Criteria, EntityCollection} = Shopware.Data;
const utils = Shopware.Utils;

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
            disabledInput: false
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
                    console.log(entity)
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
            //console.log(this.selectedParentProduct)
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
            //console.log(this.selectedParentProduct)
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

        onChangeLanguage(event) {
            this.getProduct();
            this.languageId = event;
        },

        //Method which compares 2 arrays and returns the difference between them
        arrayComparer(otherArray) {
            return current => {
                return otherArray.filter(other => {
                    return other == current
                }).length == 0;
            }
        },

        //Method which compares 2 arrays of objects and returns the difference between them
        arrayObjectComparer(otherArray) {
            return function (current) {
                return otherArray.filter(function (other) {
                    return other.seoPathInfo == current.seoPathInfo
                }).length == 0;
            }
        },

        //Order-IT was here
        generateRandomSeo(length) {
            var ret = "";
            while (ret.length < length) {
                ret += Math.random().toString(16).substring(2);
            }
            return ret.substring(0, length);
        },

        searchChildProducts() {

        },

        onClickSave() {
            const syncService = Shopware.Service('syncService');
            const httpClient = syncService.httpClient;
            const authorizationHeaders = syncService.getBasicHeaders();
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
                        //If the response has at least one found object, it means that the slug already exists and it will throw an error
                        if(response.data > 0) {
                            let msg = `Entered seo url already exists`;
                            this.createNotificationError({
                                title: "Error: Product not saved properly",
                                message: msg
                            });
                            this.getProduct();
                        } else {
                            //If the response has 0 found objects, that means that the slug does not exist and it can be saved
                            this.seoUrlRepository
                                .get(this.seoUrl.id, Shopware.Context.api)
                                .then(entity => {
                                    this.entity = entity;
                                    this.entity.seoPathInfo = this.seoUrl.seoPathInfo
                                    this.seoUrlRepository
                                        .save(this.entity, Shopware.Context.api)
                                        .then(() => {
                                            this.getSeoUrl();
                                            this.getSeoUrlBackup();
                                        });
                                });
                        }
                    })
                }

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
                    this.createNotificationSuccess({
                        title: this.$tc('global.default.success'),
                        message: this.$tc('comme-product-configurator-products-save-success.message'),
                    });
                    this.$router.replace({
                        path: `/comme/product/configurator/index`
                    });
                });
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
