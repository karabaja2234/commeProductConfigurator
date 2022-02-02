import template from './comme-product-configurator-vertical-main-tabs.html.twig';

const { Component } = Shopware;

Component.register('comme-product-configurator-vertical-main-tabs', {
    template,

    props: {
        defaultItem: {
            type: String,
            default: 'blog'
        }
    },

    methods: {
        onChangeTab(name) {
            this.currentTab = name;
        }
    }
});
