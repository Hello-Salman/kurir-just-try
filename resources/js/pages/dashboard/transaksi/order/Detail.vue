<script setup lang="ts">
import { ref, watch } from "vue";
import axios from "@/libs/axios";
import type { Order} from "@/types/order";
import type { Menu} from "@/types/menu";
// import { toast } from "vue3-toastify";

// Props dari parent
const props = defineProps<{
  order: Order | null;
  show: boolean;
}>();
const emit = defineEmits(["close", "refresh"]);

// State untuk data menu dan pelanggan
const menuDetail = ref<Menu | null>(null);

// State pembayaran
const isPaying = ref(false);

// Ref untuk area yang akan diprint
const printArea = ref<HTMLDivElement | null>(null);

// Ambil data menu dari API berdasarkan id_menu
const fetchMenu = async (id_menu: BigInteger) => {
  try {
    const res = await axios.get(`/menu/${id_menu}`);
    menuDetail.value = res.data.data;
  } catch {
    menuDetail.value = null;
  }
};

// Watcher: setiap order berubah, ambil data menu & pelanggan
watch(
  () => props.order,
  (order) => {
    if (order) {
      fetchMenu(order.id_menu);
    }
  },
  { immediate: true }
);

const printStruk = () => {
  if (!printArea.value) return;
  const printContents = printArea.value.innerHTML;
  const originalContents = document.body.innerHTML;
  document.body.innerHTML = printContents;
  window.print();
  document.body.innerHTML = originalContents;
  window.location.reload(); // reload agar tampilan kembali normal
};

</script>

<template>
  <div v-if="show" class="modal-backdrop" @click.self="emit('close')">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-body" v-if="order" ref="printArea">
          <!-- Header -->
          <div class="receipt-header">
            <div class="text-center mb-4">
              <img src="/media/profil.jpg" alt="Logo" class="receipt-logo">
              <p class="receipt-subtitle">Terima Kasih Atas Kunjungan Anda!</p>
            </div>
            <hr class="divider">
          </div>

          <!-- Product Section -->
          <div class="product-section mt-4">
            <h3 class="product-title">Pesanan Anda</h3>
            <div class="product-item">
              <div class="d-flex justify-content-between">
                <span>{{ order.menu?.nama_menu ?? '-' }}</span>
                <span>x{{ order.jumlah ?? 1 }}</span>
              </div>
              <div class="d-flex justify-content-between mt-2">
                <span>Total Harga</span>
                <span>Rp {{ Number(order.menu?.harga * order.jumlah).toLocaleString("id-ID") }}</span>
              </div>
              <div class="d-flex justify-content-between mt-2">
                <span>Ongkir</span>
                <span>Rp {{ Number(order.biaya ?? 0).toLocaleString("id-ID") }}</span>
              </div>
            </div>
          </div>

          <!-- Total Price -->
          <div class="total-price mt-4">
            <div class="d-flex justify-content-between">
              <span class="font-weight-bold">Total Pembayaran</span>
              <span class="font-weight-bold text-primary">
                Rp {{
                  Number(order.menu?.harga * order.jumlah + order.biaya).toLocaleString("id-ID")
                }}
              </span>
            </div>
          </div>

          <!-- Footer -->
          <div class="receipt-footer mt-4">
            <h6 class="text-center text-strong">
            </h6>
          </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-primary me-2" @click="printStruk">
                <i class="la la-print me-1"></i>Cetak
            </button>
            <!-- <button type="button" class="btn btn-primary" @click="emit('close')">Tutup</button> -->
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.modal-backdrop {
  position: fixed;
  z-index: 1050;
  inset: 0;
  background: url('/media/restoran-bg.jpg') center center/cover no-repeat;
  display: flex;
  align-items: center;
  justify-content: center;
}

.modal-dialog {
  background: white;
  border-radius: 15px;
  max-width: 400px;
  width: 100%;
  box-shadow: 0 10px 30px rgba(0,0,0,0.1);
  padding: 20px;
}

.receipt-header {
  text-align: center;
  margin-bottom: 20px;
}

.receipt-logo {
  width: 80px;
  height: 80px;
  border-radius: 10px;
  margin-bottom: 10px;
}

.receipt-title {
  font-size: 24px;
  font-weight: bold;
  color: #2c3e50;
  margin: 0;
}

.receipt-subtitle {
  color: #7f8c8d;
  margin: 5px 0 0;
}

.divider {
  border-top: 2px solid #eee;
  margin: 15px 0;
}

.order-details {
  margin-bottom: 20px;
}

.product-section {
  background: #f8f9fa;
  border-radius: 10px;
  padding: 15px;
}

.product-title {
  color: #2c3e50;
  margin: 0;
}

.product-item {
  background: white;
  border-radius: 8px;
  padding: 10px;
  margin-top: 10px;
}

.total-price {
  background: #f8f9fa;
  border-radius: 10px;
  padding: 15px;
  margin-top: 20px;
}

.total-price span {
  color: #2c3e50;
}

.total-price .text-primary {
  color: #27ae60;
  font-size: 18px;
}

.receipt-footer {
  text-align: center;
  margin-top: 20px;
}

.receipt-footer p {
  margin: 5px 0;
}

.btn-primary {
  background-color: #27ae60;
  border: none;
  color: white;
  padding: 10px 20px;
  border-radius: 25px;
  transition: background-color 0.3s;
}

.btn-primary:hover {
  background-color: #219a52;
}

.modal-footer {
  border-top: none;
  text-align: center;
  padding: 15px;
}
</style>
