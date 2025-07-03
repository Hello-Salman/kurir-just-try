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
        // tanggal_order: "",
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

const rating = ref<number | null>(null);
const ulasan = ref("");

const emit = defineEmits(["close", "refresh"]);

const order = ref<Order[]>([]);
// const orderOptions = computed(() =>
//   order.value
//   .filter((item) => {
//       // Saat edit, order yang sedang diedit tetap tampil
//       if (props.selected && Kirim.value.id_order == item.id) return true;
//       // Hanya tampilkan order yang belum dikirim
//       return item.status !== "dikirim";
//     })
//   .map((item) => ({
//     id: item.id,
//     text: item.no_resi,
//      // tanggal_order: item.tan
//      // ggal_order,
//   }))
// );

const kurir = ref<any[]>([]);
// const kurirOptions = computed(() =>
//   kurir.value.map((item) => ({
//     id: item.id,
//     text: item.user?.name || item.user?.nama || item.name || item.nama,
//     name: item.user?.name || item.user?.nama || item.name || item.nama,
//     email: item.user?.email || item.email,
//     phone: item.user?.phone || item.phone,
//   }))
// );

const formSchema = Yup.object().shape({
  id_order: Yup.string().required("Pilih order"),
//   id_kurir: Yup.string().required("Pilih kurir"),
  status: Yup.string().required("Pilih status kirim"),
});

function getEdit() {
  block(document.getElementById("form-rating"));
  ApiService.get("kirim", props.selected)
    .then(({ data }) => {
      console.log("Edit Data:", data);

      Kirim.value = {
        id_order: data.data.id_order || "",
        id_kurir: data.data.id_kurir || auth.user?.kurir?.id || "",
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

      console.log("Kirim Value:", Kirim.value);
    })
    .catch((err) => {
      console.error("Error:", err);
      toast.error(err.response?.data?.message || "Gagal mengambil data");
    })
    .finally(() => {
      unblock(document.getElementById("form-rating"));
    });
}

async function submit() {
  console.log("Submit id_kurir:", Kirim.value.id_kurir);
  const formData = new FormData();
  formData.append("id_order", Kirim.value.id_order);
  formData.append("id_kurir", Kirim.value.id_kurir);
  formData.append("status", Kirim.value.status);

  if (props.selected) {
    formData.append("_method", "PUT");
  }

  block(document.getElementById("form-rating"));
  try {
    await axios({
      method: "post",
      url: props.selected ? `/beri-rating/${props.selected}` : "/beri-rating/store",
      data: formData,
      headers: {
        "Content-Type": "multipart/form-data",
      },
    });

    // Jika status Terkirim dan rating diisi, kirim rating
    if (Kirim.value.status === "Terkirim" && rating.value && ulasan.value) {
      await axios.post("/beri-rating/store", {
        no_resi: Kirim.value.order.no_resi,
        rating: rating.value,
        ulasan: ulasan.value,
      });
      toast.success("Rating & ulasan berhasil disimpan");
    }

    emit("close");
    emit("refresh");
    toast.success("Data berhasil disimpan");
    formRef.value.resetForm();
    rating.value = null;
    ulasan.value = "";
  } catch (err: any) {
    if (err.response?.data?.errors) {
      formRef.value.setErrors(err.response.data.errors);
    }
    toast.error("Gagal menyimpan data: " + (err.response?.data?.message || err.message));
  } finally {
    unblock(document.getElementById("form-rating"));
  }
}

function onOrderChange() {
    console.log("Order changed:", Kirim.value.id_order);
    console.log("Avalaible Orders:", order.value);
  const selected = order.value.find((o) => o.id == Kirim.value.id_order);
  console.log("Selected Order:", selected);
  if (selected) {
    Kirim.value.order.no_resi = selected.no_resi;
    Kirim.value.order.alamat_tujuan = selected.alamat_tujuan;
  } else {
    Kirim.value.order.no_resi = "";
    Kirim.value.order.no_resi = 0;
    }
  }


// function onKurirChange() {
//   console.log("Kurir changed:", Kirim.value.id_kurir);
//   const selected = kurir.value.find((k) => k.id == Kirim.value.id_kurir);
//   console.log("Selected Kurir:", selected);

//   if (selected) {
//     Kirim.value.kurir = {
//       id: selected.id,
//       name: selected.user?.name || selected.name,
//       phone: selected.user?.phone || selected.phone,
//     };
//   } else {
//     Kirim.value.kurir = {
//       id: "",
//       name: "",
//       phone: "",
//     };
//   }
// }

onMounted(async () => {
  try {
    // Ambil data order
    const orderRes = await ApiService.get("order");
    order.value = orderRes.data.data || orderRes.data;
    console.log("User : ", auth.user);

    // Ambil data kurir
    const kurirRes = await ApiService.get("master/kurir");
    kurir.value = kurirRes.data.data || kurirRes.data;
    console.log("Kurir Loaded:", kurir.value);

    if (auth.user?.role?.name === "kurir") {
    if (auth.user.kurir?.id) {
        Kirim.value.id_kurir = auth.user.kurir.id;
    } else if (auth.user.kurir?.id) {
      Kirim.value.id_kurir = auth.user.kurir.id;
    } else {
        toast.error("Akun Anda belum terdaftar sebagai kurir.");
        Kirim.value.id_kurir = "";
    }
}

    if (props.selected) {
      getEdit();
    }
  } catch (error) {
    console.error("Error loading data:", error);
    order.value = [];
    kurir.value = [];
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

// watch(
//   () => Kirim.value.id_kurir,
//   (newVal) => {
//     if (newVal) {
//       onKurirChange();
//     }
//   }
// );
</script>

<template>
  <VForm class="form card mb-10" @submit="submit" :validation-schema="formSchema" id="form-rating" ref="formRef">
    <div class="card-header align-items-center">
      <h2 class="mb-0">{{ selected ? "Edit" : "Tambah" }} Kirim</h2>
      <button type="button" class="btn btn-sm btn-light-danger ms-auto" @click="emit('close')">
        Batal <i class="la la-times-circle p-0"></i>
      </button>
    </div>
    <div class="card-body">
      <div class="row">
        <div v-if="Kirim.status === 'Terkirim'" class="row mt-3">
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Rating (1-5)</label>
                <input type="number" min="1" max="5" class="form-control" v-model="rating" />
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Ulasan</label>
                <textarea class="form-control" v-model="ulasan" rows="2" placeholder="Tulis ulasan..."></textarea>
            </div>
        </div>
        <!-- Pilih Order -->
        <!-- <div class="col-md-6 mb-4">
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
        </div> -->


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
