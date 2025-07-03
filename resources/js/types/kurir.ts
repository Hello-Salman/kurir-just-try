export interface Kurir {
    id: BigInteger;
    name: string;
    email: string;
    password?: string | null;
    phone: string;
    status: "aktif" | "nonaktif" ;
    photo?: File | string;
}
