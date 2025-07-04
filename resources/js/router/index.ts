import {
    createRouter,
    createWebHistory,
    type RouteRecordRaw,
} from "vue-router";
import { useAuthStore } from "@/stores/auth";
import { useConfigStore } from "@/stores/config";
import NProgress from "nprogress";
import "nprogress/nprogress.css";

declare module "vue-router" {
    interface RouteMeta {
        pageTitle?: string;
        permission?: string;
    }
}

const routes: Array<RouteRecordRaw> = [

    {
        path: "/CekResi",
        name: "CekResi",
        component: () => import("@/pages/dashboard/CekResi.vue"),
        meta: {
            pageTitle: "Cek Resi",
            middleware: "guest",
        },
    },
    {
        path: "/",
        redirect: "/dashboard",
        component: () => import("@/layouts/default-layout/DefaultLayout.vue"),
        meta: {
            middleware: "auth",
        },
        children: [
            {
                path: "/dashboard",
                name: "dashboard",
                component: () => import("@/pages/dashboard/Index.vue"),
                // meta: {
                //     pageTitle: "Dashboard",
                //     breadcrumbs: ["Dashboard"],
                // },
            },
            {
                path: "/utama",
                name: "utama",
                component: () => import("@/pages/dashboard/Utama.vue"),
                meta: {
                    pageTitle: "Utama",
                    breadcrumbs: ["Utama"],
                },
            },
            {
                path: "/CekResi",
                name: "CekResi",
                component: () => import("@/pages/dashboard/CekResi.vue"),
                meta: {
                    pageTitle: "CekResi",
                    breadcrumbs: ["CekResi"],
                },
            },
            {
                path: "/dashboard/profile",
                name: "dashboard.profile",
                component: () => import("@/pages/dashboard/profile/Index.vue"),
                meta: {
                    pageTitle: "Profile",
                    breadcrumbs: ["Profile"],
                },
            },
            {
                path: "/dashboard/setting",
                name: "dashboard.setting",
                component: () => import("@/pages/dashboard/setting/Index.vue"),
                meta: {
                    pageTitle: "Website Setting",
                    breadcrumbs: ["Website", "Setting"],
                },
            },

            // MASTER
            {
                path: "/dashboard/master/users/roles",
                name: "dashboard.master.users.roles",
                component: () =>
                    import("@/pages/dashboard/master/users/roles/Index.vue"),
                meta: {
                    pageTitle: "User Roles",
                    breadcrumbs: ["Master", "Users", "Roles"],
                },
            },
            {
                path: "/dashboard/master/users",
                name: "dashboard.master.users",
                component: () =>
                    import("@/pages/dashboard/master/users/Index.vue"),
                meta: {
                    pageTitle: "Users",
                    breadcrumbs: ["Master", "Users"],
                },
            },
            {
                path: "/dashboard/master/kurir",
                name: "dashboard.master.kurir",
                component: () =>
                    import("@/pages/dashboard/master/kurir/Index.vue"),
                meta: {
                    pageTitle: "Kurir",
                    breadcrumbs: ["Master", "Kurir"],
                },
            },

            // RESTORAN
            {
                path: "/dashboard/restoran/menu",
                name: "dashboard.restoran.menu",
                component: () =>
                    import("@/pages/dashboard/restoran/menu/Index.vue"),
                meta: {
                    pageTitle: "Restoran",
                    breadcrumbs: ["Restoran", "Menu"],
                },
            },
            {
                path: "/dashboard/restoran/profil",
                name: "dashboard.restoran.profil",
                component: () =>
                    import("@/pages/dashboard/restoran/profil/Index.vue"),
                // meta: {
                //     pageTitle: "Restoran",
                //     breadcrumbs: ["Restoran", "Profil"],
                // },
            },

            // ORDER
            {
                path: "/dashboard/transaksi/order",
                name: "dashboard.transaksi.order",
                component: () =>
                    import("@/pages/dashboard/transaksi/order/Index.vue"),
                // meta: {
                //     pageTitle: "Order",
                //     breadcrumbs: ["Order" ],
                // },
            },
            {
                path: "/dashboard/transaksi/riwayat",
                name: "dashboard.transaksi.riwayat",
                component: () =>
                    import("@/pages/dashboard/transaksi/riwayat/Index.vue"),
                // meta: {
                //     pageTitle: "Pembayaran",
                //     breadcrumbs: ["Pembayaran" ],
                // },
            },

            // PENGIRIMAN
            {
                path: "/dashboard/pengiriman/kirim",
                name: "dashboard.pengiriman.kirim",
                component: () =>
                    import("@/pages/dashboard/pengiriman/kirim/Index.vue"),
                // meta: {
                //     pageTitle: "Pengiriman",
                //     breadcrumbs: ["Pengiriman", "Kirim Order"],
                // },
            },
            {
                path: "/dashboard/pengiriman/rating",
                name: "dashboard.pengiriman.rating",
                component: () =>
                    import("@/pages/dashboard/pengiriman/rating/Index.vue"),
                meta: {
                    pageTitle: "Pengiriman",
                    breadcrumbs: ["Pengiriman", "Rating"],
                },
            },
        ],
    },
    {
        path: "/",
        component: () => import("@/layouts/AuthLayout.vue"),
        children: [
            {
                path: "/sign-in",
                name: "sign-in",
                component: () => import("@/pages/auth/sign-in/Index.vue"),
                meta: {
                    pageTitle: "Sign In",
                    middleware: "guest",
                },
            },
        ],
    },
    {
        path: "/",
        component: () => import("@/layouts/AuthLayout.vue"),
        children: [
            {
                path: "/sign-up",
                name: "sign-up",
                component: () => import("@/pages/auth/sign-up/Index.vue"),
                meta: {
                    pageTitle: "Sign Up",
                    middleware: "guest",
                },
            },
        ],
    },
    {
        path: "/",
        component: () => import("@/layouts/SystemLayout.vue"),
        children: [
            {
                // the 404 route, when none of the above matches
                path: "/404",
                name: "404",
                component: () => import("@/pages/errors/Error404.vue"),
                meta: {
                    pageTitle: "Error 404",
                },
            },
            {
                path: "/500",
                name: "500",
                component: () => import("@/pages/errors/Error500.vue"),
                meta: {
                    pageTitle: "Error 500",
                },
            },
        ],
    },
    {
        path: "/:pathMatch(.*)*",
        redirect: "/404",
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
    scrollBehavior(to) {
        // If the route has a hash, scroll to the section with the specified ID; otherwise, scroll to the top of the page.
        if (to.hash) {
            return {
                el: to.hash,
                top: 80,
                behavior: "smooth",
            };
        } else {
            return {
                top: 0,
                left: 0,
                behavior: "smooth",
            };
        }
    },
});

