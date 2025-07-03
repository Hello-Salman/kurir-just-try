export interface Order {
    id: BigInteger;
    no_resi: string;
    jumlah: number;
    status: string;
    total_harga: string;
    ekspedisi: string;
    jenis_layanan: string;
    biaya: string;
    asal_provinsi: string;
    asal_kota: string;
    alamat_asal: string;
    tujuan_provinsi: string;
    tujuan_kota: string;
    alamat_tujuan: string;
}
