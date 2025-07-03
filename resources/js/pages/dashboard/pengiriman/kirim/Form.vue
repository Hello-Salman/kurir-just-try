<script setup lang="ts">
import { block, unblock } from "@/libs/utils";
import { onMounted, ref, watch, computed } from "vue";
import * as Yup from "yup";
import axios from "@/libs/axios";
import { toast } from "vue3-toastify";
import { Field, Form as VForm, ErrorMessage } from "vee-validate";
import type { Order } from "@/types";
import { useAuthStore } from "@/stores/auth";
import ApiService from "@/core/services/ApiService";

const auth = useAuthStore();
console.log("Auth User:", auth);

const Kirim = ref({
    id_order: "",
    id_kurir: "",
    status: "",
    order: {
        id: "",
        no_resi: "",
        alamat_tujuan: "",
        jumlah: "",
        total_harga: "",
    },
    kurir: {
        id: "",
        name: "",
        email: "",
        phone: "",
    }
} as any);

const formRef = ref();

const props = defineProps({
  selected: {
    type: String,
    default: null,
  },
});

const emit = defineEmits(["close", "refresh"]);

const order = ref<Order[]>([]);
const orderOptions = computed(() =>
  order.value
    .filter((item) => {
      // Jika sedang dalam mode edit dan ini adalah order yang dipilih,
      // tampilkan meskipun sudah dikirim
      if (props.selected && Kirim.value.id_order === item.id) {
        return true;
      }
      // Untuk order lain, hanya tampilkan yang belum dikirim
      return item.status !== "dikirim";
    })
    .map((item) => ({
      id: item.id,
      text: item.no_resi,
    }))
);

const kurir = ref<any[]>([]);
const kurirOptions = computed(() =>
  kurir.value.map((item) => ({
    id: item.id,
    text: item.user?.name || item.user?.nama || item.name || item.nama,
    name: item.user?.name || item.user?.nama || item.name || item.nama,
    email: item.user?.email || item.email,
    phone: item.user?.phone || item.phone,
  }))
);

// Schema validasi yang dinamis berdasarkan role user
const formSchema = computed(() => {
  const baseSchema = {
    id_order: Yup.string().required("Pilih order"),
    status: Yup.string().required("Pilih status kirim"),
  };

  // Jika user bukan kurir, tambahkan validasi id_kurir
  if (auth.user?.role?.name !== "kurir") {
    baseSchema.id_kurir = Yup.string().required("Pilih kurir");
  }

  return Yup.object().shape(baseSchema);
});

function getEdit() {
  block(document.getElementById("form-kirim"));
  ApiService.get("kirim", props.selected)
    .then(({ data }) => {
      console.log("Edit Data:", data);

      Kirim.value = {
        id_order: data.data.id_order || "",
        id_kurir: data.data.id_kurir || "",
        status: data.data.status || "Dikirim",
        order: {
          id: data.data.order?.id || "",
          no_resi: data.data.order?.no_resi || "",
          alamat_tujuan: data.data.order?.alamat_tujuan || "",
          jumlah: data.data.order?.jumlah || "",
          total_harga: data.data.order?.total_harga || "",
        },
        kurir: {
          id: data.data.kurir?.id || "",
          name: data.data.kurir?.name || data.data.kurir?.user?.name || "",
          email: data.data.kurir?.email || data.data.kurir?.user?.email || "",
          phone: data.data.kurir?.phone || data.data.kurir?.user?.phone || "",
        }
      };

      console.log("Kirim Value after edit:", Kirim.value);
    })
    .catch((err) => {
      console.error("Error:", err);
      toast.error(err.response?.data?.message || "Gagal mengambil data");
    })
    .finally(() => {
      unblock(document.getElementById("form-kirim"));
    });
}

