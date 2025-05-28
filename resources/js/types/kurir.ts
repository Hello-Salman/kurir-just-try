export interface Kurir {
    id: BigInteger;
    name: string;
    email: string;
    password?: string | null;
    phone: string;
    alamat_asal: string;
    alamat_tujuan: string;
    status: "aktif" | "nonaktif" ;
    photo?: File | string;
}
