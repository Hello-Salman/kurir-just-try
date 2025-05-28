export interface User {
    id: BigInteger;
    uuid: string;
    name: string;
    email: string;
    password?: string;
    passwordConfirmation?: string;
    phone?: BigInteger;
    role_id: BigInteger;
}
