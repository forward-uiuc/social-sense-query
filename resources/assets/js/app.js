
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

window.Vue = require('vue');

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

Vue.component('example-component', require('./components/ExampleComponent.vue'));
Vue.component('query-builder', require('./components/QueryBuilder.vue'));
Vue.component('graphql-query-builder', require('./components/GraphQLQueryBuilder.vue'));
Vue.component('context-info', require('./components/ContextInformation.vue'));
Vue.component('meta-query-builder-canvas', require('./components/MetaQueryBuilderCanvas.vue'));
Vue.component('meta-query-builder', require('./components/MetaQueryBuilder.vue'));
Vue.component('meta-query-viewer', require('./components/MetaQueryViewer.vue'));
Vue.component('query-viewer', require('./components/QueryViewer.vue'));
Vue.component('d3-tree-view', require('./components/D3TreeView.vue'));
Vue.component('query-editor', require('./components/QueryEditor.vue'));
Vue.component('provider-authorization-status', require('./components/ProviderAuthorizationStatus.vue'));
Vue.component('landing-page-headline', require('./components/LandingPageHeadline.vue'));

import TreeView from "vue-json-tree-view";
import D3Network from "vue-d3-network";

Vue.use(TreeView)
Vue.use(D3Network)

Vue.$http = window.axios;

const app = new Vue({
    el: '#app',
		methods: {
			echo: function(query) {
				return query;
			}
		}
});
