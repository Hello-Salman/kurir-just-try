<template>
  <div class="cek-resi-page">
    <h1 class="mb-4 text-center">Cek Resi Pengiriman</h1>
    <form @submit.prevent="cekResi" class="cek-resi-form">
      <div class="mb-3">
        <label for="no_resi" class="form-label">Pilih Order</label>
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
      </div>
      <div class="mb-3">
        <label for="courier" class="form-label">ekspedisi</label>
        <select id="courier" v-model="courier" class="form-select" required>
          <option value="">Pilih Kurir</option>
          <option value="jne">JNE</option>
          <option value="tiki">TIKI</option>
          <!-- Tambahkan kurir lain sesuai kebutuhan -->
        </select>
      </div>
      <button type="submit" class="btn btn-primary w-100" :disabled="loading">
        <span v-if="loading"><i class="la la-spinner la-spin"></i> Memproses...</span>
        <span v-else>Cek Resi</span>
      </button>
    </form>

    <div v-if="error" class="alert alert-danger mt-4">{{ error }}</div>

    <div v-if="result" class="hasil-cek-resi mt-4">
      <h2 class="mb-3">Hasil Cek Resi</h2>
      <div class="row g-3">
        <div class="col-12 col-md-6">
          <div class="info-box">
            <strong>Nomor Resi:</strong> <span>{{ result.no_resi }}</span>
          </div>
          <div class="info-box">
            <strong>Kurir:</strong> <span>{{ result.courier }}</span>
          </div>
          <div class="info-box">
            <strong>Status:</strong> <span>{{ result.status }}</span>
          </div>
          <div class="info-box">
            <strong>Pengirim:</strong> <span>{{ result.pengirim }}</span>
          </div>
          <div class="info-box">
            <strong>Penerima:</strong> <span>{{ result.penerima }}</span>
          </div>
          <div class="info-box">
            <strong>Lokasi Terakhir:</strong> <span>{{ result.lokasi_terakhir }}</span>
          </div>
        </div>
        <div class="col-12 col-md-6">
          <div class="history-box">
            <strong>Riwayat Pengiriman:</strong>
            <ul class="timeline">
              <li v-for="(item, idx) in result.history" :key="idx">
                <span class="timeline-date">{{ item.manifest_date }} {{ item.manifest_time }}</span>
                <span class="timeline-desc">{{ item.manifest_description }} <span v-if="item.city_name">({{ item.city_name }})</span></span>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from "vue";
import axios from "axios";

const noResi = ref("");
const courier = ref("");
const result = ref(null);
const loading = ref(false);
const error = ref(null);

async function cekResi() {
  loading.value = true;
  error.value = null;
  result.value = null;
  try {
    const response = await axios.post("/api/cek-resi", {
      no_resi: noResi.value,
      courier: courier.value,
    });
    if (response.data.success && response.data.data) {
      result.value = response.data.data;
    } else {
      error.value = response.data.message || "Resi tidak ditemukan.";
    }
  } catch (e) {
    error.value = e.response?.data?.message || "Terjadi kesalahan.";
  } finally {
    loading.value = false;
  }
}
</script>

<style scoped>
.cek-resi-page {
  max-width: 700px;
  margin: 2rem auto;
  padding: 2rem 1.5rem;
  background: #fff;
  border-radius: 10px;
  box-shadow: 0 2px 16px rgba(0,0,0,0.07);
}
.cek-resi-form {
  background: #f8f9fa;
  padding: 1.5rem;
  border-radius: 8px;
  margin-bottom: 1.5rem;
}
.info-box {
  margin-bottom: 0.7rem;
  font-size: 1rem;
}
.history-box {
  background: #f6f6f6;
  border-radius: 8px;
  padding: 1rem;
  margin-top: 1rem;
  max-height: 350px;
  overflow-y: auto;
}
.timeline {
  list-style: none;
  padding: 0;
  margin: 0;
}
.timeline li {
  margin-bottom: 1rem;
  padding-left: 1.2rem;
  position: relative;
}
.timeline-date {
  font-weight: bold;
  color: #007bff;
  display: block;
}
.timeline-desc {
  color: #333;
  font-size: 0.97rem;
}
.timeline li:before {
  content: '';
  position: absolute;
  left: 0;
  top: 7px;
  width: 8px;
  height: 8px;
  background: #007bff;
  border-radius: 50%;
}
@media (max-width: 767px) {
  .cek-resi-page {
    padding: 1rem 0.5rem;
  }
  .cek-resi-form {
    padding: 1rem;
  }
}
</style>