function submit() {
  console.log("Submit data:", {
    id_order: Kirim.value.id_order,
    id_kurir: Kirim.value.id_kurir,
    status: Kirim.value.status
  });

  // Validasi manual sebelum submit
  if (!Kirim.value.id_order) {
    toast.error("Pilih order terlebih dahulu");
    return;
  }

  if (!Kirim.value.id_kurir) {
    toast.error("ID Kurir tidak ditemukan. Pastikan akun Anda terdaftar sebagai kurir.");
    return;
  }

  if (!Kirim.value.status) {
    toast.error("Pilih status kirim terlebih dahulu");
    return;
  }

  const formData = new FormData();
  formData.append("id_order", Kirim.value.id_order);
  formData.append("id_kurir", Kirim.value.id_kurir);
  formData.append("status", Kirim.value.status);

  if (props.selected) {
    formData.append("_method", "PUT");
  }

  block(document.getElementById("form-kirim"));
  axios({
    method: "post",
    url: props.selected ? `/kirim/${props.selected}` : "/kirim/store",
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
      console.error("Submit Error:", err);
      if (err.response?.data?.errors) {
        formRef.value.setErrors(err.response.data.errors);
      }
      toast.error("Gagal menyimpan data: " + (err.response?.data?.message || err.message));
    })
    .finally(() => {
      unblock(document.getElementById("form-kirim"));
    });
}

function onOrderChange() {
  console.log("Order changed:", Kirim.value.id_order);
  console.log("Available Orders:", order.value);
  const selected = order.value.find((o) => o.id == Kirim.value.id_order);
  console.log("Selected Order:", selected);
  if (selected) {
    Kirim.value.order.no_resi = selected.no_resi;
    Kirim.value.order.alamat_tujuan = selected.alamat_tujuan;
    Kirim.value.order.jumlah = selected.jumlah;
    Kirim.value.order.total_harga = selected.total_harga;
  } else {
    Kirim.value.order.no_resi = "";
    Kirim.value.order.alamat_tujuan = "";
    Kirim.value.order.jumlah = "";
    Kirim.value.order.total_harga = "";
  }
}