router.beforeEach(async (to, from, next) => {
    if (to.name) {
        // Start the route progress bar.
        NProgress.start();
    }

    const authStore = useAuthStore();
    const configStore = useConfigStore();

    // current page view title
    if (to.meta.pageTitle) {
        document.title = `${to.meta.pageTitle} - ${import.meta.env.VITE_APP_NAME
            }`;
    } else {
        document.title = import.meta.env.VITE_APP_NAME as string;
    }

    // reset config to initial state
    configStore.resetLayoutConfig();

    // verify auth token before each page change
    if (!authStore.isAuthenticated) await authStore.verifyAuth();

    // before page access check if page requires authentication
    if (to.meta.middleware == "auth") {
        if(to.name =="dashboard" && authStore.user.role?.name == "pelanggan"){
            next({ name: "dashboard.restoran.profil"})
        }
        if (authStore.isAuthenticated) {
            if (
                to.meta.permission &&
                !authStore.user.permission.includes(to.meta.permission)
            ) {
                next({ name: "404" });
            } else if (to.meta.checkDetail == false) {
                next();
            }

            next();
        } else {
            next({ name: "sign-in" });
        }
    } else if (to.meta.middleware == "guest" && authStore.isAuthenticated) {
        if(to.name =="dashboard" && authStore.user.role?.name == "pelanggan"){
            next({ name: "dashboard.restoran.profil"})
        }
        next({ name: "dashboard" });
    } else {
        next();
    }
});

router.afterEach(() => {
    // Complete the animation of the route progress bar.
    NProgress.done();
});

export default router;
