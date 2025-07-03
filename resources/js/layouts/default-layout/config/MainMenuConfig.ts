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

            {
                heading: "User Dashboard",
                name: "utama",
                route: "/utama",
                keenthemesIcon: "chart-line-up-2",
            },
            {
                heading: "Cek Resi",
                name: "CekResi",
                route: "/CekResi",
                keenthemesIcon: "graph-4",
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
                        heading: "Produk",
                        name: "menu",
                        route: "/dashboard/restoran/menu",
                    },
                ],
            },

            // ORDER
            {
                sectionTitle: "Transaksi",
                route: "/dashboard/transaksi",
                name: "transaksi",
                keenthemesIcon: "bi bi-basket",
                sub: [
                    {
                        heading: "Tambah Order",
                        name: "order",
                        route: "/dashboard/transaksi/order"
                    },
                    {
                        heading: "Riwayat Order",
                        name: "riwayat",
                        route: "/dashboard/transaksi/riwayat"
                    },
                ],
            },
            // PENGIRIMAN
            {
                sectionTitle: "Pengiriman",
                route: "/dashboard/pengiriman",
                name: "pengiriman",
                keenthemesIcon: "truck",
                sub: [
                    {
                        heading: "Kirim Order",
                        name: "kirim",
                        route: "/dashboard/pengiriman/kirim",
                    },
                    {
                        heading: "Rating & Ulasan",
                        name: "rating",
                        route: "/dashboard/pengiriman/rating",
                    },
                ],
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
