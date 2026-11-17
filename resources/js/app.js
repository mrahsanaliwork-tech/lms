import './bootstrap';
import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue, route } from 'ziggy-js';

// Restore dark mode before first paint
const savedTheme = localStorage.getItem('theme');
if (savedTheme === 'dark' || (!savedTheme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
    document.documentElement.classList.add('dark');
}

// Global v-click-outside directive
const clickOutsideDirective = {
    mounted(el, binding) {
        el._clickOutside = (e) => { if (!el.contains(e.target)) binding.value(e); };
        document.addEventListener('click', el._clickOutside, true);
    },
    unmounted(el) {
        document.removeEventListener('click', el._clickOutside, true);
    },
};

createInertiaApp({
    title: (title) => `${title} - LMS Platform`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        // Expose Ziggy config + route() globally so <script setup> components
        // can call route() without an explicit import in every file.
        const ziggyConfig = props.initialPage.props.ziggy ?? {};
        window.Ziggy = ziggyConfig;
        window.route = (name, params, absolute) => route(name, params, absolute, ziggyConfig);

        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue, ziggyConfig)
            .directive('click-outside', clickOutsideDirective)
            .mount(el);
    },
    progress: {
        color: '#6366f1',
    },
});
