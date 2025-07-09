-- Database: dapoer_ss
CREATE DATABASE IF NOT EXISTS dapoer_ss;
USE dapoer_ss;

-- Table: users
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20),
    password VARCHAR(255) NOT NULL,
    birth_date DATE,
    gender ENUM('male', 'female') NOT NULL,
    role ENUM('customer', 'admin') DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table: categories
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    slug VARCHAR(50) UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table: products
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    image VARCHAR(255),
    category_id INT,
    stock INT DEFAULT 0,
    rating DECIMAL(2,1) DEFAULT 0,
    reviews_count INT DEFAULT 0,
    badge VARCHAR(20),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id)
);

-- Table: cart
CREATE TABLE cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_product (user_id, product_id)
);

-- Table: orders
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    order_number VARCHAR(20) UNIQUE NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    payment_method VARCHAR(50),
    shipping_address TEXT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Table: order_items
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- Insert default categories
INSERT INTO categories (name, slug) VALUES
('Kue Kering', 'kue-kering'),
('Kue Basah', 'kue-basah'),
('Snack Box', 'snack-box'),
('Hampers', 'hampers');

-- Insert sample products
INSERT INTO products (name, description, price, image, category_id, stock, rating, reviews_count, badge) VALUES
('Chocochip Cookies', 'Kue kering dengan chocochip premium yang renyah dan lezat', 40000, 'https://images.unsplash.com/photo-1499636136210-6f4ee915583e?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3MzkyNDZ8MHwxfHNlYXJjaHwxfHxjaG9jb2xhdGUlMjBjaGlwJTIwY29va2llc3xlbnwwfHx8fDE3NTA0MDA3MDF8MA&ixlib=rb-4.1.0&q=80&w=1080', 1, 50, 4.5, 128, 'Bestseller'),
('Nastar Premium', 'Nastar dengan selai nanas asli dan mentega berkualitas tinggi', 45000, 'https://images.unsplash.com/photo-1740631599955-0ca3e75d8139?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3MzkyNDZ8MHwxfHNlYXJjaHwxfHxuYXN0YXIlMjBjb29raWVzfGVufDB8fHx8MTc1MDQwMDcyM3ww&ixlib=rb-4.1.0&q=80&w=1080', 1, 30, 5.0, 95, ''),
('Thumbprint Cookies', 'Kue kering dengan topping selai buah yang manis', 42000, 'https://images.unsplash.com/photo-1557089706688-ca3cf47d3e6e?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3MzkyNDZ8MHwxfHNlYXJjaHwxfHx0aHVtYnByaW50JTIwY29va2llc3xlbnwwfHx8fDE3NTA0MDA3NTZ8MA&ixlib=rb-4.1.0&q=80&w=1080', 1, 25, 4.2, 67, 'New'),
('Macaroon Colorful', 'Macaroon warna-warni dengan berbagai rasa yang unik', 55000, 'https://images.unsplash.com/photo-1599599810769-bcde5a160d32?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3MzkyNDZ8MHwxfHNlYXJjaHwxfHxtYWNhcm9vbnN8ZW58MHx8fHwxNzE3MDcxMDkwfDA&ixlib=rb-4.1.0&q=80&w=1080', 2, 20, 4.8, 89, 'Limited'),
('Red Velvet Cake', 'Kue red velvet dengan cream cheese frosting yang lembut', 85000, 'https://images.unsplash.com/photo-1586985545062-69928b1d9587?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3MzkyNDZ8MHwxfHNlYXJjaHwxfHxyZWQlMjB2ZWx2ZXQlMjBjYWtlfGVufDB8fHx8MTc1MDQwMDYzNnww&ixlib=rb-4.1.0&q=80&w=1080', 2, 15, 4.9, 156, 'Popular'),
('Snack Box Deluxe', 'Paket snack lengkap untuk acara atau hadiah', 75000, 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3MzkyNDZ8MHwxfHNlYXJjaHwxfHxzbmFjayUyMGJveHxlbnwwfHx8fDE3NTA0MDA2MzZ8MA&ixlib=rb-4.1.0&q=80&w=1080', 3, 40, 4.6, 203, ''),
('Wedding Hampers', 'Hampers eksklusif untuk acara pernikahan', 150000, 'https://images.unsplash.com/photo-1513475382585-d06e58bcb0e0?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3MzkyNDZ8MHwxfHNlYXJjaHwxfHx3ZWRkaW5nJTIwaGFtcGVyc3xlbnwwfHx8fDE3NTA0MDA2MzZ8MA&ixlib=rb-4.1.0&q=80&w=1080', 4, 10, 4.7, 78, 'Premium'),
('Chocolate Brownies', 'Brownies coklat yang fudgy dan rich dengan topping kacang', 38000, 'https://images.unsplash.com/photo-1606313564200-e75d5e30476c?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3MzkyNDZ8MHwxfHNlYXJjaHwxfHxjaG9jb2xhdGUlMjBicm93bmllc3xlbnwwfHx8fDE3NTA0MDA2MzZ8MA&ixlib=rb-4.1.0&q=80&w=1080', 2, 35, 4.4, 112, '');

-- Insert default admin user
INSERT INTO users (first_name, last_name, email, phone, password, birth_date, gender, role) VALUES
('Admin', 'Dapoer SS', 'admin@dapoerss.com', '+62812-3456-7890', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '1990-01-01', 'male', 'admin');
-- Password: password
