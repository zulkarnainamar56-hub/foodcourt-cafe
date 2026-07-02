<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

if (isLoggedIn()) {
    if (isAdmin()) {
        redirect('admin/dashboard.php');
    } else {
        redirect('user/dashboard.php');
    }
}

$categories = getCategories($conn);
$featured_menus = $conn->query("SELECT m.*, c.name as category_name FROM menu_items m 
                                  JOIN categories c ON m.category_id = c.id 
                                  WHERE m.is_available = 1 
                                  ORDER BY m.rating DESC LIMIT 6");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FoodCourt Cafe - Sistem Pemesanan Makanan Online</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>css/style.css">
    <style>
        .landing-page {
            margin: 0;
        }
        
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 5rem 2rem;
            text-align: center;
        }
        
        .hero-section h1 {
            font-size: 48px;
            margin-bottom: 1rem;
        }
        
        .hero-section p {
            font-size: 20px;
            margin-bottom: 2rem;
            opacity: 0.9;
        }
        
        .cta-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .cta-btn {
            padding: 12px 30px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            font-size: 16px;
            transition: all 0.3s;
        }
        
        .cta-btn-primary {
            background: white;
            color: #667eea;
        }
        
        .cta-btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }
        
        .cta-btn-secondary {
            background: transparent;
            color: white;
            border: 2px solid white;
        }
        
        .cta-btn-secondary:hover {
            background: white;
            color: #667eea;
        }
        
        .features-section {
            max-width: 1200px;
            margin: 3rem auto;
            padding: 2rem;
        }
        
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }
        
        .feature-card {
            text-align: center;
            padding: 2rem;
        }
        
        .feature-icon {
            font-size: 48px;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }
        
        .feature-card h3 {
            font-size: 20px;
            margin-bottom: 0.5rem;
            color: var(--text-color);
        }
        
        .featured-menu-section {
            max-width: 1200px;
            margin: 3rem auto;
            padding: 2rem;
        }
        
        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-top: 1.5rem;
        }
        
        .menu-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
        }
        
        .menu-card:hover {
            transform: translateY(-5px);
        }
        
        .menu-card img {
            width: 100%;
            height: 150px;
            object-fit: cover;
        }
        
        .menu-card-content {
            padding: 1rem;
        }
        
        .menu-card-content h3 {
            font-size: 14px;
            margin-bottom: 0.5rem;
        }
        
        .menu-card-content .price {
            color: var(--primary-color);
            font-weight: bold;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="navbar-container">
            <div class="navbar-logo">
                <a href="#">🍔 FoodCourt Cafe</a>
            </div>
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="auth/login.php" class="nav-link">Login</a>
                </li>
                <li class="nav-item">
                    <a href="auth/register.php" class="nav-link">Daftar</a>
                </li>
            </ul>
        </div>
    </nav>
    
    <div class="landing-page">
        <section class="hero-section">
            <h1>🍽️ Selamat Datang di FoodCourt Cafe</h1>
            <p>Pesan makanan favorit Anda dengan mudah, cepat, dan terpercaya</p>
            <div class="cta-buttons">
                <a href="auth/register.php" class="cta-btn cta-btn-primary">Mulai Pesan Sekarang</a>
                <a href="#features" class="cta-btn cta-btn-secondary">Pelajari Lebih Lanjut</a>
            </div>
        </section>
        
        <section class="features-section" id="features">
            <h2 style="text-align: center; color: var(--primary-color);">Fitur Unggulan</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-utensils"></i></div>
                    <h3>Menu Lengkap</h3>
                    <p>Pilihan menu yang beragam dari berbagai kategori makanan favorit Anda</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-lightning-bolt"></i></div>
                    <h3>Pesanan Cepat</h3>
                    <p>Proses pemesanan yang mudah dan cepat hanya dalam beberapa klik</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-credit-card"></i></div>
                    <h3>Pembayaran Aman</h3>
                    <p>Berbagai metode pembayaran yang aman dan terpercaya</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-truck"></i></div>
                    <h3>Pengiriman Cepat</h3>
                    <p>Pesanan dikirim dengan cepat dan dalam kondisi terbaik</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-star"></i></div>
                    <h3>Rating Akurat</h3>
                    <p>Lihat rating dan review dari pelanggan lain untuk membantu pilihan Anda</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-headset"></i></div>
                    <h3>Dukungan 24/7</h3>
                    <p>Tim customer service kami siap membantu Anda kapan saja</p>
                </div>
            </div>
        </section>
        
        <section class="featured-menu-section">
            <h2 style="text-align: center; color: var(--primary-color);">Menu Rekomendasi 🌟</h2>
            <div class="menu-grid">
                <?php while ($menu = $featured_menus->fetch_assoc()): ?>
                    <div class="menu-card">
                        <img src="<?php echo BASE_URL; ?>images/<?php echo htmlspecialchars($menu['image']); ?>" 
                             alt="<?php echo htmlspecialchars($menu['name']); ?>"
                             onerror="this.src='<?php echo BASE_URL; ?>images/placeholder.png'">
                        <div class="menu-card-content">
                            <h3><?php echo htmlspecialchars($menu['name']); ?></h3>
                            <p class="price"><?php echo formatCurrency($menu['price']); ?></p>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </section>
    </div>
    
    <footer class="footer">
        <div class="footer-container">
            <div class="footer-section">
                <h3>🍔 FoodCourt Cafe</h3>
                <p>Sistem pemesanan makanan online terbaik dengan menu pilihan terlengkap.</p>
            </div>
            <div class="footer-section">
                <h4>Hubungi Kami</h4>
                <p>📍 Jl. Makan Enak No. 123, Jakarta</p>
                <p>📞 08123456789</p>
                <p>📧 info@foodcourt.com</p>
            </div>
            <div class="footer-section">
                <h4>Jam Operasional</h4>
                <p>Senin - Minggu</p>
                <p>09:00 - 22:00 WIB</p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2024 FoodCourt Cafe. Semua hak cipta dilindungi.</p>
        </div>
    </footer>
</body>
</html>
