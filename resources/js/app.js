require('./bootstrap');

import Vue from 'vue';
import router from './vue/router';
import store from './vue/store';
import App from './views/App';
import VeeValidate,{Validator} from 'vee-validate';
import * as es from 'vee-validate/dist/locale/es';

const config = {
    aria: true,
    classNames: {},
    classes: false,
    delay: 0,
    dictionary: null,
    errorBagName: 'errors', // change if property conflicts
    events: 'input|blur',
    fieldsBagName: 'fields',
    i18n: null, // the vue-i18n plugin instance
    i18nRootKey: 'validations', // the nested key under which the validation messages will be located
    inject: true,
    locale: 'es',
    validity: false,
    useConstraintAttrs: true
};

Vue.use(VeeValidate, config);
Validator.localize('es',es);

import toastr from 'toastr';

Vue.component(
    'passport-clients',
    require('./components/passport/Clients.vue').default
);

Vue.component(
    'passport-authorized-clients',
    require('./components/passport/AuthorizedClients.vue').default
);

Vue.component(
    'passport-personal-access-tokens',
    require('./components/passport/PersonalAccessTokens.vue').default
);

Vue.component(
    'main-app',
    require('./components/main-app').default
);

Vue.component('pagination', require('laravel-vue-pagination'));

router.beforeEach((to, from, next) => {
    if (to.matched.some(record => record.meta.requiresAuth)) {
        // verifica si esta logueado o no
        if (!store.getters.loggedIn) {
            next({
                name: 'login',
            })
        } else {
            next()
        }
    } else {
        next()
    }
});

$('body').tooltip({
    selector: ".btn-tooltip",
    container: 'body',
    placement:'bottom',
    trigger:'hover'
});


toastr.options = {
    "closeButton": true,
    "debug": true,
    "newestOnTop": true,
    "progressBar": true,
    "positionClass": "toast-top-right",
    "preventDuplicates": true,
    "onclick": null,
    "showDuration": "7000",
    "hideDuration": "7000",
    "timeOut": "7000",
    "extendedTimeOut": "7000",
    "showEasing": "swing",
    "hideEasing": "linear",
    "showMethod": "fadeIn",
    "hideMethod": "fadeOut"
}

window.createToastr = (type,txt) =>{
    if(type=="error"){
        toastr.error(txt,"Alerta");
    }else if(type=="warning"){
        toastr.warning(txt,"Aleta");
    }else if(type=="info"){
        toastr.info(txt,"Información");
    }else{
        toastr.success(txt,"Exito");
    }
}

const app = new Vue({
    el: '#app',
    components: { App },
    router,
    store
});
