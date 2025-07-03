<script setup lang="ts">
import { h, ref, watch, computed } from "vue";
import { useAuthStore } from "@/stores/auth";
import { useDelete } from "@/libs/hooks";
import Form from "./Form.vue";
import Detail from "./Detail.vue";
import { createColumnHelper } from "@tanstack/vue-table";
import type { order } from "@/types";

const authStore = useAuthStore();
const isAdmin = computed(() => authStore.user?.role?.name === "admin");

const column = createColumnHelper<order>();
const paginateRef = ref<any>(null);
const selected = ref<string>(""); // ID Order yang dipilih
const openForm = ref<boolean>(false); // Form Order/edit

const showDetail = ref(false);
const detailOrder = ref<order | null>(null);
// Fungsi untuk membuka detail order
function openDetail(order: order) {
  detailOrder.value = order;
  showDetail.value = true;
}

// Hook untuk menghapus data
const { delete: deleteOrder } = useDelete({
  onSuccess: () => paginateRef.value?.refetch(),
});

// Definisi kolom tabel order
const columns = computed(() =>
  [

    // Kolom umum
    column.accessor("user.name", { header: "Username" }),
    column.accessor("no_resi", { header: "No Resi"}),
     // Kolom khusus admin
    ...(isAdmin.value
      ? [
          column.accessor("user.phone", { header: "No. Hp User" }),
          column.accessor("alamat_tujuan", { header: "Alamat Tujuan" }),
            column.accessor("tujuan_provinsi.name", {
                header: "Provinsi Tujuan",
            }),
            column.accessor("tujuan_kota.name", {
                header: "Kota Tujuan",
            }),
        ]
      : []),
    column.accessor("menu.nama_menu", { header: "Nama Menu" }),
    column.accessor("menu.harga", {
      header: "Harga Satuan",
      cell: (cell) => "Rp " + Number(cell.getValue()).toLocaleString("id-ID"),
    }),
    column.accessor("jumlah", { header: "Jumlah" }),
    // column.accessor("biaya", {
    //   header: "Ongkir",
    //   cell: (cell) => "Rp " + Number(cell.getValue()).toLocaleString("id-ID"),
    // }),
    // column.accessor("total_harga", {
    //   header: "Total Biaya",
    //   cell: (cell) => {
    //     const row = cell.row.original;
    //     const harga = row.menu?.harga ?? 0;
    //     const jumlah = row.jumlah ?? 0;
    //     const biaya = row.biaya ?? 0;
    //     const total = harga * jumlah + biaya;
    //     return "Rp " + Number(total).toLocaleString("id-ID");
    //   },
    // }),
    column.accessor("status", {
      header: "Status",
      cell: (cell) =>
        h(
          "span",
          {
            class: "badge",
            style: {
              backgroundColor: {
                dibayar: "blue",
                dikirim: "#ffc107",
                diterima: "#28a745",
                batal: "red",
              }[cell.getValue()] || "#adb5bd",
              color: "white",
              padding: "5px 10px",
              borderRadius: "5px",
            },
          },
          {
            dibayar: "Dibayar",
            dikirim: "Dikirim",
            diterima: "Diterima",
            batal: "Batal",
          }[cell.getValue()] || "Tidak Diketahui"
        ),
    }),
    column.accessor("id", {
      header: "Aksi",
      cell: (cell) => {
        const row = cell.row.original;
        const actions = [
          h(
            "button",
            {
              class: "btn btn-sm btn-icon btn-secondary",
              onClick: () => openDetail(row),
            },
            h("i", { class: "la la-receipt fs-2" })
          ),
        ];

        if (isAdmin.value) {
          actions.push(
            // h(
            //   "button",
            //   {
            //     class: "btn btn-sm btn-icon btn-info",
            //     onClick: () => {
            //       selected.value = cell.getValue();
            //       openForm.value = true;
            //     },
            //   },
            //   h("i", { class: "la la-pencil fs-2" })
            // ),
            h(
              "button",
              {
                class: "btn btn-sm btn-icon btn-danger",
                onClick: () => {
                  deleteOrder(`/order/${cell.getValue()}`);
                },
              },
              h("i", { class: "la la-trash fs-2" })
            )
          );
        }

        return h("div", { class: "d-flex gap-2" }, actions);
      },
    }),
  ]
);


const refresh = () => paginateRef.value?.refetch?.();

watch(openForm, (val) => {
  if (!val) selected.value = "";
  window.scrollTo(0, 0);
});
</script>

<template>
  <Form
    v-if="openForm"
    :selected="selected"
    @close="openForm = false"
    @refresh="refresh"
  />
  <Detail
  :order="detailOrder"
  :show="showDetail"
  @close="showDetail = false"
  />

  <div class="card">
    <div class="card-header align-items-center">
      <h2 class="mb-0">List Order</h2>
      <button
        type="button"
        class="btn btn-sm btn-primary ms-auto"
        v-if="!openForm"
        @click="openForm = true"
      >
        <i class="la la-plus me-2"></i> Tambah
      </button>
    </div>
    <div class="card-body">
      <paginate
        ref="paginateRef"
        id="table-order"
        url="/order"
        :columns="columns"
        class="table-responsive"
      ></paginate>
    </div>
  </div>
</template>
