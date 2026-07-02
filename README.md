# 🍔 FoodCourt Cafe - Sistem Manajemen Pemesanan Makanan

Sistem pemesanan makanan online yang komprehensif dan mudah digunakan untuk restoran atau kafe modern.

## ✨ Fitur Utama

### Untuk Pengguna (User)
- 👤 Autentikasi & Registrasi pengguna
- 🍽️ Browse menu dengan kategori dan search
- 🛒 Shopping cart dengan manajemen item
- 📝 Checkout dan pembuatan pesanan
- 💳 Berbagai metode pembayaran (Tunai, Kartu, Transfer)
- 📋 Riwayat pesanan dan tracking status
- ⭐ Rating dan review menu
- 🔔 Notifikasi pesanan real-time
- 👤 Manajemen profil pengguna

### Untuk Admin
- 📊 Dashboard dengan statistik lengkap
- 🍽️ Manajemen menu items (CRUD)
- 📦 Manajemen kategori makanan
- 📋 Manajemen pesanan & status
- 💰 Laporan penjualan & revenue
- 📈 Analisis menu terlaris
- ⚙️ Pengaturan sistem
- 📥 Export data ke CSV

## 🚀 Instalasi

### Prasyarat
- PHP 7.4+
- MySQL 5.7+
- Apache / Nginx
- Composer (opsional)

### Langkah Instalasi

1. **Clone Repository**
```bash
git clone https://github.com/zulkarnainamar56-hub/foodcourt-cafe.git
cd foodcourt-cafe
```

2. **Setup Database**
```bash
# Buat database baru
mysql -u root -p < db/database.sql
```

3. **Konfigurasi Database**
Edit file `config/database.php`:
```php
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'foodcourt_cafe';
```

4. **Akses Aplikasi**
```
http://localhost/foodcourt-cafe/
```

## 📝 Akun Default

### Admin
- **Username:** admin
- **Password:** admin123

## 📁 Struktur File

```
foodcourt-cafe/
├── admin/                  # Halaman admin
│   ├── dashboard.php      # Dashboard statistik
│   ├── manage-menu.php    # Kelola menu
│   ├── manage-orders.php  # Kelola pesanan
│   ├── reports.php        # Laporan & statistik
│   └── settings.php       # Pengaturan
├── user/                   # Halaman pengguna
│   ├── dashboard.php      # Dashboard user
│   ├── menu.php           # Browse menu
│   ├── order.php          # Keranjang & checkout
│   ├── order-history.php  # Riwayat pesanan
│   └── profile.php        # Edit profil
├── auth/                   # Autentikasi
│   ├── login.php          # Login page
│   ├── register.php       # Registrasi
│   └── logout.php         # Logout
├── api/                    # API endpoints
│   ├── add-to-cart.php    # Tambah ke keranjang
│   ├── remove-from-cart.php # Hapus dari keranjang
│   └── get-order-details.php # Detail pesanan
├── includes/              # Include files
│   ├── header.php         # Header template
│   ├── footer.php         # Footer template
│   └── functions.php      # Helper functions
├��─ config/                # Konfigurasi
│   └── database.php       # Database config
├── css/                   # Stylesheet
│   └── style.css          # Main CSS
├── js/                    # JavaScript
│   └── script.js          # Main JS
├── images/                # Gambar produk
├── db/                    # Database
│   └── database.sql       # Schema database
├── index.php              # Home page
└── README.md              # Dokumentasi
```

## 📊 Database Schema

### Tabel Utama
- **users** - Data pengguna
- **categories** - Kategori menu
- **menu_items** - Item menu makanan
- **orders** - Data pesanan
- **order_items** - Detail item pesanan
- **cart** - Shopping cart sementara
- **reviews** - Rating & review produk
- **notifications** - Notifikasi sistem
- **settings** - Pengaturan aplikasi

## 🎨 Teknologi yang Digunakan

- **Frontend:** HTML5, CSS3, JavaScript (Vanilla)
- **Backend:** PHP 7.4+
- **Database:** MySQL 5.7+
- **Server:** Apache/Nginx

## 🔒 Keamanan

- Password hashing menggunakan bcrypt
- SQL Injection prevention dengan prepared statements
- XSS prevention dengan htmlspecialchars()
- CSRF protection dengan session validation
- Role-based access control (RBAC)

## 📱 Fitur Responsif

Aplikasi fully responsive dan mobile-friendly:
- Desktop (1200px+)
- Tablet (768px - 1199px)
- Mobile (< 768px)

## 🚦 Status Pesanan

1. **Pending** - Menunggu konfirmasi
2. **Confirmed** - Pesanan dikonfirmasi
3. **Preparing** - Sedang disiapkan
4. **Ready** - Siap diambil
5. **Completed** - Selesai
6. **Cancelled** - Dibatalkan

## 💳 Metode Pembayaran

- Tunai (Cash)
- Kartu Kredit (Card)
- Transfer Bank (Transfer)

## 📞 Support

Untuk bantuan dan pertanyaan:
- Email: info@foodcourt.com
- WhatsApp: 08123456789
- Jam operasional: 09:00 - 22:00 WIB

## 📄 Lisensi

Proyek ini dilisensikan di bawah MIT License - lihat file LICENSE untuk detail.

## 👨‍💻 Kontribusi

Kontribusi sangat diterima! Silakan fork repository dan buat pull request.

## 📈 Roadmap

- [ ] Integrasi payment gateway (Midtrans, Xendit)
- [ ] Live tracking pesanan dengan map
- [ ] Mobile app (React Native / Flutter)
- [ ] AI recommendation system
- [ ] Multi-location support
- [ ] Loyalty program
- [ ] Integration dengan social media

## 🙏 Terima Kasih

Terima kasih telah menggunakan FoodCourt Cafe!

---

**Dibuat dengan ❤️ oleh Zulkarnain Amar**
