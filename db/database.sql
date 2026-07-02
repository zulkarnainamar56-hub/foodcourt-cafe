-- Create Database
CREATE DATABASE IF NOT EXISTS foodcourt_cafe;
USE foodcourt_cafe;

-- Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(15),
    address TEXT,
    role ENUM('admin', 'user') DEFAULT 'user',
    profile_image VARCHAR(255),
    is_active TINYINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Categories Table
CREATE TABLE IF NOT EXISTS categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    image VARCHAR(255),
    is_active TINYINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Menu Items Table
CREATE TABLE IF NOT EXISTS menu_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    category_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    image VARCHAR(255),
    is_available TINYINT DEFAULT 1,
    rating FLOAT DEFAULT 0,
    total_reviews INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);

-- Orders Table
CREATE TABLE IF NOT EXISTS orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    order_number VARCHAR(20) UNIQUE NOT NULL,
    total_amount DECIMAL(10, 2) NOT NULL,
    status ENUM('pending', 'confirmed', 'preparing', 'ready', 'completed', 'cancelled') DEFAULT 'pending',
    payment_status ENUM('unpaid', 'paid', 'partial') DEFAULT 'unpaid',
    payment_method ENUM('cash', 'card', 'transfer') DEFAULT 'cash',
    notes TEXT,
    estimated_time INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Order Items Table
CREATE TABLE IF NOT EXISTS order_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    menu_item_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    subtotal DECIMAL(10, 2) NOT NULL,
    special_notes TEXT,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (menu_item_id) REFERENCES menu_items(id) ON DELETE RESTRICT
);

-- Reviews Table
CREATE TABLE IF NOT EXISTS reviews (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    menu_item_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (menu_item_id) REFERENCES menu_items(id) ON DELETE CASCADE,
    UNIQUE KEY unique_review (user_id, menu_item_id)
);

-- Cart Table (Temporary Shopping Cart)
CREATE TABLE IF NOT EXISTS cart (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    menu_item_id INT NOT NULL,
    quantity INT NOT NULL,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (menu_item_id) REFERENCES menu_items(id) ON DELETE CASCADE,
    UNIQUE KEY unique_cart_item (user_id, menu_item_id)
);

-- Notifications Table
CREATE TABLE IF NOT EXISTS notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    type VARCHAR(50),
    title VARCHAR(100),
    message TEXT,
    is_read TINYINT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Settings Table
CREATE TABLE IF NOT EXISTS settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    description TEXT
);

-- Insert Default Admin User (username: admin, password: admin123)
INSERT INTO users (username, email, password, full_name, role) VALUES 
('admin', 'admin@foodcourt.com', '$2y$10$YIjlrVyFfK5oF5Z8Z5Z8Z.7Z5Z5Z5Z5Z5Z5Z5Z5Z5Z5Z5Z5Z5Z5', 'Administrator', 'admin');

-- Insert Sample Categories
INSERT INTO categories (name, description, image) VALUES 
('Makanan Cepat', 'Makanan siap saji dan cepat', 'fast-food.jpg'),
('Minuman', 'Berbagai pilihan minuman segar', 'beverages.jpg'),
('Dessert', 'Kue dan dessert lezat', 'desserts.jpg'),
('Makanan Berat', 'Nasi, mie, dan makanan utama', 'main-course.jpg');

-- Insert Sample Menu Items
INSERT INTO menu_items (category_id, name, description, price, image) VALUES 
(1, 'Burger Spesial', 'Burger dengan daging sapi premium dan saus istimewa', 35000, 'burger.jpg'),
(1, 'Fried Chicken', 'Ayam goreng renyah dengan tepung pilihan', 32000, 'chicken.jpg'),
(2, 'Es Coklat', 'Minuman coklat dingin yang menyegarkan', 15000, 'chocolate-ice.jpg'),
(2, 'Jus Jeruk Segar', 'Jus jeruk alami tanpa pemanis buatan', 18000, 'orange-juice.jpg'),
(3, 'Brownies Coklat', 'Kue brownies lembut dan nikmat', 25000, 'brownies.jpg'),
(3, 'Cheese Cake', 'Kue keju premium dengan rasa istimewa', 30000, 'cheesecake.jpg'),
(4, 'Nasi Goreng Spesial', 'Nasi goreng dengan telur, udang, dan sayuran', 42000, 'nasi-goreng.jpg'),
(4, 'Mie Ayam Jamur', 'Mie kuning dengan potongan ayam dan jamur segar', 38000, 'mie-ayam.jpg');

-- Insert Default Settings
INSERT INTO settings (setting_key, setting_value, description) VALUES 
('restaurant_name', 'FoodCourt Cafe', 'Nama restoran'),
('restaurant_phone', '08123456789', 'Nomor telepon restoran'),
('restaurant_address', 'Jl. Makan Enak No. 123, Jakarta', 'Alamat restoran'),
('restaurant_email', 'info@foodcourt.com', 'Email restoran'),
('restaurant_hours', '09:00-22:00', 'Jam operasional'),
('tax_percentage', '10', 'Persentase pajak'),
('delivery_fee', '5000', 'Biaya pengiriman'),
('min_order', '30000', 'Minimal pemesanan');
