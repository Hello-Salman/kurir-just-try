<script setup lang="ts">
import { ref, onMounted, watch, computed } from "vue";
import * as Yup from "yup";
import axios from "@/libs/axios";
import { toast } from "vue3-toastify";
import type { Order, Menu } from "@/types";
import { block, unblock } from "@/libs/utils";
import ApiService from "@/core/services/ApiService";

import { useAuthStore } from "@/stores/auth";


const authStore = useAuthStore();
const currentKurir = authStore.user.kurir;

const props = defineProps({
  selected: {
    type: String,
    default: null,
  },
});

const emit = defineEmits(["close", "refresh"]);

const Order = ref({
  id_menu: "",
  jumlah: 0,
  total_harga: 0,
  status: "",
  menu: {
    nama_menu: "",
    harga: 0,
  },
}as any);
const formRef = ref();

const menus = ref<Menu[]>([]);
const menuOptions = computed(() =>
  menus.value.map((item) => ({
    id: item.id,
    text: item.nama_menu,
    harga: item.harga,
  }))
);

const formSchema = Yup.object().shape({
  nama_menu: Yup.string().required("Nama Menu harus diisi"),
  jumlah: Yup.number().required("Jumlah harus diisi"),
  total_harga: Yup.number().required("Total Harga harus diisi"),
  status: Yup.string().required("Pilih status"),
  });

function getEdit() {
  block(document.getElementById("form-order"));
  ApiService.get("/order", props.selected)
    .then(({ data }) => {
      console.log(data);

      Order.value = {
        id_menu: data.id_menu || "",
        jumlah : data.jumlah || 0,
        total_harga: data.total_harga || 0,
        status: data.status || "on_delivery", // Ambil langsung dari data kurir
        menu: {
          nama_menu: data.menu?.nama_menu || "",
          harga: data.menu?.harga || 0,
        },
      };

      console.log(Order.value);
    })
    .catch((err) => {
      toast.error(err.response?.data?.message || "Gagal mengambil data");
    })
    .finally(() => {
      unblock(document.getElementById("form-order"));
    });
}

// const getPelanggan = async () => {
//   try {
//     const res = await axios.get("/pelanggan");
//     pelangganList.value = res.data.data.data || res.data.data; // sesuaikan struktur respons
//   } catch {
//     pelangganList.value = [];
//   }
// };


function submit() {
  const formData = new FormData();
  formData.append("id_menu", Order.value.id_menu);
  formData.append("jumlah", Order.value.jumlah)
  formData.append("total_harga", Order.value.total_harga);
  formData.append("status", Order.value.status);
  formData.append("id_kurir", currentKurir.value.kurir.id_kurir);


  if (props.selected) {
    formData.append("_method", "PUT");
  }

  block(document.getElementById("form-order"));
  axios({
    method: "post",
    url: props.selected
    ? `/order/store/${props.selected}`
    : "/order/store",
    data: formData,
    headers: {
      "Content-Type": "multipart/form-data",
    },
  })
    .then(() => {
      emit("close");
      emit("refresh");
      toast.success("Data berhasil disimpan");
      formRef.value.resetForm();
    })
    .catch((err: any) => {
      formRef.value.setErrors(err.response.data.errors);
      toast.error(err.response.data.message);
    })
    .finally(() => {
      unblock(document.getElementById("form-order"));
    });
}

function onMenuChange(e: any) {
  const selected = menus.value.find((m) => m.id == Order.value.menu_id);
  if (selected) {
    Order.value.menu.nama_menu = selected.nama_menu;
  } else {
    Order.value.menu.nama_menu = "";
  }
}

// onMounted(async () => {
//   if (props.selected) {
//     getEdit();
//   }
//     try {
//         const res = await ApiService.get("menu");
//         menus.value = res.data.data || res.data;
//     } catch  {
//         menus.value = [];
//     }
// });

onMounted(() => {
    // Jika form baru (bukan edit), otomatis mengisi data kurir yang login
    Order.value.id_kurir = currentKurir || "";
    console.log(Order.value.id_kurir);
    if (props.selected) getEdit(); // Jika ada selected, ambil data transaksi untuk diedit
  });

watch(
    () => props.selected,
    () => {
        if (props.selected) {
            getEdit();
        }
    }
);
</script>

<template>
  <VForm class="form card mb-10" @submit="submit" id="form-order" ref="formRef">
    <div class="card-header align-items-center">
      <h2 class="mb-0">{{ selected ? "Edit" : "Tambah" }} Order</h2>
      <button type="button" class="btn btn-sm btn-light-danger ms-auto" @click="emit('close')">
        Batal <i class="la la-times-circle p-0"></i>
      </button>
    </div>
    <div class="card-body">
      <div class="row">
        <div class="col-md-6">
          <div class="fv-row mb-7">
            <label class="form-label fw-bold fs-6 required">Pilih Menu</label>
            <Field
            name="id_menu"
            type="hidden"
            v-model="Order.id_menu"
            >
            <select2
            placeholder="Pilih Menu"
            class="form-select-solid"
            :options="menuOptions"
            name="id_menu"
            v-model="Order.id_menu"
            @change="onMenuChange"
            >
            </select2>
            </Field>
          </div>
        </div>
        <div class="col-md-6">
          <div class="fv-row mb-7">
            <label class="form-label fw-bold fs-6 required">Jumlah</label>
            <input
              type="number"
              v-model="Order.jumlah"
              class="form-control form-control-lg form-control-solid"
              placeholder="Jumlah Pesanan"
            />
          </div>
        </div>

        <div class="col-md-3 mb-7">
          <label class="form-label required fw-bold" for="kurir">Kurir</label>
          <Field type="text" name="id_kurir" class="form-control" :value="`${authStore.user.name}`" readonly>
          </Field>
          <ErrorMessage name="id_kurir" class="text-danger small" />
        </div>

      </div>
    </div>
    <div class="card-footer d-flex">
      <button
        type="submit"
        class="btn btn-primary btn-sm ms-auto"
      > Simpan
      </button>
    </div>
  </VForm>
</template>
