<script setup lang="ts">
import { ref, onMounted, watch, computed } from "vue";
import * as Yup from "yup";
import axios from "@/libs/axios";
import { toast } from "vue3-toastify";
import type { Order, Menu } from "@/types";
import { block, unblock } from "@/libs/utils";
import { useAuthStore } from "@/stores/auth";
import { useForm, Field, ErrorMessage, Form as VForm } from "vee-validate";
import ApiService from "@/core/services/ApiService";
import { orderColumns } from "@tanstack/vue-table";


const authStore = useAuthStore();
const cutrrentUser = computed(() => authStore.user);


const Order = ref({
    id_menu: "",
    jumlah: "",
    status: "",
    menu: {
        nama_menu: "",
    harga: 0,
},
}as any);
const formRef = ref();

// Data provinces, cities asal dan tujuan
const provinces = ref<Record<string, string>>({});
const citiesOrigin = ref<Record<string, string>>({});
const citiesDestination = ref<Record<string, string>>({});

const provinceOrigin = ref("0");
const cityOrigin = ref("");
const provinceDestination = ref("0");
const cityDestination = ref("");
// const penerima = ref("");
const alamat_tujuan = ref("");
const alamat_asal = ref("");
const total_harga = ref(0);
// const nama_menu = ref("");

// Kurir / ekspedisi dan layanan
const couriers = ref([
  { code: "jne", name: "JNE" },
  { code: "tiki", name: "TIKI" },
  { code: "pos", name: "POS Indonesia" },
]);

const selectedCourier = ref("");  // ekspedisi/kurir dipilih
// const services = ref<ServiceCost[]>([]);
const services = ref<{ service: string; description: string; cost: number; etd: string }[]>([]);
// const services = ref<Array<{ service: string; description: string; cost: number }>>([]);
const selectedService = ref("");
const berat_barang = ref<number | null>(null);
const biaya = ref<number>(0);

const menu = ref<Menu[]>([]);
const menuOptions = computed(() =>
menu.value.map((item) => ({
    id: item.id,
    text: item.nama_menu,
    harga: item.harga,
}))
);
const props = defineProps({
    selected: {
    type: String,
    default: null,
},
});
const emit = defineEmits(["close", "refresh"]);

const formSchema = Yup.object().shape({
    nama_menu: Yup.string().required("Nama Menu harus diisi"),
    jumlah: Yup.number().required("Jumlah harus diisi"),
    status: Yup.string().required("Pilih status"),
    alamat_asal: Yup.string().required("Alamat Tujuan harus diisi"),
    alamat_tujuan: Yup.string().required("Alamat Tujuan harus diisi"),
    provinceOrigin: Yup.string().required("Provinsi asal harus dipilih").notOneOf(["0"], "Provinsi asal harus dipilih"),
    cityOrigin: Yup.string().required("Kota asal harus dipilih"),
    provinceDestination: Yup.string().required("Provinsi tujuan harus dipilih").notOneOf(["0"], "Provinsi tujuan harus dipilih"),
    cityDestination: Yup.string().required("Kota tujuan harus dipilih"),
    kurir: Yup.string().required("Ekspedisi harus dipilih"),
    jenis_layanan: Yup.string().required("Layanan harus dipilih"),
    berat_barang: Yup.number().required("Berat barang harus diisi").min(0.1, "Berat minimal 0.1 kg"),
    //   no_hp_penerima: Yup.string().required("No HP Penerima harus diisi"),
    // biaya: Yup.string().required("Biaya")
});

  const { handleSubmit, errors, resetForm, } = useForm({
  validationSchema: formSchema,
  initialValues: {
      // nama_barang: "",
      // penerima: "",
    // no_hp_penerima: "",
    alamat_tujuan: "",
    alamat_asal: "",
    provinceOrigin: "0",
    cityOrigin: "",
    provinceDestination: "0",
    cityDestination: "",
    kurir: "",
    jenis_layanan: "",
    berat_barang: 0,
  }
});

// Fetch provinsi
const fetchProvinces = async () => {
  try {
    const res = await axios.get("/provinces");
    provinces.value = res.data;
  } catch (error) {
    toast.error("Gagal mengambil data provinsi");
  }
};