onMounted(async () => {
  try {
    // Ambil data order
    const orderRes = await ApiService.get("order");
    order.value = orderRes.data.data || orderRes.data;
    console.log("Orders loaded:", order.value);

    // Ambil data kurir
    const kurirRes = await ApiService.get("master/kurir");
    kurir.value = kurirRes.data.data || kurirRes.data;
    console.log("Kurir loaded:", kurir.value);
    console.log("Current user:", auth.user);

    // Set default kurir berdasarkan role user
    if (auth.user?.role?.name === "kurir") {
      // Cek apakah user memiliki data kurir
      if (auth.user.kurir?.id) {
        Kirim.value.id_kurir = auth.user.kurir.id;
        console.log("Kurir ID set from auth.user.kurir:", auth.user.kurir.id);
      } else {
        // Jika tidak ada kurir.id di auth.user, cari dari data kurir berdasarkan user_id
        const userKurir = kurir.value.find(k => k.user_id === auth.user.id || k.user?.id === auth.user.id);
        if (userKurir) {
          Kirim.value.id_kurir = userKurir.id;
          console.log("Kurir ID found from kurir list:", userKurir.id);
        } else {
          console.warn("User adalah kurir tapi tidak ditemukan data kurir yang sesuai");
          // Tidak menampilkan error toast, hanya log warning
          Kirim.value.id_kurir = "";
        }
      }
    } else {
      // Jika bukan kurir, kosongkan id_kurir
      Kirim.value.id_kurir = "";
      console.log("User bukan kurir, id_kurir dikosongkan");
    }

    // Jika ada selected (mode edit), ambil data edit
    if (props.selected) {
      getEdit();
    }
  } catch (error) {
    console.error("Error loading data:", error);
    order.value = [];
    kurir.value = [];
    // Tampilkan error yang lebih informatif
    toast.error("Gagal memuat data: " + (error.response?.data?.message || error.message));
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

watch(
  () => Kirim.value.id_order,
  (newVal) => {
    if (newVal) {
      onOrderChange();
    }
  }
);
</script>

<template>
  <VForm class="form card mb-10" @submit="submit" :validation-schema="formSchema" id="form-kirim" ref="formRef">
    <div class="card-header align-items-center">
      <h2 class="mb-0">{{ selected ? "Edit" : "Tambah" }} Kirim</h2>
      <button type="button" class="btn btn-sm btn-light-danger ms-auto" @click="emit('close')">
        Batal <i class="la la-times-circle p-0"></i>
      </button>
    </div>
    <div class="card-body">
      <div class="row">
        <!-- Pilih Order -->
        <div class="col-md-6 mb-4">
          <label class="form-label fw-bold fs-6 required">Pilih Order</label>
          <Field name="id_order" v-model="Kirim.id_order">
            <select2
              placeholder="Pilih Order"
              class="form-select"
              :options="orderOptions"
              name="id_order"
              v-model="Kirim.id_order"
              @change="onOrderChange"
            ></select2>
          </Field>
          <ErrorMessage name="id_order" class="text-danger" />
        </div>

        <!-- Status Kirim -->
        <div class="col-md-6">
            <div class="fv-row mb-7">
                <label class="form-label fw-bold fs-6 required">Status Kirim</label>
                <Field as="select" class="form-select form-select-lg form-select-solid" name="status" v-model="Kirim.status">
                    <option value="" disabled>Pilih Status</option>
                    <option value="Dikirim">Dikirim</option>
              <option value="Terkirim">Terkirim</option>
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

<!-- Pilih Kurir -->
<!-- <div class="col-md-6 mb-4">
  <label class="form-label fw-bold fs-6 required">Pilih Kurir</label>
  <Field name="id_kurir" v-model="Kirim.id_kurir">
    <select2
      placeholder="Pilih Kurir"
      class="form-select"
      :options="kurirOptions"
      v-model="Kirim.id_kurir"
      @change="onKurirChange"
      ></select2>
    </Field>
  <ErrorMessage name="id_kurir" class="text-danger" />
</div> -->

<!-- Detail Order yang dipilih -->
<!-- <div class="col-md-12 mb-4" v-if="Kirim.order.no_resi">
    <div class="card bg-light">
        <div class="card-header">
            <h5 class="mb-0">Detail Order</h5>
        </div>
        <div class="card-body">
      <div class="row">
        <div class="col-md-6">
          <p><strong>No. Resi:</strong> {{ Kirim.order.no_resi }}</p>
          <p><strong>Jumlah:</strong> {{ Kirim.order.jumlah || 'N/A' }}</p>
        </div>
        <div class="col-md-6">
          <p><strong>Total Harga:</strong> {{ Kirim.order.total_harga ? 'Rp ' + Number(Kirim.order.total_harga).toLocaleString('id-ID') : 'N/A' }}</p>
           <p><strong>Tanggal Order:</strong> {{ Kirim.order.tanggal_order || 'N/A' }}</p> -->
        <!-- </div>
        <div class="col-md-12">
          <p><strong>Alamat Tujuan:</strong> {{ Kirim.order.alamat_tujuan || 'N/A' }}</p>
        </div>
      </div>
    </div>
  </div>
</div> -->

<!-- Detail Kurir yang dipilih -->
<!-- <div class="col-md-12 mb-4" v-if="Kirim.kurir.name">
    <div class="card bg-light">
        <div class="card-header">
            <h5 class="mb-0">Detail Kurir</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
          <p><strong>Nama:</strong> {{ Kirim.kurir.name }}</p>
        </div>
        <div class="col-md-4">
          <p><strong>Email:</strong> {{ Kirim.kurir.email || 'N/A' }}</p>
        </div>
        <div class="col-md-4">
          <p><strong>Telepon:</strong> {{ Kirim.kurir.phone || 'N/A' }}</p>
        </div>
    </div>
    </div>
</div>
</div> -->
