# Dashboard dan Laporan Admin

## Tujuan

Dashboard Admin digunakan untuk menampilkan kondisi operasional sistem
secara cepat tanpa perlu membuka setiap modul satu per satu.

## Widget Dashboard

Widget yang tersedia:

- Ringkasan operasional
- Grafik stok darah berdasarkan golongan dan rhesus
- Permintaan darah terbaru
- Kantong darah yang mendekati kedaluwarsa

## Ringkasan Operasional

Data yang ditampilkan:

- Stok darah tersedia
- Permintaan darah aktif
- Pendaftaran donor hari ini
- Distribusi darah hari ini

Stok darah tersedia hanya menghitung kantong dengan kondisi:

```text
status = available
status_mutu = passed
kedaluwarsa_pada > waktu sekarang