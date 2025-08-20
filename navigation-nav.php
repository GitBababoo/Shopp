<?php
// ตรวจสอบว่ามีการ include ไฟล์ config หรือไม่
if (!isset($currentUser)) {
    try {
        if (!defined('DB_HOST')) {
            require_once 'config/config.php';
        }
        if (!class_exists('User')) {
            require_once 'classes/User.php';
        }
        
        $user = new User();
        $currentUser = $user->getCurrentUser();
    } catch (Exception $e) {
        // หากเกิดข้อผิดพลาด ให้ตั้งค่า currentUser เป็น null
        $currentUser = null;
        error_log('Navigation error: ' . $e->getMessage());
    }
}
?>

<!-- Modern Navigation -->
<nav class="navbar navbar-expand-lg">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            <i class="fas fa-shopping-bag me-2"></i>Shopp
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">หน้าแรก</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="products.php">สินค้า</a>
                </li>
                <?php if ($currentUser && $user->isAdmin()): ?>
                <li class="nav-item">
                    <a class="nav-link" href="admin/index.php">จัดการระบบ</a>
                </li>
                <?php endif; ?>
            </ul>
            
            <ul class="navbar-nav align-items-center">
                <li class="nav-item">
                    <a class="nav-link position-relative cart-link" href="cart.php" title="ตะกร้าสินค้า">
                        <i class="fas fa-shopping-cart cart-icon"></i>
                        <span class="cart-badge" id="cart-badge">
                            <span class="cart-count" id="cart-count">0</span>
                        </span>
                        <div class="cart-pulse" id="cart-pulse"></div>
                    </a>
                </li>
                
                <?php if ($currentUser): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user me-1"></i><?php echo htmlspecialchars($currentUser['username']); ?>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="profile.php">โปรไฟล์</a></li>
                        <li><a class="dropdown-item" href="orders.php">คำสั่งซื้อ</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="auth/logout.php">ออกจากระบบ</a></li>
                    </ul>
                </li>
                <?php else: ?>
                <li class="nav-item">
                    <a class="nav-link" href="auth/login.php">เข้าสู่ระบบ</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="auth/register.php">สมัครสมาชิก</a>
                </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<style>
.cart-link {
    transition: all 0.3s ease;
    position: relative;
    overflow: visible;
}

.cart-icon {
    font-size: 1.2rem;
    transition: all 0.3s ease;
}

.cart-link:hover .cart-icon {
    transform: scale(1.1);
    color: #007bff;
}

.cart-badge {
    position: absolute;
    top: -8px;
    right: -8px;
    background: linear-gradient(135deg, #dc3545, #c82333);
    color: white;
    border-radius: 50%;
    min-width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    font-weight: 600;
    border: 2px solid white;
    box-shadow: 0 2px 8px rgba(220, 53, 69, 0.3);
    transition: all 0.3s ease;
    transform: scale(0);
}

.cart-badge.show {
    transform: scale(1);
}

.cart-badge.animate {
    animation: cartBounce 0.6s ease;
}

.cart-pulse {
    position: absolute;
    top: -8px;
    right: -8px;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: rgba(220, 53, 69, 0.3);
    transform: scale(0);
    pointer-events: none;
}

.cart-pulse.animate {
    animation: pulseEffect 0.8s ease;
}

@keyframes cartBounce {
    0% { transform: scale(0); }
    50% { transform: scale(1.3); }
    100% { transform: scale(1); }
}

@keyframes pulseEffect {
    0% {
        transform: scale(1);
        opacity: 1;
    }
    100% {
        transform: scale(2.5);
        opacity: 0;
    }
}

.cart-count {
    line-height: 1;
}

/* Loading state */
.cart-loading {
    opacity: 0.6;
    pointer-events: none;
}
</style>

<script>
// โหลดจำนวนสินค้าในตะกร้าเมื่อหน้าเว็บโหลด
document.addEventListener('DOMContentLoaded', function() {
    updateCartCount();
    
    // ตั้งค่า interval สำหรับอัปเดตจำนวนสินค้าทุก 30 วินาที
    setInterval(updateCartCount, 30000);
});

function updateCartCount(showAnimation = false) {
    const cartBadge = document.getElementById('cart-badge');
    const cartCount = document.getElementById('cart-count');
    const cartPulse = document.getElementById('cart-pulse');
    
    // แสดง loading state
    if (cartBadge) {
        cartBadge.classList.add('cart-loading');
    }
    
    fetch('get_cart_count.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const newCount = data.count || 0;
                const currentCount = parseInt(cartCount.textContent) || 0;
                
                // อัปเดตจำนวน
                cartCount.textContent = newCount;
                
                // แสดง/ซ่อน badge
                if (newCount > 0) {
                    cartBadge.classList.add('show');
                    
                    // แสดง animation หากจำนวนเพิ่มขึ้น
                    if (showAnimation && newCount > currentCount) {
                        cartBadge.classList.add('animate');
                        cartPulse.classList.add('animate');
                        
                        // ลบ animation class หลังจากเสร็จสิ้น
                        setTimeout(() => {
                            cartBadge.classList.remove('animate');
                            cartPulse.classList.remove('animate');
                        }, 800);
                    }
                } else {
                    cartBadge.classList.remove('show');
                }
                
                // ลบ loading state
                cartBadge.classList.remove('cart-loading');
            }
        })
        .catch(error => {
            console.error('Error fetching cart count:', error);
            // ลบ loading state แม้เกิดข้อผิดพลาด
            if (cartBadge) {
                cartBadge.classList.remove('cart-loading');
            }
        });
}

// ฟังก์ชันสำหรับอัปเดตจำนวนสินค้าพร้อม animation
function updateCartCountWithAnimation(newCount) {
    const cartBadge = document.getElementById('cart-badge');
    const cartCount = document.getElementById('cart-count');
    const cartPulse = document.getElementById('cart-pulse');
    
    if (cartCount && cartBadge) {
        const currentCount = parseInt(cartCount.textContent) || 0;
        
        // อัปเดตจำนวน
        cartCount.textContent = newCount;
        
        if (newCount > 0) {
            cartBadge.classList.add('show');
            
            // แสดง animation หากจำนวนเพิ่มขึ้น
            if (newCount > currentCount) {
                cartBadge.classList.add('animate');
                cartPulse.classList.add('animate');
                
                setTimeout(() => {
                    cartBadge.classList.remove('animate');
                    cartPulse.classList.remove('animate');
                }, 800);
            }
        } else {
            cartBadge.classList.remove('show');
        }
    }
}

// ทำให้ฟังก์ชันเป็น global เพื่อให้หน้าอื่นเรียกใช้ได้
window.updateCartCount = updateCartCount;
window.updateCartCountWithAnimation = updateCartCountWithAnimation;
</script>