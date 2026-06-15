# API Distribusi Darah

## Tujuan

API Distribusi Darah digunakan oleh Rumah Sakit untuk memantau proses
distribusi darah dari permintaan miliknya sendiri.

## Autentikasi

Seluruh endpoint membutuhkan Bearer Token Sanctum.

```http
Authorization: Bearer TOKEN
Accept: application/json