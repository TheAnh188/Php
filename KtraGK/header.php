<?php
// Ensure session is started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$is_logged_in = isset($_SESSION['masv']);
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Sinh Viên & Đăng Ký Học Phần</title>
    <!-- Bootstrap CSS (assuming you want responsiveness - Câu 7) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { padding-top: 60px; /* Adjust based on navbar height */ }
        .student-img-list { max-width: 60px; height: auto; }
        .student-img-details { max-width: 150px; height: auto; }
        .navbar { background-color: #f8f9fa; border-bottom: 1px solid #dee2e6;}
        .table th { background-color: #e9ecef; }
        .container { max-width: 1140px; } /* Optional: Limit container width */
        .pagination .page-link { color: #0d6efd; }
        .pagination .active .page-link { z-index: 3; color: #fff; background-color: #0d6efd; border-color: #0d6efd; }
        .action-links a { margin-right: 5px; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg fixed-top navbar-light bg-light">
    <div class="container-fluid">
        <a class="navbar-brand" href="<?php echo $is_logged_in ? 'hocphan_list.php' : 'login.php'; ?>">QLSV</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <?php if ($is_logged_in): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page == 'student_list.php' ? 'active' : ''; ?>" href="student_list.php">Sinh Viên</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page == 'hocphan_list.php' ? 'active' : ''; ?>" href="hocphan_list.php">Học Phần</a>
                    </li>
                    <li class="nav-item">
                        <?php
                            $cart_count = 0;
                            if (isset($_SESSION['cart'])) {
                                $cart_count = count($_SESSION['cart']);
                            }
                        ?>
                        <a class="nav-link <?php echo $current_page == 'cart_view.php' ? 'active' : ''; ?>" href="cart_view.php">
                            Giỏ Đăng Ký
                        </a>
                    </li>
                     <!-- Add other links as needed -->
                <?php endif; ?>
            </ul>
             <ul class="navbar-nav ms-auto">
                 <?php if ($is_logged_in): ?>
                     <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Chào, <?php echo htmlspecialchars($_SESSION['hoten'] ?? $_SESSION['masv']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="logout.php">Đăng Xuất</a></li>
                        </ul>
                    </li>
                 <?php else: ?>
                     <li class="nav-item">
                         <a class="nav-link <?php echo $current_page == 'login.php' ? 'active' : ''; ?>" href="login.php">Đăng Nhập</a>
                     </li>
                 <?php endif; ?>
             </ul>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <?php
    // Display messages stored in session
    if (isset($_SESSION['message'])):
    ?>
        <div class="alert alert-<?php echo $_SESSION['message_type'] ?? 'info'; ?> alert-dismissible fade show" role="alert">
            <?php echo $_SESSION['message']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
    endif;
    ?>