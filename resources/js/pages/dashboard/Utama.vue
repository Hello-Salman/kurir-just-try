<template>
  <div class="dashboard-wrapper py-5">
    <div class="container">
      <h1 class="fw-bold mb-4 text-center">Dashboard Easy Eats</h1>
      <p class="text-center mb-5 text-muted">
        Selamat datang di Easy Eats! Kelola restoran, menu, pesanan, pengiriman, dan profil Anda dengan mudah melalui dashboard ini.
      </p>

      <!-- Statistik Ringkas -->
      <div class="row g-4 mb-5 justify-content-center">
        <div class="col-md-3 col-6" v-for="stat in stats" :key="stat.label">
          <div class="card shadow border-0 text-center">
            <div class="card-body">
              <i :class="stat.icon" :style="{ color: stat.color, fontSize: '2.5rem' }"></i>
              <h2 class="fw-bold my-2">{{ stat.value }}</h2>
              <div class="text-muted small">{{ stat.label }}</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Navigasi Fitur -->
      <div class="row g-4 justify-content-center">
        <!-- Card Menu Restoran -->
        <div class="col-md-4">
          <div class="card shadow-lg border-0 h-100">
            <div class="card-body text-center">
              <i class="la la-cutlery fs-1 text-primary mb-3"></i>
              <h5 class="card-title mb-2">Menu Restoran</h5>
              <p class="card-text">Kelola daftar menu makanan & minuman restoran Anda.</p>
              <router-link to="/dashboard/restoran/menu" class="btn btn-primary btn-sm">Lihat Menu</router-link>
            </div>
          </div>
        </div>
        <!-- Card Order -->
        <div class="col-md-4">
          <div class="card shadow-lg border-0 h-100">
            <div class="card-body text-center">
              <i class="la la-shopping-basket fs-1 text-success mb-3"></i>
              <h5 class="card-title mb-2">Order</h5>
              <p class="card-text">Pantau dan kelola pesanan yang masuk ke restoran Anda.</p>
              <router-link to="/dashboard/order" class="btn btn-success btn-sm">Lihat Order</router-link>
            </div>
          </div>
        </div>
        <!-- Card Pengiriman (khusus kurir & admin) -->
        <div class="col-md-4" v-if="isKurir || isAdmin">
          <div class="card shadow-lg border-0 h-100">
            <div class="card-body text-center">
              <i class="la la-truck fs-1 text-danger mb-3"></i>
              <h5 class="card-title mb-2">Pengiriman</h5>
              <p class="card-text">Kelola dan pantau pengiriman order ke pelanggan.</p>
              <router-link to="/dashboard/pengiriman/kirim" class="btn btn-danger btn-sm text-white">Kirim Order</router-link>
              <router-link to="/dashboard/pengiriman/riwayat" class="btn btn-outline-danger btn-sm ms-2">Riwayat</router-link>
            </div>
          </div>
        </div>
        <!-- Card Profil Restoran -->
        <div class="col-md-4">
          <div class="card shadow-lg border-0 h-100">
            <div class="card-body text-center">
              <i class="la la-user-circle fs-1 text-info mb-3"></i>
              <h5 class="card-title mb-2">Profil Restoran</h5>
              <p class="card-text">Lihat dan perbarui informasi profil restoran Anda.</p>
              <router-link to="/dashboard/restoran/profil" class="btn btn-info btn-sm">Lihat Profil</router-link>
            </div>
          </div>
        </div>
        <!-- Card Setting (Opsional) -->
        <div class="col-md-4" v-if="isAdmin">
          <div class="card shadow-lg border-0 h-100">
            <div class="card-body text-center">
              <i class="la la-cog fs-1 text-warning mb-3"></i>
              <h5 class="card-title mb-2">Pengaturan</h5>
              <p class="card-text">Atur preferensi aplikasi dan akun Anda.</p>
              <router-link to="/dashboard/setting" class="btn btn-warning btn-sm text-white">Pengaturan</router-link>
            </div>
          </div>
        </div>
        <!-- Card User (Opsional) -->
        <div class="col-md-4" v-if="isAdmin">
          <div class="card shadow-lg border-0 h-100">
            <div class="card-body text-center">
              <i class="la la-users fs-1 text-secondary mb-3"></i>
              <h5 class="card-title mb-2">Manajemen User</h5>
              <p class="card-text">Kelola data user/admin yang memiliki akses ke sistem.</p>
              <router-link to="/dashboard/master/users" class="btn btn-secondary btn-sm">Kelola User</router-link>
            </div>
          </div>
        </div>
      </div>
      <div class="text-center mt-5 text-muted small">
        &copy; {{ new Date().getFullYear() }} Easy Eats.
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from "vue";
import { useAuthStore } from "@/stores/auth";
import axios from "@/libs/axios";

const authStore = useAuthStore();
const user = computed(() => authStore.user || {});
const isAdmin = computed(() => user.value.role === "admin");
const isKurir = computed(() => user.value.role === "kurir");

// Statistik ringkas
const stats = ref([
  { label: "Total Order", value: 0, icon: "la la-shopping-basket", color: "#28a745" },
  { label: "Total Menu", value: 0, icon: "la la-cutlery", color: "#007bff" },
  { label: "Total Pengiriman", value: 0, icon: "la la-truck", color: "#dc3545" },
  { label: "Total User", value: 0, icon: "la la-users", color: "#6c757d" },
]);

async function fetchStats() {
  try {
    const [orderRes, menuRes, pengirimanRes, userRes] = await Promise.all([
      axios.get("/stat/order"),
      axios.get("/stat/menu"),
      axios.get("/stat/pengiriman"),
      axios.get("/stat/user"),
    ]);
    stats.value[0].value = orderRes.data.total || 0;
    stats.value[1].value = menuRes.data.total || 0;
    stats.value[2].value = pengirimanRes.data.total || 0;
    stats.value[3].value = userRes.data.total || 0;
  } catch {
    // fallback: biarkan 0 jika gagal
  }
}

onMounted(fetchStats);
</script>

<style scoped>
.dashboard-wrapper {
  min-height: 80vh;
  background: linear-gradient(135deg, #f9fafb 60%, #e3e9f7 100%);
}
.card {
  transition: transform 0.2s;
}
.card:hover {
  transform: translateY(-4px) scale(1.02);
}
.text-center {
  color: #181a20 !important;
}
@media (max-width: 767px) {
  .dashboard-wrapper {
    padding: 1rem 0;
  }
}
</style>
