<?php
require 'db_connect.php';
include 'auth_check.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['confirm_registration'])) {

    $cart_items = $_SESSION['cart'] ?? [];
    if (empty($cart_items)) {
        $_SESSION['message'] = "Giỏ đăng ký trống.";
        $_SESSION['message_type'] = "warning";
        header("Location: cart_view.php");
        exit();
    }

    $masv_logged_in = $_SESSION['masv'];

    // --- Start Transaction ---
    $conn->begin_transaction();

    try {
        // 1. Insert into DangKy table
        $sql_dangky = "INSERT INTO DangKy (MaSV, NgayDK) VALUES (?, CURDATE())";
        $stmt_dangky = $conn->prepare($sql_dangky);
        if ($stmt_dangky === false) throw new Exception("Prepare failed (DangKy): " . $conn->error);
        $stmt_dangky->bind_param("s", $masv_logged_in);
        if (!$stmt_dangky->execute()) throw new Exception("Execute failed (DangKy): " . $stmt_dangky->error);

        $madk = $conn->insert_id; // Get the last inserted MaDK
        $stmt_dangky->close();

        if (!$madk) throw new Exception("Không thể tạo mã đăng ký.");


        // 2. Insert into ChiTietDangKy and Update HocPhan SoLuong
        $sql_chitiet = "INSERT INTO ChiTietDangKy (MaDK, MaHP) VALUES (?, ?)";
        $stmt_chitiet = $conn->prepare($sql_chitiet);
        if ($stmt_chitiet === false) throw new Exception("Prepare failed (ChiTiet): " . $conn->error);

        $sql_update_soluong = "UPDATE HocPhan SET SoLuong = SoLuong - 1 WHERE MaHP = ? AND SoLuong > 0";
        $stmt_update_soluong = $conn->prepare($sql_update_soluong);
         if ($stmt_update_soluong === false) throw new Exception("Prepare failed (Update SoLuong): " . $conn->error);


        foreach ($cart_items as $mahp => $item) {
            // Insert into ChiTietDangKy
            $stmt_chitiet->bind_param("is", $madk, $mahp);
            if (!$stmt_chitiet->execute()) throw new Exception("Execute failed (ChiTiet for $mahp): " . $stmt_chitiet->error);

            // Update SoLuong in HocPhan
            $stmt_update_soluong->bind_param("s", $mahp);
            if (!$stmt_update_soluong->execute()) throw new Exception("Execute failed (Update SoLuong for $mahp): " . $stmt_update_soluong->error);

            // Check if the update actually reduced the count (i.e., SoLuong was > 0)
            if ($stmt_update_soluong->affected_rows === 0) {
                 throw new Exception("Học phần '" . htmlspecialchars($item['TenHP']) . "' ($mahp) đã hết chỗ trong lúc bạn đăng ký.");
            }
        }
        $stmt_chitiet->close();
        $stmt_update_soluong->close();

        // --- If all successful, Commit Transaction ---
        $conn->commit();

        // Clear the cart after successful registration
        unset($_SESSION['cart']);

        $_SESSION['message'] = "Đăng ký học phần thành công!";
        $_SESSION['message_type'] = "success";
        // Redirect to a success page showing the details
        header("Location: dangky_success.php?madk=" . $madk);
        exit();

    } catch (Exception $e) {
        // --- If any error occurs, Rollback Transaction ---
        $conn->rollback();

        $_SESSION['message'] = "Đăng ký thất bại: " . $e->getMessage();
        $_SESSION['message_type'] = "danger";
        // Redirect back to cart or confirmation page
        header("Location: cart_view.php");
        exit();
    }

} else {
    // Redirect if not a valid POST request
    header("Location: cart_view.php");
    exit();
}
?>