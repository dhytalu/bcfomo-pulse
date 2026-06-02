# Fomo Pulse Notifikasi

Plugin WordPress untuk menampilkan notifikasi fake order/booking untuk meningkatkan konversi.

## Deskripsi

Fomo Pulse Notifikasi adalah plugin WordPress yang menampilkan notifikasi real-time tentang pemesanan (fake booking) di situs Anda. Notifikasi ini menciptakan efek social proof yang dapat meningkatkan kepercayaan pengunjung dan konversi.

## Fitur Utama

- **3 Gaya Tampilan**: Popup pojok, bar bawah, bar atas, atau keduanya sekaligus
- **Custom Post Type (CPT)**: Kelola data booking dengan mudah melalui WordPress admin
- **Pengaturan Lengkap**: Kontrol interval, durasi, posisi, dan lainnya
- **Link WhatsApp**: Notifikasi dapat diklik dan diarahkan ke WhatsApp
- **Responsive**: Cocok untuk semua ukuran layar
- **Sound Effect**: Bunyi notifikasi setiap kali muncul

## Instalasi

1. Download folder `bcfomo-pulse`
2. Upload ke direktori `wp-content/plugins/` di situs WordPress Anda
3. Aktifkan plugin melalui menu **Plugins** di WordPress Admin
4. Buka menu **Fomo Pulse** untuk mengatur plugin

## Penggunaan

### 1. Menambah Fake Booking

1. Di WordPress Admin, buka **Fomo Pulse** → **Fake Booking**
2. Klik **Tambah Booking Baru**
3. Isi data:
   - **Nama Tamu**: Nama orang yang melakukan booking
   - **Layanan**: Pilih layanan (EXPRESS 12 HOUR, EXPRESS 6 HOUR, EXPRESS 3 HOUR, EXPRESS 2 HOUR)
   - **Lokasi**: Lokasi tamu (opsional)
   - **Waktu Pickup**: Waktu pickup (opsional)
4. Klik **Publish**

### 2. Pengaturan Fomo

Buka **Fomo Pulse** → **FOMO Notifikasi** dan atur:

| Pengaturan | Deskripsi | Default |
|------------|-----------|---------|
| Aktifkan FOMO | Mengaktifkan notifikasi di seluruh situs | Ya |
| Gaya tampilan | Pilih: Popup pojok, Bar bawah, Bar atas, atau Keduanya | Popup |
| Posisi popup | Kiri bawah atau Kanan bawah | Kiri bawah |
| Jeda notif pertama | Waktu sebelum notifikasi pertama muncul (detik) | 3 |
| Durasi tampil | Lama notifikasi ditampilkan (detik) | 5 |
| Interval antar notif | Waktu antara notifikasi (detik) | 8 |
| Maks notif per sesi | Jumlah maksimal notifikasi per kunjungan | 10 |
| Nomor WhatsApp | Nomor WhatsApp untuk link notifikasi (format: 6281234567890) | - |
| Nonaktifkan di halaman | ID halaman yang tidak menampilkan notifikasi (pisahkan dengan koma) | - |

## Struktur File

```
bcfomo-pulse/
├── fomo-pulse.php          # File utama plugin
├── README.md                # Dokumentasi ini
├── assets/
│   ├── admin.css            # CSS untuk halaman admin
│   ├── admin.js             # JS untuk halaman admin
│   ├── custom.css           # CSS kustom
│   ├── fomo.css             # CSS untuk notifikasi frontend
│   └── fomo.js              # JS untuk notifikasi frontend
└── includes/
    ├── admin-settings.php   # Pengaturan admin dan CPT
    ├── ajax.php             # Handler AJAX
    ├── fomo.php             # Logika FOMO
    └── shortcode.php        # Shortcode (opsional)
```

## Kebutuhan Sistem

- WordPress 5.0 atau lebih baru
- PHP 7.0 atau lebih baru

## Author

Buat Creative

## Version

1.0.0
