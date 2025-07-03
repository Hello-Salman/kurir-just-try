<script setup lang="ts">
import { h, ref, watch } from "vue";
import { useDelete } from "@/libs/hooks";
import Form from "./Form.vue";
import { createColumnHelper } from "@tanstack/vue-table";
import type { order } from "@/types";

const column = createColumnHelper<order>();
const paginateRef = ref<any>(null);
const selected = ref<string>("");
const openForm = ref<boolean>(false);

const { delete: deleteRating } = useDelete({
    onSuccess: () => paginateRef.value.refetch(),
});

const columns = [
    column.accessor("no", {
        header: "#",
    }),
    column.accessor("order.user.name", { header: "Nama Pelanggan" }),
    column.accessor("order.menu.nama_menu", { header: "Nama Product" }),
    column.accessor("order.no_resi", { header: "No Resi" }),
    column.accessor("rating" , {
        header: "Rating",
        cell: (cell) =>
            h(
                "span",
                {
                    class: "badge",
                    style: {
                        backgroundColor:
                            cell.getValue() >= 4
                                ? "#28a745"
                                : cell.getValue() >= 3
                                ? "#ffc107"
                                : "#dc3545",
                        color: "white",
                        padding: "5px 10px",
                        borderRadius: "5px",
                    },
                },
                cell.getValue().toString()
            ),
    }),
    column.accessor("ulasan", {
        header: "ulasan",
        cell: (cell) =>
            h("span", { class: "text-muted" }, cell.getValue() || "Tidak ada"),
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
                            deleteRating(`/rating/${cell.getValue()}`),
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
            <h2 class="mb-0">Rating & Ulasan</h2>
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
                id="table-rating"
                url="/rating"
                :columns="columns"
            ></paginate>
        </div>
    </div>
</template>
