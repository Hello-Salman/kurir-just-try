<script setup lang="ts">
import { block, unblock } from "@/libs/utils";
import { ref, onMounted, watch } from "vue";
import * as Yup from "yup";
import axios from "@/libs/axios";
import { toast } from "vue3-toastify";

const props = defineProps({
  selected: {
    type: String,
    default: null,
  },
});

const emit = defineEmits(["close", "refresh"]);

const menu = ref({
  nama_menu: "", // Sesuai dengan kolom di backend
  harga: "",
  kategori: "",
  status: "",
  photo: null, // Gunakan null untuk file
});

const fileTypes = ref(["image/jpeg", "image/png", "image/jpg"]);
const photo = ref<any>([]); // Untuk menyimpan file foto
const formRef = ref();

const formSchema = Yup.object().shape({
  nama_menu: Yup.string().required("Nama Menu harus diisi"), // Sesuai dengan backend
  harga: Yup.number().required("Harga harus diisi").min(0, "Harga tidak boleh negatif"),
  kategori: Yup.string().required("Kategori harus diisi"),
  status: Yup.string().required("Status harus diisi"),
});

function getEdit() {
  block(document.getElementById("form-menu"));
  axios
    .get(`/menu/${props.selected}`)
    .then(({ data }) => {
      menu.value = {
        nama_menu: data.nama_menu,
        harga: data.harga,
        kategori: data.kategori,
        status: data.status,
        photo: null, // Kosongkan karena file tidak bisa di-bind langsung
      };
      if (data.photo) {
        photo.value = [`/storage/${data.photo}`]; // Muat foto jika ada
      }
    })
    .catch((err) => {
      toast.error(err.response.data.message || "Gagal memuat data");
    })
    .finally(() => {
      unblock(document.getElementById("form-menu"));
    });
}

function submit() {
  const isEdit = !!props.selected;
  const url = isEdit ? `/menu/${props.selected}` : "/menu/store";

  const formData = new FormData();
  formData.append("nama_menu", menu.value.nama_menu);
  formData.append("harga", menu.value.harga);
  formData.append("kategori", menu.value.kategori);
  formData.append("status", menu.value.status);

  if (photo.value.length > 0 && photo.value[0].file) {
    formData.append("photo", photo.value[0].file);
  }

  // Tambahkan _method untuk spoof PUT jika sedang edit
  if (isEdit) {
    formData.append("_method", "PUT");
  }

  block(document.getElementById("form-menu"));
  axios
    .post(url, formData, {
      headers: { "Content-Type": "multipart/form-data" },
    })
    .then(() => {
      emit("close");
      emit("refresh");
      toast.success("Data berhasil disimpan");
      formRef.value.resetForm();
      photo.value = [];
    })
    .catch((err) => {
      formRef.value.setErrors(err.response?.data?.errors || {});
      toast.error(err.response?.data?.message || "Gagal menyimpan data");
    })
    .finally(() => {
      unblock(document.getElementById("form-menu"));
    });
}

onMounted(() => {
  if (props.selected) {
    getEdit();
  }
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
  <VForm
    class="form card mb-10"
    @submit="submit"
    :validation-schema="formSchema"
    id="form-menu"
    ref="formRef"
  >
    <div class="card-header align-items-center">
      <h2 class="mb-0">{{ selected ? "Edit" : "Tambah" }} Produk</h2>
      <button
        type="button"
        class="btn btn-sm btn-light-danger ms-auto"
        @click="emit('close')"
      >
        Batal
        <i class="la la-times-circle p-0"></i>
      </button>
    </div>
    <div class="card-body">
      <div class="row">
        <div class="col-md-6">
          <div class="fv-row mb-7">
            <label class="form-label fw-bold fs-6 required">Nama Produk</label>
            <Field
              class="form-control form-control-lg form-control-solid"
              type="text"
              name="nama_menu"
              v-model="menu.nama_menu"
              placeholder="Masukkan Nama Menu"
            />
            <ErrorMessage name="nama_menu" class="text-danger" />
          </div>
        </div>
        <div class="col-md-6">
          <div class="fv-row mb-7">
            <label class="form-label fw-bold fs-6 required">Harga</label>
            <Field
              class="form-control form-control-lg form-control-solid"
              type="number"
              name="harga"
              v-model="menu.harga"
              placeholder="Masukkan Harga"
            />
            <ErrorMessage name="harga" class="text-danger" />
          </div>
        </div>
        <div class="col-md-6">
          <div class="fv-row mb-7">
            <label class="form-label fw-bold fs-6 required">Kategori</label>
            <Field
              as="select"
              class="form-select form-select-lg form-select-solid"
              name="kategori"
              v-model="menu.kategori"
            >
              <option value="" disabled>Pilih Kategori</option>
              <option value="Makanan">Makanan</option>
              <option value="Minuman">Minuman</option>
              <option value="Snack">Snack</option>
            </Field>
            <ErrorMessage name="kategori" class="text-danger" />
          </div>
        </div>
        <div class="col-md-6">
          <div class="fv-row mb-7">
            <label class="form-label fw-bold fs-6">Foto</label>
            <file-upload
              :files="photo"
              :accepted-file-types="fileTypes"
              required
              v-on:updatefiles="(file) => (photo = file)"
          ></file-upload>
            <ErrorMessage name="photo" class="text-danger" />
          </div>
        </div>
        <div class="col-md-6">
        <div class="fv-row mb-7">
          <label class="form-label fw-bold fs-6 required">Status</label>
          <Field
            as="select"
            class="form-select form-select-lg form-select-solid"
            name="status"
            v-model="menu.status"
          >
            <option value="" disabled>Pilih Status</option>
            <option value="Tersedia">Tersedia</option>
            <option value="Habis">Habis</option>
          </Field>
          <ErrorMessage name="status" class="text-danger" />
        </div>
      </div>
      </div>
    </div>
    <div class="card-footer d-flex">
      <button type="submit" class="btn btn-primary btn-sm ms-auto">Simpan</button>
    </div>
  </VForm>
</template>
