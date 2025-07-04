<script setup lang="ts">
import { h, ref, watch, computed } from "vue";
import { useDelete } from "@/libs/hooks";
import Form from "./Form.vue";
import { useAuthStore } from "@/stores/auth";
import { createColumnHelper } from "@tanstack/vue-table";
import type { Menu } from "@/types";

const authStore = useAuthStore();
const isAdmin = computed(() => authStore.user?.role?.name === "admin");

const column = createColumnHelper<Menu>();
const paginateRef = ref<any>(null);
const selected = ref<string>("");
const openForm = ref<boolean>(false);

const showModal = ref(false);
const selectedImage = ref("");

const { delete: deleteMenu } = useDelete({
  onSuccess: () => paginateRef.value.refetch(),
});

const columns = [
  column.accessor("nama_menu", { header: "Nama Menu" }),
  column.accessor("harga", {
    header: "Harga Satuan",
    cell: (cell) =>
      "Rp " + Number(cell.getValue()).toLocaleString("id-ID"),
  }),
  column.accessor("kategori", {
    header: "Kategori",
    cell: (cell) =>
      h(
        "span",
        {
          class: "badge",
          style: {
            backgroundColor:
              cell.getValue() === "Makanan"
                ? "#FFA500"
                : cell.getValue() === "Minuman"
                ? "#17a2b8"
                : "#ffc107",
            color: "white",
            padding: "5px 10px",
            borderRadius: "5px",
          },
        },
        cell.getValue()
      ),
  }),
  column.accessor("photo", {
    header: "Foto",
    cell: (cell) =>
      h("img", {
        src: cell.getValue()
          ? `/storage/${cell.getValue()}`
          : "/images/no-image.png",
        alt: "Foto Menu",
        class: "img-thumbnail",
        style: "width: 50px; height: 50px; object-fit: cover; cursor: pointer;",
        onClick: () => {
          selectedImage.value = cell.getValue()
            ? `/storage/${cell.getValue()}`
            : "/images/no-image.png";
          showModal.value = true;
        },
      }),
  }),
  column.accessor("status", {
    header: "Status",
    cell: (cell) =>
      h(
        "span",
        {
          class: "badge",
          style: {
            backgroundColor:
              cell.getValue() === "Tersedia" ? "#28a745" : "#dc3545",
            color: "white",
            padding: "5px 10px",
            borderRadius: "5px",
          },
        },
        cell.getValue()
      ),
  }),
  column.accessor("id", {
    header: "Aksi",
    cell: (cell) => {
      if (!isAdmin.value) return null;

      return h("div", { class: "d-flex gap-2" }, [
        h(
          "button",
          {
            class: "btn btn-sm btn-icon btn-info",
            onClick: () => {
              selected.value = cell.getValue().toString();
              openForm.value = true;
            },
          },
          h("i", { class: "la la-pencil fs-2" })
        ),
        h(
          "button",
          {
            class: "btn btn-sm btn-icon btn-danger",
            onClick: () => deleteMenu(`/menu/${cell.getValue()}`),
          },
          h("i", { class: "la la-trash fs-2" })
        ),
      ]);
    },
  }),
];

// Kolom final, filter 'Aksi' jika bukan admin
const finalColumns = computed(() => {
  const baseCols = [...columns];
  if (!isAdmin.value) {
    return baseCols.filter((col) => col.header !== "Aksi");
  }
  return baseCols;
});

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

  <div
    v-if="showModal"
    class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-70"
  >
    <div
      class="bg-white p-6 rounded-xl shadow-lg text-center max-w-[90vw] max-h-[90vh] overflow-auto"
    >
      <img
        :src="selectedImage"
        alt="Preview"
        class="max-w-[30vw] max-h-[30vh] rounded-lg mb-6 object-contain"
      />
      <button @click="showModal = false" class="btn btn-danger btn-lg px-5 py-2">
        Tutup
      </button>
    </div>
  </div>

  <div class="card">
    <div class="card-header align-items-center">
      <h2 class="mb-0"> List Produk </h2>
      <button
        type="button"
        class="btn btn-sm btn-primary ms-auto"
        v-if="!openForm && isAdmin"
        @click="openForm = true"
      >
        Tambah
        <i class="la la-plus"></i>
      </button>
    </div>
    <div class="card-body">
      <paginate
        ref="paginateRef"
        id="table-menu"
        url="/menu"
        :columns="finalColumns"
      />
    </div>
  </div>
</template>
