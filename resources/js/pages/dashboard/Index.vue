<template>
  <div class="container mt-5">
    <!-- FORM -->
    <div class="card shadow-sm border-0 mb-4">
      <div class="card-body">
        <h3 class="mb-4 text-center">📦 Cek Ongkos Kirim</h3>
        <div class="row g-3">
          <!-- ORIGIN -->
          <div class="col-md-6">
            <label class="form-label fw-bold">Provinsi Asal</label>
            <select v-model="provinceOrigin" class="form-select" @change="fetchCities('origin')">
              <option value="0">-- pilih provinsi asal --</option>
              <option v-for="(name, id) in provinces" :key="id" :value="id">{{ name }}</option>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label fw-bold">Kota Asal</label>
            <select v-model="cityOrigin" class="form-select">
              <option value="">-- pilih kota asal --</option>
              <option v-for="(name, id) in citiesOrigin" :key="id" :value="id">{{ name }}</option>
            </select>
          </div>

          <!-- DESTINATION -->
          <div class="col-md-6">
            <label class="form-label fw-bold">Provinsi Tujuan</label>
            <select v-model="provinceDestination" class="form-select" @change="fetchCities('destination')">
              <option value="0">-- pilih provinsi tujuan --</option>
              <option v-for="(name, id) in provinces" :key="id" :value="id">{{ name }}</option>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label fw-bold">Kota Tujuan</label>
            <select v-model="cityDestination" class="form-select">
              <option value="">-- pilih kota tujuan --</option>
              <option v-for="(name, id) in citiesDestination" :key="id" :value="id">{{ name }}</option>
            </select>
          </div>

          <!-- COURIER & WEIGHT -->
          <div class="col-md-6">
            <label class="form-label fw-bold">Kurir</label>
            <select v-model="courier" class="form-select">
              <option value="0">-- pilih kurir --</option>
              <option value="jne">JNE</option>
              <option value="pos">POS</option>
              <option value="tiki">TIKI</option>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label fw-bold">Berat (gram)</label>
            <input type="number" v-model.number="weight" class="form-control" placeholder="Contoh: 1000" />
          </div>
        </div>
      </div>
    </div>

    <!-- BUTTON -->
    <div class="text-center mb-4">
      <button
        class="btn btn-lg btn-primary px-5"
        @click="checkOngkir"
        :disabled="isProcessing"
      >
        <span v-if="isProcessing" class="spinner-border spinner-border-sm me-2"></span>
        {{ isProcessing ? "Memproses..." : "💵 CEK ONGKOS KIRIM" }}
      </button>
    </div>

    <!-- HASIL ONGKIR -->
    <div v-if="ongkirResults.length > 0" class="card shadow-sm border-0 mb-5">
      <div class="card-body">
        <h5 class="mb-3">📋 Hasil Ongkos Kirim:</h5>
        <transition-group name="fade" tag="ul" class="list-group">
          <li class="list-group-item fade-item" v-for="(cost, index) in ongkirResults" :key="index">
            <strong>{{ cost.service }}</strong> - Rp. {{ formatRupiah(cost.cost[0].value) }} ({{ cost.cost[0].etd }} hari)
          </li>
        </transition-group>
      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios";

export default {
  name: "Ongkir",
  data() {
    return {
      provinces: {},
      provinceOrigin: "0",
      cityOrigin: "",
      citiesOrigin: {},
      provinceDestination: "0",
      cityDestination: "",
      citiesDestination: {},
      courier: "0",
      weight: null,
      ongkirResults: [],
      isProcessing: false,
    };
  },
  created() {
    this.fetchProvinces();
  },
  methods: {
    fetchProvinces() {
      axios.get("/provinces").then((res) => {
        this.provinces = res.data;
        console.log(this.provinces);
      });
    },
    fetchCities(type) {
      const provinceId = type === "origin" ? this.provinceOrigin : this.provinceDestination;
      if (provinceId !== "0") {
        axios.get(`/cities/${provinceId}`).then((res) => {
          if (type === "origin") {
            this.citiesOrigin = res.data;
            this.cityOrigin = "";
          } else {
            this.citiesDestination = res.data;
            this.cityDestination = "";
          }
        });
      }
    },
    checkOngkir() {
      if (
        !this.cityOrigin ||
        !this.cityDestination ||
        this.courier === "0" ||
        !this.weight
      ) {
        alert("Semua field harus diisi!");
        return;
      }

      this.isProcessing = true;
      this.ongkirResults = [];

      axios
        .post("/ongkir", {
          city_origin: this.cityOrigin,
          city_destination: this.cityDestination,
          courier: this.courier,
          weight: this.weight,
        })
        .then((res) => {
          this.ongkirResults = res.data[0].costs || [];
        })
        .catch((err) => {
          console.error(err);
        })
        .finally(() => {
          this.isProcessing = false;
        });
    },
    formatRupiah(value) {
      return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
      }).format(value);
    },
  },
};
</script>

<style scoped>
.fade-item {
  transition: all 0.5s ease;
}
.fade-item-enter-from {
  opacity: 0;
  transform: translateY(10px);
}
.fade-item-enter-to {
  opacity: 1;
  transform: translateY(0);
}
</style>
