<script setup lang="ts">
import { h, ref, watch } from "vue";
import { useDelete } from "@/libs/hooks";
import Form from "./Form.vue";
import { createColumnHelper } from "@tanstack/vue-table";
import type { kirim } from "@/types";

const column = createColumnHelper<kirim>();
const paginateRef = ref<any>(null);
const selected = ref<string>("");
const openForm = ref<boolean>(false);

const { delete: deleteKirim } = useDelete({
    onSuccess: () => paginateRef.value.refetch(),
});

const columns = [
    column.accessor("no", {
        header: "#",
    }),
    column.accessor("kurir.user.name", { header: "Pengirim" }),
    column.accessor("order.no_resi", { header: "No Resi" }),
    column.accessor("order.ekspedisi", { header: "Ekspedisi" }),
    column.accessor("order.menu.nama_menu", { header: "Nama Menu" }),
    column.accessor("order.user.name", { header: "Penerima" }),
    column.accessor("order.user.phone", { header: "No Hp Penerima" }),
    column.accessor("order.tujuan_kota.name", { header: "Kota Tujuan"}),
    column.accessor("order.alamat_tujuan", { header: "Alamat Penerima" }),
    column.accessor("status", {
    header: "Status",
    cell: (cell) =>
        h(
        "span",
        {
            class: "badge",
            style: {
            backgroundColor: {
                Dikirim: "#ffc107",
                Terkirim: "#28a745",
            }[cell.getValue()] || "#adb5bd",
            color: "white",
            padding: "5px 10px",
            borderRadius: "5px",
            },
        },
        {
            Dikirim: "Dikirim",
            Terkirim: "Terkirim",
        }[cell.getValue()] || "Tidak Diketahui"
        ),
    }),

    column.accessor("id", {
        header: "Aksi",
        cell: (cell) =>
            h("div", { class: "d-flex gap-2" }, [
                h(
                    "button",
                    {
                        class: "btn btn-sm btn-icon btn-info",
                        onClick: () => {
                            selected.value = cell.getValue();
                            openForm.value = true;
                        },
                    },
                    h("i", { class: "la la-pencil fs-2" })
                ),
                h(
                    "button",
                    {
                        class: "btn btn-sm btn-icon btn-danger",
                        onClick: () =>
                            deleteKirim(`/kirim/${cell.getValue()}`),
                    },
                    h("i", { class: "la la-trash fs-2" })
                ),
            ]),
    }),
];

const refresh = () => paginateRef.value.refetch();

watch(openForm, (val) => {
    if (!val) {
        selected.value = "";
    }
    window.scrollTo(0, 0);
});
</script>

<template>
    <Form
        :selected="selected"
        @close="openForm = false"
        v-if="openForm"
        @refresh="refresh"
    />

    <div class="card">
        <div class="card-header align-items-center">
            <h2 class="mb-0">List Pengiriman</h2>
            <button
                type="button"
                class="btn btn-sm btn-primary ms-auto"
                v-if="!openForm"
                @click="openForm = true"
            >
                Tambah
                <i class="la la-plus"></i>
            </button>
        </div>
        <div class="card-body">
            <paginate
                ref="paginateRef"
                id="table-kirim"
                url="/kirim"
                :columns="columns"
            ></paginate>
        </div>
    </div>
</template>