// Fetch kota berdasarkan provinsi
const fetchCities = async (type: "origin" | "destination") => {
  const provId = type === "origin" ? provinceOrigin.value : provinceDestination.value;
  if (provId === "0") return;
  try {
    const res = await axios.get(`/cities/${provId}`);
    if (type === "origin") {
      citiesOrigin.value = res.data;
      cityOrigin.value = "";
    } else {
      citiesDestination.value = res.data;
      cityDestination.value = "";
    }
  } catch (error) {
    toast.error("Gagal mengambil data kota");
  }
};
const getSelectedCost = () => {
  console.log('All services:', services.value);
  console.log('Selected service:', selectedService.value);

  const service = services.value.find(s => s.service === selectedService.value);
  const cost = service?.cost ?? 0;
  biaya.value = cost;

  console.log('Selected cost:', cost);
  return cost;
};

const fetchOngkir = async () => {
  if (
    provinceOrigin.value === "0" || !cityOrigin.value ||
    provinceDestination.value === "0" || !cityDestination.value ||
    !selectedCourier.value || !berat_barang.value || berat_barang.value <= 0
  ) {
    console.log(berat_barang.value)
    services.value = [];
    selectedService.value = "";
    biaya.value = 0;
    console.log("Keluar")
    return;
  }

  try {
    block(document.getElementById("form-order"));
    console.log("city ", citiesOrigin.value)
    console.log("city ", citiesDestination.value)
    console.log("berat ", berat_barang.value)
    console.log("courier ", selectedCourier.value)
    const res = await axios.post("/cost", {
      origin: cityOrigin.value,
      destination: cityDestination.value,
      weight: Math.round(berat_barang.value * 1000), // gram
      courier: selectedCourier.value,
    });



    // const resultServices = res.data.rajaongkir.results[0]?.costs || [];
    services.value = res.data.map((s: any) => ({
      service: s.service,
      description: s.description,
      cost: s.cost[0].value,
      etd: s.cost[0].etd
      // courier: selectedCourier.value.code,
    }));

    // Reset selected service dan biaya
    selectedService.value = "";
    biaya.value = 0;
    console.log("1", biaya.value)
  } catch (error) {
    toast.error("Gagal mengambil data ongkir");
    services.value = [];
    selectedService.value = "";
    biaya.value = 0;
    console.log("2", biaya.value)
  } finally {
    unblock(document.getElementById("form-order"));
  }
};

// Watchers untuk fetch ongkir saat data berubah
watch([provinceOrigin, cityOrigin, provinceDestination, cityDestination, selectedCourier, berat_barang], () => {
  fetchOngkir();
});
watch(selectedService, (val) => {
  const service = services.value.find(s => s.service === val);
  biaya.value = service ? service.cost : 0;
  getSelectedCost();
});



