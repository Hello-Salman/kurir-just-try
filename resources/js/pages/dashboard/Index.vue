<template>
  <div class="dashboard-wrapper py-5">
    <div class="container">
      <h1 class="fw-bold mb-4 text-center">Dashboard Easy Eats</h1>
      <p class="text-center mb-5 text-muted">
        Selamat datang di Easy Eats! Kelola restoran, menu, pesanan, dan profil Anda dengan mudah melalui dashboard ini.
      </p>
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
        <div class="col-md-4">
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
        <div class="col-md-4">
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

<script>
export default {
  name: "DashboardIndex",
};
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
