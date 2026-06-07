import './bootstrap';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import '../css/app.css';
import 'remixicon/fonts/remixicon.css';
import { ZiggyVue } from 'ziggy-js';
import { Ziggy } from './ziggy';



const appName = import.meta.env.VITE_APP_NAME || 'SAMOSIR';

// Setup Ziggy globally
if (Ziggy) {
    window.Ziggy = Ziggy;
}

// Setup route() as global function
if (window.Ziggy) {
    window.route = (name, params = {}, absolute = false) => {
        const routes = Ziggy.routes || Ziggy.namedRoutes;
        return routes && routes[name]
            ? (routes[name].uri || routes[name])
                .replace(/\{([^}]+)\}/g, (_, key) => {
                    const value = params[key];
                    delete params[key];
                    return value;
                })
                .replace(/\/$/, '') +
                (Object.keys(params).length
                    ? '?' + new URLSearchParams(params).toString()
                    : '')
            : name;
    };
}

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue, Ziggy)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