function getEdit() {
  block(document.getElementById("form-order"));
  ApiService.get("/order", props.selected)
    .then(({ data }) => {
      console.log(data);

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
function hitungTotal(){
    total_harga.value = (Order.value.jumlah * Order.value.menu.harga) + biaya.value;
}

// Submit handler
// function onSubmit() {
// const onSubmit = handleSubmit((values) => {
function onSubmit() {

    hitungTotal();

  console.log("Submit data");
  const formData = new FormData();

  formData.append("id_menu", Order.value.id_menu);
  formData.append("jumlah", Order.value.jumlah);

  formData.append("tujuan_provinsi_id", provinceDestination.value);
  formData.append("tujuan_kota_id", cityDestination.value);
  formData.append("alamat_tujuan", alamat_tujuan.value);

  formData.append("asal_provinsi_id", provinceOrigin.value);
  formData.append("asal_kota_id", cityOrigin.value);
  formData.append("alamat_asal", alamat_asal.value);

  formData.append("ekspedisi", selectedCourier.value);
  formData.append("jenis_layanan", selectedService.value);
  formData.append("berat_barang", berat_barang.value?.toString() || "0");
  formData.append("biaya", biaya.value.toString());
  formData.append("total_harga", total_harga.value.toString());

    if (props.selected) {
    formData.append("_method", "PUT");
  } else {
    formData.append("status", "on_delivery");
  }

  block(document.getElementById("form-order"));
  axios({
    method: "post",
    url: props.selected ? `/order/${props.selected}` : "/order/store",
    data: formData,
    headers: { "Content-Type": "multipart/form-data"
    },
  })
    .then(() => {
      emit("close");
      emit("refresh");
      toast.success("Data berhasil disimpan");
      formRef.value.resetForm(); // Reset form setelah submit
    })
    .catch((err: any) => {
      const message = err.response?.data?.message || "Terjadi kesalahan.";
      toast.error(message);
    })
    .finally(() => {
      unblock(document.getElementById("form-order"));
    });
}
// watch(selectedService, () => {
//   getSelectedCost();
// });

function onMenuChange() {
    console.log("Menu changed:", Order.value.id_menu);
    console.log("Available Menus:", menu.value);
  const selected = menu.value.find((m) => m.id == Order.value.id_menu);
  console.log("Selected Menu:", selected);
  if (selected) {
    Order.value.menu.nama_menu = selected.nama_menu;
    Order.value.menu.harga = selected.harga;
  } else {
    Order.value.menu.nama_menu = "";
    Order.value.menu.harga = 0;
  }
}

onMounted(async () => {
    // Fetch provinces and cities
    fetchProvinces();
  if (props.selected) {
    getEdit();
  }
    try {
        const res = await ApiService.get("menu");
        menu.value = res.data.data || res.data;
        console.log("Menu Loaded:", menu.value);
    } catch  {
        menu.value = [];
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
    () => Order.value.id_menu,
    (newVal) => {
        if (newVal) {
            onMenuChange();
        }
    }
);
</script>

<template>
  <VForm id="form-order" class="form card mb-10" @submit="onSubmit" :validation-schema="formSchema" ref="formRef">
    <div class="card-header align-items-center">
      <h2 class="mb-0">{{ props.selected ? "Edit" : "Tambah" }} Order</h2>
      <button type="button" class="btn btn-sm btn-light-danger ms-auto" @click="emit('close')">
        Batal <i class="la la-times-circle p-0"></i>
      </button>
    </div>
    <ErrorMessage name="nama_menu" />
    <ErrorMessage name="jumlah" />
    <ErrorMessage name="total_harga" />
    <ErrorMessage name="alamat_asal" />
    <ErrorMessage name="alamat_tujuan" />
    <ErrorMessage name="provinceOrigin" />
    <ErrorMessage name="cityOrigin" />
    <ErrorMessage name="provinceDestination" />
    <ErrorMessage name="kurir" />
    <ErrorMessage name="cityDestination" />
    <ErrorMessage name="jenis_layanan" />
    <ErrorMessage name="berat_barang" />
    <!-- <ErrorMessage name="status" /> -->
    <div class="card-body">
        <div class="row">

        <!-- Pilih Menu & Harga -->
        <div class="col-md-6 mb-4">
          <label class="form-label fw-bold fs-6 required">Pilih Menu</label>
          <Field name="nama_menu" type="hidden" v-model="Order.id_menu">
            <select2
              placeholder="Pilih Menu"
              class="form-select"
              :options="menuOptions"
              name="nama_menu"
              v-model="Order.id_menu"
              @change="onMenuChange"
            ></select2>
          </Field>
        </div>

        <!-- Jumlah -->
        <div class="col-md-6 mb-4">
          <label class="form-label fw-bold fs-6 required">Jumlah</label>
          <Field
            name="jumlah"
            type="number"
            :value="Order.jumlah"
            @input="Order.jumlah = $event.target.value"
            class="form-control"
            placeholder="Jumlah Pesanan"
            />
        <ErrorMessage name="jumlah" class="text-danger small" />
        </div>

        <div class="col-md-6 mb-4">
          <label class="form-label fw-bold fs-6 required">Harga Satuan</label>
          <input
            name="harga"
            type="text"
            v-model="Order.menu.harga"
            class="form-control"
            placeholder="Harga Satuan"
            readonly
          />
        </div>

        <!-- <div class="col-md-6">
        <div class="fv-row mb-7">
          <label class="form-label fw-bold fs-6 required">Status</label>
          <Field
            as="select"
            class="form-select"
            name="status"
            v-model="Order.status"
          >
            <option value="" disabled>Pilih Status</option>
            <option value="on_delivery">Di Proses</option>
            <option value="selesai">Selesai</option>
            <option value="batal">Dibatalkan</option>
          </Field>
          <ErrorMessage name="status" class="text-danger" />
        </div>
      </div> -->

        <div class="col-md-6">
            <div class="fv-row mb-7">
            <label class="form-label required fw-bold">Berat Barang (Kg)</label>
            <Field type="number" v-model="berat_barang" class="form-control" placeholder="Contoh: 0.5" min="0.1"
                step="0.1" name="berat_barang" />
            <ErrorMessage name="berat_barang" class="text-danger small" />
            <!-- <div v-if="errors.berat_barang" class="text-danger">{{ errors.berat_barang }}</div> -->
            </div>
        </div>

        <!-- Provinsi & Kota Tujuan -->
        <div class="col-md-6 mb-4">
          <label class="form-label required fw-bold">Provinsi Tujuan</label>
          <Field as="select" name="provinceDestination" v-model="provinceDestination" class="form-control"
            @change="fetchCities('destination')">
            <option value="0">-- Pilih Provinsi Tujuan --</option>
            <option v-for="(name, id) in provinces" :key="id" :value="id">{{ name }}</option>
          </Field as="select">
          <ErrorMessage name="provinceDestination" class="text-danger small" />
        </div>
        <div class="col-md-6 mb-4">
          <label class="form-label required fw-bold">Kota Tujuan</label>
          <Field as="select" name="cityDestination" v-model="cityDestination" class="form-control">
            <option value="">-- Pilih Kota Tujuan --</option>
            <option v-for="(name, id) in citiesDestination" :key="id" :value="id">{{ name }}</option>
          </Field as="select">
          <ErrorMessage name="cityDestination" class="text-danger small" />
        </div>
        <div class="col-md-6 mb-4">
          <label class="form-label required fw-bold">Alamat Lengkap Penerima</label>
          <Field type="text" name="alamat_tujuan" v-model="alamat_tujuan" class="form-control"
            placeholder="Masukan Alamat Lengkap" />
          <ErrorMessage name="alamat_tujuan" class="text-danger small" />
        </div>

        <!-- Provinsi & Kota Asal -->
        <div class="col-md-6 mb-4">
          <label class="form-label required fw-bold">Provinsi Asal</label>
          <Field as="select" name="provinceOrigin" v-model="provinceOrigin" class="form-control"
            @change="fetchCities('origin')">
            <option value="0">-- Pilih Provinsi Asal --</option>
            <option v-for="(name, id) in provinces" :key="id" :value="id">{{ name }}</option>
          </Field as="select">
          <ErrorMessage name="provinceOrigin" class="text-danger small" />
        </div>
        <div class="col-md-6 mb-4">
          <label class="form-label required fw-bold">Kota Asal</label>
          <Field as="select" name="cityOrigin" v-model="cityOrigin" class="form-control">
            <option value="">-- Pilih Kota Asal --</option>
            <option v-for="(name, id) in citiesOrigin" :key="id" :value="id">{{ name }}</option>
          </Field as="select">
          <ErrorMessage name="cityOrigin" class="text-danger small" />
        </div>
        <div class="col-md-6 mb-4">
          <label class="form-label required fw-bold">Alamat Pengambilan Barang</label>
          <Field type="text" name="alamat_asal" v-model="alamat_asal" class="form-control"
            placeholder="Masukan Alamat Lengkap" />
          <ErrorMessage name="alamat_asal" class="text-danger small" />
        </div>

        <div class="col-md-6">
            <div class="fv-row mb-7">
                <label class="form-label required fw-bold">Kurir</label>
                <Field as="select" v-model="selectedCourier" class="form-control" name="kurir">
                    <option value="">-- Pilih Kurir --</option>
                    <option v-for="c in couriers" :key="c.code" :value="c.code">{{ c.name }}</option>
                </Field as="select">
                <ErrorMessage name="kurir" class="text-danger small" />
                <!-- <div v-if="errors.kurir" class="text-danger">{{ errors.kurir }}</div> -->
            </div>
        </div>

        <div class="col-md-6">
            <div class="fv-row mb-7">
            <label for="jenis_layanan" class="form-label required fw-bold">Jenis Layanan</label>
            <Field as="select" id="jenis_layanan" name="jenis_layanan" class="form-select" v-model="selectedService"
                @change="getSelectedCost" :disabled="services.length === 0">
                <option value="">{{ services.length === 0 ? 'Tidak ada jenis layanan tersedia' : 'Pilih jenis layanan' }}
                </option>
                <option v-for="service in services" :key="service.service" :value="service.service">
                    {{ service.service }} - Rp{{ Number(service.cost).toLocaleString() }} { {{ service.etd }}
                    Hari }
                </option>
            </Field as="select">
            <ErrorMessage name="jenis_layanan" class="text-danger small" />
            </div>
        </div>

        <div class="col-md-6">
            <div class="fv-row mb-7">
                <label class="form-label fw-bold">Biaya (Rp)</label>
                <input type="text" name="biaya" class="form-control"
                :value="services.length > 0 && biaya ? biaya.toLocaleString('id-ID') : '-'" readonly />
            </div>
        </div>

      </div>
    </div>
    <div class="card-footer d-flex">
        <button type="button" @click="onSubmit" class="btn btn-primary ms-auto"> Simpan </button>
    </div>
  </VForm>
</template>
