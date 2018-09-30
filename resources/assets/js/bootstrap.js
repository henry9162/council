
window._ = require('lodash');
window.Popper = require('popper.js').default;

import InstantSearch from 'vue-instantsearch';

/**
 * We'll load jQuery and the Bootstrap jQuery plugin which provides support
 * for JavaScript based Bootstrap features such as modals and tabs. This
 * code may be modified to fit the specific needs of your application.
 */

try {
    window.$ = window.jQuery = require('jquery');

    require('bootstrap');
} catch (e) {}

/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

window.axios = require('axios');

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

/**
 * Next we will register the CSRF Token as a common header with Axios so that
 * all outgoing HTTP requests automatically have it attached. This is just
 * a simple convenience so we don't have to attach every token manually.
 */

let token = document.head.querySelector('meta[name="csrf-token"]');

if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
} else {
    console.error('CSRF token not found: https://laravel.com/docs/csrf#csrf-x-csrf-token');
}

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */

// import Echo from 'laravel-echo'

// window.Pusher = require('pusher-js');

// window.Echo = new Echo({
//     broadcaster: 'pusher',
//     key: process.env.MIX_PUSHER_APP_KEY,
//     cluster: process.env.MIX_PUSHER_APP_CLUSTER,
//     encrypted: true
// });


//The below gives a call back function and returns a boolean true or false.
//This is what the bootstrap.js allows.
/*window.Vue.prototype.authorize = function(handler){

    let user = window.App.user;

    return user ? handler(user) : false;
};*/
/*canUpdate(){
    return this.authorize(user => this.data.user_id == user.id);
}*/

//But with this second approach, we gonna allow for named authorizations

let authorizations = require('./authorizations');

window.Vue.prototype.authorize = function(...params){
    if (! window.App.signedIn) return false;

    if (typeof params[0] === 'string'){
        return authorizations[params[0]](params[1]);
    }

    return params[0](window.App.user);
};

//we assign a global variable signedIn
Vue.prototype.signedIn = window.App.signedIn;

window.events = new Vue();

Vue.use(InstantSearch);

window.flash = function(message, level = 'success'){
    window.events.$emit('flash', {message, level});
};