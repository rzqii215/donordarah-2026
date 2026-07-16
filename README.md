# Sistem Informasi Manajemen Donor Darah

> Platform web untuk membantu pengelolaan donor darah, mulai dari pendaftaran pendonor, pengajuan kebutuhan darah, pengelolaan stok, sampai pemantauan operasional oleh admin.

## Identitas Pengembang

| Keterangan | Detail |
| --- | --- |
| Nama | Muhamad Rizqi Candra |
| NIM | 20240801035 |
| Project | Sistem Informasi Manajemen Donor Darah |
| Jenis | Capstone Project |

## Tentang Project

Sistem Informasi Manajemen Donor Darah adalah aplikasi berbasis web yang dirancang untuk mempertemukan kebutuhan pendonor, pemohon donor, petugas, dan administrator dalam satu ekosistem digital yang tertata.

Project ini dibangun dengan konsep portal terpisah agar setiap pengguna mendapatkan alur kerja yang sesuai dengan kebutuhannya. Pendonor dapat mencari jadwal donor, melihat lokasi donor, melakukan pendaftaran, dan memantau riwayat. Pemohon donor dapat membuat pengajuan kebutuhan darah serta memantau status tindak lanjut. Petugas dan admin mengelola data operasional melalui panel administrasi.

## Konsep Utama

Project ini membawa konsep **donor darah yang lebih terhubung, transparan, dan mudah dipantau**. Fokus sistem bukan hanya pada pencatatan data, tetapi juga pada pengalaman pengguna yang lebih jelas dan profesional.

Prinsip yang digunakan:

- **Terstruktur**: setiap role memiliki portal dan hak akses masing-masing.
- **Dinamis**: data utama diambil dari sistem, bukan dari tampilan statis.
- **Aman**: akses pengguna dilindungi dengan autentikasi, verifikasi email, role, dan permission.
- **Responsif**: tampilan disiapkan agar nyaman digunakan di desktop maupun mobile.
- **Siap operasional**: fitur mencakup alur donor, pengajuan, stok darah, distribusi, laporan, dan audit aktivitas.

## Role Pengguna

| Role | Fungsi Utama |
| --- | --- |
| Super Admin | Mengelola pengguna, role, permission, tampilan autentikasi, laporan, dan activity log. |
| Petugas | Mengelola jadwal donor, lokasi donor, pendaftaran, pemeriksaan, kantong darah, stok, pengajuan, dan distribusi. |
| Pendonor | Melengkapi profil, mencari jadwal, melihat lokasi, mendaftar donor, melihat stok, dan memantau riwayat donor. |
| Pemohon Donor | Melengkapi profil instansi, membuat pengajuan darah, memantau status pengajuan, dan mengunduh bukti PDF. |

## Fitur Utama

### Autentikasi

- Login sesuai portal pengguna.
- Register manual.
- Login dan register menggunakan Google.
- Verifikasi email.
- Lupa password dan reset password.
- Pembatasan akses berdasarkan role.

### Portal Pendonor

- Dashboard pendonor.
- Profil pendonor.
- Pencarian jadwal donor.
- Pencarian lokasi donor.
- Informasi stok darah.
- Pendaftaran donor.
- Riwayat donor.
- Pembatalan pendaftaran sesuai ketentuan.

### Portal Pemohon Donor

- Dashboard pemohon donor.
- Profil pemohon atau rumah sakit.
- Pengajuan kebutuhan darah.
- Riwayat pengajuan.
- Status tindak lanjut pengajuan.
- Informasi distribusi darah.
- Cetak atau unduh bukti PDF.

### Panel Admin

- Manajemen user.
- Manajemen role dan permission.
- Manajemen profil pendonor.
- Manajemen profil pemohon donor.
- Manajemen lokasi dan jadwal donor.
- Manajemen pendaftaran dan pemeriksaan kesehatan.
- Manajemen kantong darah dan stok.
- Manajemen permintaan dan distribusi darah.
- Laporan operasional.
- Activity log.
- Pengaturan tampilan halaman autentikasi.

## Teknologi

| Bagian | Teknologi |
| --- | --- |
| Backend | Laravel |
| Frontend | Blade, Livewire, Tailwind CSS |
| Admin Panel | Filament |
| Database | MariaDB / MySQL |
| Autentikasi | Laravel Auth, Google OAuth |
| Email | Laravel Notification, Resend |
| PDF | DomPDF |
| Container | Docker Compose |

## Struktur Sistem

```text
Sistem Informasi Manajemen Donor Darah
├── Portal Pendonor
│   ├── Profil
│   ├── Jadwal Donor
│   ├── Lokasi Donor
│   ├── Stok Darah
│   └── Riwayat Donor
├── Portal Pemohon Donor
│   ├── Profil Pemohon
│   ├── Pengajuan Darah
│   ├── Riwayat Pengajuan
│   ├── Distribusi Darah
│   └── Bukti PDF
└── Panel Admin
    ├── Pengguna dan Role
    ├── Data Master Donor Darah
    ├── Operasional Donor
    ├── Stok dan Distribusi
    └── Laporan Operasional
```

## Alur Singkat

1. Pengguna melakukan registrasi atau login sesuai portal.
2. Sistem memvalidasi role, status akun, dan verifikasi email.
3. Pendonor dapat mencari jadwal dan melakukan pendaftaran donor.
4. Pemohon donor dapat mengajukan kebutuhan darah.
5. Petugas memproses pendaftaran, pemeriksaan, stok, pengajuan, dan distribusi.
6. Super Admin memantau aktivitas, laporan, pengguna, dan konfigurasi sistem.

## Nilai Project

Project ini dibuat untuk menghadirkan sistem donor darah yang lebih rapi secara data, lebih mudah digunakan oleh pengguna, dan lebih siap dipantau oleh pihak operasional. Dengan pemisahan portal dan manajemen role, sistem dapat membantu proses donor darah menjadi lebih efisien, transparan, dan terdokumentasi.

## Status Project

Project ini dikembangkan sebagai bagian dari Capstone Project dan telah mencakup fitur inti untuk kebutuhan:

- pendonor,
- pemohon donor,
- petugas operasional,
- super admin,
- laporan,
- autentikasi,
- pengelolaan stok,
- pengajuan darah,
- distribusi darah.

## Pengembang

**Muhamad Rizqi Candra**  
**NIM: 20240801035**

> Dibangun dengan fokus pada fungsi, struktur, dan pengalaman pengguna yang lebih modern untuk manajemen donor darah berbasis web.
