# API Stok Darah

## Tujuan

API Stok Darah menyediakan ringkasan stok darah yang aman untuk
ditampilkan pada frontend publik.

API tidak menampilkan:

- Kode kantong darah
- Identitas pendonor
- Nomor pendaftaran donor
- Lokasi penyimpanan internal
- Petugas verifikator
- Riwayat alokasi

## Akses

API bersifat:

```text
public
read-only

# API Rumah Sakit dan Permintaan Darah

## Autentikasi

Seluruh endpoint membutuhkan Bearer Token Sanctum.

```http
Authorization: Bearer TOKEN
Accept: application/json