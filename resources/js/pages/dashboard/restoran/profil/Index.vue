<template>
    <div class="max-w-3xl mx-auto p-6 bg-white shadow-lg rounded-2xl d-flex flex-column justify-content-center">
        <img src="/media/profil.jpg" alt="Easy Eats" class="w-auto h-64 object-cover rounded-xl mb-6 mx-auto block">
        <h1 class="text-700 mb-4 d-flex justify-content-center">{{ profil.deskripsi }}</h1>
        <div class="mb-2">
            <strong>Alamat:</strong> {{ profil.alamat }}
        </div>
        <div class="mb-2">
            <strong>Contact:</strong> {{ profil.contact }}
        </div>
        <div >
            <strong>Jam Operasional:</strong> {{ profil.jam_operasional }}
        </div>
    </div>
</template>

<script>
export default {
    name: 'ProfilRestoran',
    data() {
        return {
            profil: {
                deskripsi: 'Easy Eats: Pesan dan Nikmati Makanan Dengan Mudah.',
                alamat: 'Jl. Rabesen Dejeh, Sampang, Mexico Barat',
                contact: '031-12345678',
                jam_operasional: 'Buka setiap hari dari jam 10:00 hingga 22:00',
                img: '/media/profil.jpg'
            }
        };
    }
};
</script>

<style scoped>
body {
    background-color: #f9fafb;
}

</style>
