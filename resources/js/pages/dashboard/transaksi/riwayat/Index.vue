<script setup lang="ts">
import { h, ref, watch } from "vue";
import { useDelete } from "@/libs/hooks";
import { createColumnHelper } from "@tanstack/vue-table";
import type { kirim } from "@/types";

const column = createColumnHelper<kirim>();
const paginateRef = ref<any>(null);
const selected = ref<string>("");
const openForm = ref<boolean>(false);

const { delete: deletePengiriman } = useDelete({
    onSuccess: () => paginateRef.value.refetch(),
});

const columns = [
    column.accessor("no", {
        header: "#",
    }),
    column.accessor("order.menu.nama_menu", { header: "Nama Menu" }),
    column.accessor("order.user.name", { header: "Penerima" }),
    column.accessor("order.user.phone", { header: "No Hp Penerima" }),
    column.accessor("order.alamat_tujuan", { header: "Alamat Penerima" }),
    column.accessor("status", {
    header: "Status",
    cell: (cell) =>
        cell.getValue() === "Terkirim"
        ? h(
            "span",
            {
                class: "badge",
                style: {
                backgroundColor: "#28a745",
                color: "white",
                padding: "5px 10px",
                borderRadius: "5px",
                },
            },
            "Terkirim"
            )
        : null, // selain Terkirim, kosong
    }),

    // column.accessor("id", {
    //     header: "Aksi",
    //     cell: (cell) =>
    //         h("div", { class: "d-flex gap-2" }, [
    //             // h(
    //             //     "button",
    //             //     {
    //             //         class: "btn btn-sm btn-icon btn-info",
    //             //         onClick: () => {
    //             //             selected.value = cell.getValue();
    //             //             openForm.value = true;
    //             //         },
    //             //     },
    //             //     h("i", { class: "la la-pencil fs-2" })
    //             // ),
    //             // h(
    //             //     "button",
    //             //     {
    //             //         class: "btn btn-sm btn-icon btn-danger",
    //             //         onClick: () =>
    //             //             deletePengiriman(`/riwayat/${cell.getValue()}`),
    //             //     },
    //             //     h("i", { class: "la la-trash fs-2" })
    //             // ),
    //         ]),
    // }),
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
    <!-- <Form
        :selected="selected"
        @close="openForm = false"
        v-if="openForm"
        @refresh="refresh"
    /> -->

    <div class="card">
        <div class="card-header align-items-center">
            <h2 class="mb-0">Riwayat Order</h2>
        </div>
        <div class="card-body">
            <paginate
                ref="paginateRef"
                id="table-riwayat"
                url="/riwayat"
                :columns="columns"
            ></paginate>
        </div>
    </div>
</template>
