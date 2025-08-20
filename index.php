<?php
require_once __DIR__ . '/classes/User.php';
require_once __DIR__ . '/classes/Database.php';

$user = new User();
$db = Database::getInstance();
$currentUser = $user->getCurrentUser();

// Get categories
$categories = $db->fetchAll("SELECT * FROM categories ORDER BY name");

// Get featured products
$products = $db->fetchAll("
    SELECT p.*, c.name as category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    ORDER BY p.created_at DESC 
    LIMIT 8
");

$logoutSuccess = isset($_GET['logout']) && $_GET['logout'] === 'success';
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - <?php echo SITE_DESCRIPTION; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@300;400;500;600;700;800&family=Kanit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link href="assets/css/modern-style.css" rel="stylesheet">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --success-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            --dark-gradient: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
        }
        
        body {
            font-family: 'Kanit', sans-serif;
            line-height: 1.6;
        }
        
        /* Navigation */
        .navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }
        
        .navbar-brand {
            font-weight: 700;
            font-size: 1.8rem;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .nav-link {
            font-weight: 500;
            transition: all 0.3s ease;
            position: relative;
        }
        
        .nav-link:hover {
            color: #667eea !important;
        }
        
        .nav-link::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: 0;
            left: 50%;
            background: var(--primary-gradient);
            transition: all 0.3s ease;
        }
        
        .nav-link:hover::after {
            width: 100%;
            left: 0;
        }
        
        /* Hero Section */
        .hero {
            background: var(--primary-gradient);
            color: white;
            padding: 8rem 0 6rem;
            position: relative;
            overflow: hidden;
        }
        
        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000"><polygon fill="%23ffffff08" points="0,1000 1000,0 1000,1000"/></svg>');
            background-size: cover;
        }
        
        .hero-content {
            position: relative;
            z-index: 2;
        }
        
        .hero h1 {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .hero p {
            font-size: 1.3rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }
        
        .btn-hero {
            background: rgba(255, 255, 255, 0.2);
            border: 2px solid rgba(255, 255, 255, 0.3);
            color: white;
            padding: 1rem 2rem;
            font-size: 1.1rem;
            font-weight: 500;
            border-radius: 50px;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }
        
        .btn-hero:hover {
            background: white;
            color: #667eea;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        
        /* Categories Section */
        .categories {
            padding: 6rem 0;
            background: #f8f9fa;
        }
        
        .category-card {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            text-align: center;
            transition: all 0.3s ease;
            border: none;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            height: 100%;
        }
        
        .category-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }
        
        .category-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 1.5rem;
            background: var(--primary-gradient);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: white;
        }
        
        /* Products Section */
        .products {
            padding: 6rem 0;
        }
        
        .product-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            transition: all 0.3s ease;
            border: none;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            height: 100%;
        }
        
        .product-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }
        
        .product-image {
            height: 250px;
            background: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 4rem;
            color: #dee2e6;
            position: relative;
            overflow: hidden;
        }
        
        .product-image::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: var(--primary-gradient);
            opacity: 0;
            transition: all 0.3s ease;
        }
        
        .product-card:hover .product-image::before {
            opacity: 0.1;
        }
        
        .product-info {
            padding: 1.5rem;
        }
        
        .product-title {
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #333;
        }
        
        .product-category {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }
        
        .product-price {
            font-size: 1.3rem;
            font-weight: 700;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        /* Buttons */
        .btn-gradient {
            background: var(--primary-gradient);
            border: none;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 50px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-gradient:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
            color: white;
        }
        
        /* Alert */
        .alert-success {
            background: var(--success-gradient);
            border: none;
            color: white;
            border-radius: 15px;
        }
        
        /* Footer */
        .footer {
            background: var(--dark-gradient);
            color: white;
            padding: 4rem 0 2rem;
        }
        
        .footer h5 {
            font-weight: 600;
            margin-bottom: 1.5rem;
        }
        
        .footer a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .footer a:hover {
            color: white;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .hero {
                padding: 4rem 0 3rem;
                text-align: center;
            }
            
            .hero h1 {
                font-size: 2.5rem;
                margin-bottom: 1rem;
            }
            
            .hero p {
                font-size: 1.1rem;
                margin-bottom: 1.5rem;
            }
            
            .btn-hero {
                padding: 0.75rem 1.5rem;
                font-size: 1rem;
            }
            
            .categories {
                padding: 3rem 0;
            }
            
            .products {
                padding: 3rem 0;
            }
            
            .category-card {
                padding: 1.5rem;
                margin-bottom: 1rem;
            }
            
            .category-icon {
                width: 60px;
                height: 60px;
                font-size: 1.5rem;
                margin-bottom: 1rem;
            }
            
            .product-image {
                height: 200px;
                font-size: 3rem;
            }
            
            .product-info {
                padding: 1rem;
            }
            
            .navbar-brand {
                font-size: 1.4rem;
            }
            
            .nav-link {
                padding: 0.5rem;
                font-size: 0.9rem;
            }
            
            .footer {
                padding: 2rem 0 1rem;
                text-align: center;
            }
        }
        
        @media (max-width: 480px) {
            .hero h1 {
                font-size: 2rem;
            }
            
            .hero p {
                font-size: 1rem;
            }
            
            .category-card {
                padding: 1rem;
            }
            
            .category-icon {
                width: 50px;
                height: 50px;
                font-size: 1.2rem;
            }
            
            .product-image {
                height: 180px;
                font-size: 2.5rem;
            }
            
            .container {
                padding: 0 1rem;
            }
        }
        
        /* Touch-friendly improvements */
        @media (hover: none) and (pointer: coarse) {
            .category-card:hover {
                transform: none;
            }
            
            .product-card:hover {
                transform: none;
            }
            
            .btn-hero:hover {
                transform: none;
            }
            
            .btn-gradient:hover {
                transform: none;
            }
        }
        
        /* Animations */
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        
        .floating {
            animation: float 3s ease-in-out infinite;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .hero h1 {
                font-size: 2.5rem;
            }
            
            .hero p {
                font-size: 1.1rem;
            }
        }
    </style>
</head>
<body>
    <?php include 'navigation-nav.php'; ?>
    
    <?php if ($logoutSuccess): ?>
        <div class="alert alert-success alert-dismissible fade show m-0" role="alert" style="margin-top: 76px !important;">
            <div class="container">
                <i class="fas fa-check-circle me-2"></i>ออกจากระบบเรียบร้อยแล้ว
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    <?php endif; ?>
    
    <!-- Modern Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <h1 class="hero-title">
                    ยินดีต้อนรับสู่ <span style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">Shopp</span>
                </h1>
                <p class="hero-subtitle">
                    ค้นพบสินค้าคุณภาพดีในราคาที่เหมาะสม พร้อมบริการที่ดีที่สุด
                </p>
                <div class="d-flex gap-3 flex-wrap justify-content-center">
                    <a href="products.php" class="btn btn-primary btn-lg">
                        <i class="fas fa-shopping-bag me-2"></i>เริ่มช้อปปิ้ง
                    </a>
                    <a href="#products" class="btn btn-secondary btn-lg">
                        <i class="fas fa-list me-2"></i>ดูสินค้า
                    </a>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Categories Section -->
    <section id="categories" class="categories">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center mb-5" data-aos="fade-up">
                    <h2 class="display-4 fw-bold mb-3">หมวดหมู่สินค้า</h2>
                    <p class="lead text-muted">เลือกช้อปตามหมวดหมู่ที่คุณสนใจ</p>
                </div>
            </div>
            
            <div class="row g-4">
                <?php 
                $icons = ['fas fa-laptop', 'fas fa-tshirt', 'fas fa-home', 'fas fa-gamepad', 'fas fa-book'];
                foreach ($categories as $index => $category): 
                ?>
                    <div class="col-lg-2 col-md-4 col-sm-6" data-aos="fade-up" data-aos-delay="<?php echo $index * 100; ?>">
                        <div class="category-card">
                            <div class="category-icon">
                                <i class="<?php echo $icons[$index % count($icons)]; ?>"></i>
                            </div>
                            <h5 class="fw-bold"><?php echo htmlspecialchars($category['name']); ?></h5>
                            <p class="text-muted mb-3"><?php echo htmlspecialchars($category['description']); ?></p>
                            <a href="products.php?category=<?php echo $category['id']; ?>" class="btn btn-gradient btn-sm">
                                ดูสินค้า
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    
    <!-- Modern Products Section -->
    <section class="products-section" id="products">
        <div class="container">
            <h2 class="section-title fade-in">สินค้าแนะนำ</h2>
            
            <div class="products-grid">
                <?php foreach ($products as $index => $product): ?>
                <div class="product-card fade-in" style="animation-delay: <?php echo $index * 0.1; ?>s;">
                    <div class="product-image">
                        <?php if (!empty($product['image_url'])): ?>
                            <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" style="width: 100%; height: 100%; object-fit: cover; border-radius: var(--radius-xl) var(--radius-xl) 0 0;">
                        <?php else: ?>
                            <i class="fas fa-image"></i>
                        <?php endif; ?>
                    </div>
                    <div class="product-info">
                        <h3 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h3>
                        <div class="product-price">฿<?php echo number_format($product['price'], 2); ?></div>
                        <p class="product-description"><?php echo htmlspecialchars(substr($product['description'], 0, 80)); ?>...</p>
                        <div class="d-flex gap-2 mt-3">
                            <a href="product_detail.php?id=<?php echo $product['id']; ?>" class="btn btn-secondary flex-1">
                                <i class="fas fa-eye me-1"></i>ดูรายละเอียด
                            </a>
                            <button class="btn btn-primary add-to-cart-btn" 
                                    data-product-id="<?php echo $product['id']; ?>"
                                    data-product-name="<?php echo htmlspecialchars($product['name']); ?>"
                                    data-product-price="<?php echo $product['price']; ?>">
                                <i class="fas fa-cart-plus"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="text-center mt-5">
                <a href="products.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-th-large me-2"></i>ดูสินค้าทั้งหมด
                </a>
            </div>
        </div>
    </section>
    
    <!-- Modern Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3 class="footer-title"><i class="fas fa-shopping-bag me-2"></i>Shopp</h3>
                    <p class="footer-description">ร้านค้าออนไลน์ที่ทันสมัยและครบครัน พร้อมให้บริการลูกค้าด้วยความใส่ใจ</p>
                    <div class="social-links">
                        <a href="#" class="social-link"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-line"></i></a>
                    </div>
                </div>
                
                <div class="footer-section">
                    <h4 class="footer-subtitle">เมนู</h4>
                    <ul class="footer-links">
                        <li><a href="#home">หน้าหลัก</a></li>
                        <li><a href="#categories">หมวดหมู่</a></li>
                        <li><a href="#products">สินค้า</a></li>
                        <li><a href="#contact">ติดต่อ</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h4 class="footer-subtitle">บริการลูกค้า</h4>
                    <ul class="footer-links">
                        <li><a href="#">วิธีการสั่งซื้อ</a></li>
                        <li><a href="#">การชำระเงิน</a></li>
                        <li><a href="#">การจัดส่ง</a></li>
                        <li><a href="#">การคืนสินค้า</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h4 class="footer-subtitle">ติดต่อเรา</h4>
                    <div class="contact-info">
                        <div class="contact-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <span>123 ถนนสุขุมวิท กรุงเทพฯ</span>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-phone"></i>
                            <span>02-123-4567</span>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-envelope"></i>
                            <span>info@shopp.com</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="footer-bottom">
                <div class="footer-copyright">
                    <p>&copy; 2024 Shopp. สงวนลิขสิทธิ์.</p>
                </div>
                <div class="footer-credits">
                    <p>พัฒนาด้วย <i class="fas fa-heart text-danger"></i> โดย Shopp Team</p>
                </div>
            </div>
        </div>
    </footer>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="assets/js/script.js"></script>
    
    <script>
        // Initialize AOS
        AOS.init({
            duration: 1000,
            once: true
        });
        

        
        // Smooth scrolling
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
        
        // Intersection Observer for fade-in animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, observerOptions);
        
        // Observe all fade-in elements
        document.querySelectorAll('.fade-in').forEach(el => {
            observer.observe(el);
        });
        
        // Add to cart functionality with modern UX
        document.querySelectorAll('.add-to-cart-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const productId = this.dataset.productId;
                const productName = this.dataset.productName;
                const productPrice = this.dataset.productPrice;
                const originalText = this.innerHTML;
                
                // Add loading state
                this.disabled = true;
                this.classList.add('loading');
                this.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>กำลังเพิ่ม...';
                this.style.opacity = '0.8';
                
                // Call real API
                fetch('add_to_cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        product_id: parseInt(productId),
                        quantity: 1
                    })
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        // Update cart count with animation
                        const cartCountElement = document.getElementById('cart-count');
                        if (cartCountElement && data.cart_count !== undefined) {
                            cartCountElement.style.transform = 'scale(1.3)';
                            cartCountElement.style.background = '#28a745';
                            cartCountElement.textContent = data.cart_count;
                            
                            setTimeout(() => {
                                cartCountElement.style.transform = 'scale(1)';
                                cartCountElement.style.background = '';
                            }, 300);
                        }
                        
                        // Success state
                        this.classList.remove('loading');
                        this.classList.add('success');
                        this.innerHTML = '<i class="fas fa-check me-2"></i>เพิ่มแล้ว!';
                        this.style.background = '#28a745';
                        
                        // Show success toast
                        Swal.fire({
                            icon: 'success',
                            title: 'เพิ่มสินค้าแล้ว!',
                            text: `${productName} ถูกเพิ่มลงในตะกร้าแล้ว`,
                            timer: 2000,
                            showConfirmButton: false,
                            toast: true,
                            position: 'top-end'
                        });
                        
                        // Reset button after 2 seconds
                        setTimeout(() => {
                            this.classList.remove('success');
                            this.innerHTML = originalText;
                            this.style.background = '';
                            this.disabled = false;
                            this.style.opacity = '1';
                        }, 2000);
                    } else {
                        throw new Error(data.message || 'ไม่สามารถเพิ่มสินค้าลงตะกร้าได้');
                    }
                })
                .catch(error => {
                    console.error('Error adding to cart:', error);
                    
                    // Error state
                    this.classList.remove('loading');
                    this.classList.add('error');
                    this.innerHTML = '<i class="fas fa-times me-2"></i>ผิดพลาด';
                    this.style.background = '#dc3545';
                    
                    // Show error message
                    let errorMessage = 'เกิดข้อผิดพลาดในการเพิ่มสินค้า';
                    if (error.message.includes('401')) {
                        errorMessage = 'กรุณาเข้าสู่ระบบก่อนใช้งาน';
                    } else if (error.message) {
                        errorMessage = error.message;
                    }
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'เกิดข้อผิดพลาด!',
                        text: errorMessage,
                        timer: 3000,
                        showConfirmButton: false,
                        toast: true,
                        position: 'top-end'
                    });
                    
                    // Reset button after 2 seconds
                    setTimeout(() => {
                        this.classList.remove('error');
                        this.innerHTML = originalText;
                        this.style.background = '';
                        this.disabled = false;
                        this.style.opacity = '1';
                    }, 2000);
                });
            });
        });
        
        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                navbar.style.background = 'rgba(255, 255, 255, 0.98)';
            } else {
                navbar.style.background = 'rgba(255, 255, 255, 0.95)';
            }
        });
    </script>
</body>
</html>