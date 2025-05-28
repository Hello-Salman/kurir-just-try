import type { MenuItem } from "@/layouts/default-layout/config/types";

const MainMenuConfig: Array<MenuItem> = [
    {
        pages: [
            {
                heading: "Dashboard",
                name: "dashboard",
                route: "/dashboard",
                keenthemesIcon: "element-11",
            },
        ],
    },

    // WEBSITE
    {
        heading: "Website",
        route: "/dashboard/website",
        name: "website",
        pages: [
            // MASTER
            {
                sectionTitle: "Master",
                route: "/master",
                keenthemesIcon: "cube-3",
                name: "master",
                sub: [
                    {
                        sectionTitle: "User",
                        route: "/users",
                        name: "master-user",
                        sub: [
                            {
                                heading: "Role",
                                name: "master-role",
                                route: "/dashboard/master/users/roles",
                            },
                            {
                                heading: "User",
                                name: "master-user",
                                route: "/dashboard/master/users",
                            },
                            {
                                heading: "Kurir",
                                name: "master-kurir",
                                route: "/dashboard/master/kurir",
                            },
                        ],
                    },
                ],
            },

            // RESTORAN
            {
                sectionTitle: "Easy Eats",
                route: "/dashboard/restoran",
                name: "restoran",
                keenthemesIcon: "shop",
                sub: [
                    {
                        heading: "Profil",
                        name: "profil-restoran",
                        route: "/dashboard/restoran/profil",
                    },
                    {
                        heading: "Menu",
                        name: "menu",
                        route: "/dashboard/restoran/menu",
                    },
                ],
            },

            // ORDER
            {
                heading: "Data Order",
                route: "/dashboard/order",
                name: "order",
                keenthemesIcon: "bi bi-basket",
            },
            {
                heading: "Pengiriman",
                route: "/dashboard/pengiriman",
                name: "pengiriman",
                keenthemesIcon: "courier-express",
            },
            {
                heading: "Setting",
                route: "/dashboard/setting",
                name: "setting",
                keenthemesIcon: "setting-2",
            },
        ],
    },
];

export default MainMenuConfig;
