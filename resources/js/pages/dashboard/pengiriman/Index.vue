<script setup lang="ts">
import { computed, h, ref, watch } from "vue";
import { useDelete } from "@/libs/hooks";
import Form from "./Form.vue";
import { createColumnHelper } from "@tanstack/vue-table";
import type { order } from "@/types";

const column = createColumnHelper<order>();
const paginateRef = ref<any>(null);
const selected = ref<string>(""); // ID order yang dipilih
const openForm = ref<boolean>(false); // Form order/edit

const showDetail = ref(false);
const detailOrder = ref<order | null>(null);

function openDetail(order: order) {
  detailOrder.value = order;
  showDetail.value = true;
}

// Hook untuk menghapus data
const { delete: deleteOrder } = useDelete({
  onSuccess: () => paginateRef.value?.refetch(),
});

// Definisi kolom tabel order
const columns = [
//column.accessor("no", { header: "No" }),
  column.accessor("menu.nama_menu", { header: "Nama Menu" }),
  column.accessor("menu.harga", {
    header: "Harga Satuan",
    cell: (cell) =>
      "Rp " + Number(cell.getValue()).toLocaleString("id-ID"),
  }),
  column.accessor("jumlah", {
    header: "Jumlah",
    cell: (cell) => cell.getValue(),
  }),
  column.accessor("total_harga", {
  header: "Total Harga",
  cell: (cell) => {
    const row = cell.row.original;
    // Pastikan harga dan jumlah ada
    const harga = row.menu?.harga ?? 0;
    const jumlah = row.jumlah ?? 0;
    const total = harga * jumlah;
    return "Rp " + Number(total).toLocaleString("id-ID");
  },
}),
  column.accessor("status", {
  header: "Status",
  cell: (cell) =>
    h(
      "span",
      {
        class: "badge",
        style: {
          backgroundColor: {
            on_delivery: "#ffc107", // Kuning untuk On Delivery
            selesai: "#28a745", // Hijau untuk Selesai
            batal: "#dc3545", // Merah untuk Batal
          }[cell.getValue()] || "#6c757d", // Default abu-abu jika status tidak dikenal
          color: "white",
          padding: "5px 10px",
          borderRadius: "5px",
        },
      },
      cell
        .getValue()
        .replace("_", " ") // Ganti underscore dengan spasi
        .charAt(0)
        .toUpperCase() + cell.getValue().slice(1) // Kapitalisasi huruf pertama
    ),
}),

//   column.accessor( "photo", {
//     header: "Foto",
//     cell: (cell) =>
//         h("img", {
//             src: `/storage/${cell.getValue()}`,
//             alt: "Foto Order",
//             style:  "width: 50px; height: 50px; object-fit: cover; border-radius: 5px;",
//           }),
//   }),

  column.accessor("id", {
    header: "Aksi",
    cell: (cell) =>
      h("div", { class: "d-flex gap-2" }, [
        // Tombol Detail
    //   h(
    //     "button",
    //     {
    //       class: "btn btn-sm btn-icon btn-secondary",
    //       onClick: () => openDetail(cell.row.original),
    //     },
    //     h("i", { class: "la la-receipt fs-2" }) // Icon struk
    //   ),
      //Tombol Edit
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
        // Tombol Hapus
        h(
          "button",
          {
            class: "btn btn-sm btn-icon btn-danger",
            onClick: () => {
              deleteOrder(`/order/${cell.getValue()}`);
            },
          },
          h("i", { class: "la la-trash fs-2" })
        ),
      ]),
  }),
];

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
      <!-- <button
        type="button"
        class="btn btn-sm btn-primary ms-auto"
        v-if="!openForm"
        @click="openForm = true"
      >
        <i class="la la-plus me-2"></i> Tambah
      </button> -->
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
