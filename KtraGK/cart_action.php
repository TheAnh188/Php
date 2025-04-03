<?php
// cart_action.php

require 'db_connect.php'; // Đảm bảo session_start() được gọi và có kết nối CSDL
include 'auth_check.php'; // Đảm bảo người dùng đã đăng nhập

// Lấy action và mahp từ URL (nếu có)
$action = $_GET['action'] ?? null;
$mahp = $_GET['mahp'] ?? null; // mahp có thể null, đặc biệt với action='clear'

// Trang mặc định để chuyển hướng về nếu có lỗi hoặc không rõ ràng
$redirect_page = $_SERVER['HTTP_REFERER'] ?? 'hocphan_list.php';

// Khởi tạo giỏ hàng trong session nếu chưa tồn tại
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// --- Xử lý dựa trên action ---

// --- ADD Action ---
if ($action === 'add') {
    // *** KIỂM TRA mahp CHỈ KHI THÊM ***
    if (!$mahp) {
        $_SESSION['message'] = "Mã học phần không hợp lệ để thêm.";
        $_SESSION['message_type'] = "warning";
        header("Location: " . $redirect_page);
        exit();
    }

    // Kiểm tra xem học phần đã có trong giỏ chưa
    if (isset($_SESSION['cart'][$mahp])) {
        $_SESSION['message'] = "Học phần '" . htmlspecialchars($mahp) . "' đã có trong giỏ.";
        $_SESSION['message_type'] = "info";
    } else {
        // Kiểm tra số lượng còn lại trước khi thêm
        $stmt = $conn->prepare("SELECT TenHP, SoTinChi, SoLuong FROM HocPhan WHERE MaHP = ?");
         if ($stmt) { // Kiểm tra prepare thành công không
            $stmt->bind_param("s", $mahp);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $hocphan = $result->fetch_assoc();
                if ($hocphan['SoLuong'] > 0) {
                    // Thêm vào giỏ hàng
                    $_SESSION['cart'][$mahp] = [
                        'TenHP' => $hocphan['TenHP'],
                        'SoTinChi' => $hocphan['SoTinChi']
                    ];
                    $_SESSION['message'] = "Đã thêm học phần '" . htmlspecialchars($hocphan['TenHP']) . "' vào giỏ.";
                    $_SESSION['message_type'] = "success";
                } else {
                    $_SESSION['message'] = "Học phần '" . htmlspecialchars($hocphan['TenHP']) . "' đã hết chỗ.";
                    $_SESSION['message_type'] = "danger";
                }
            } else {
                 $_SESSION['message'] = "Không tìm thấy học phần '" . htmlspecialchars($mahp) . "'.";
                 $_SESSION['message_type'] = "danger";
            }
            $stmt->close();
         } else {
            // Lỗi chuẩn bị câu lệnh
            error_log("Prepare failed (SELECT HocPhan): (" . $conn->errno . ") " . $conn->error); // Ghi log lỗi
            $_SESSION['message'] = "Lỗi hệ thống khi kiểm tra học phần.";
            $_SESSION['message_type'] = "danger";
         }
    }
    // Chuyển hướng về trang danh sách học phần sau khi thêm
    header("Location: hocphan_list.php");
    exit();

}
// --- REMOVE Action ---
elseif ($action === 'remove') {
     // *** KIỂM TRA mahp CHỈ KHI XÓA ***
    if (!$mahp) {
        $_SESSION['message'] = "Mã học phần không hợp lệ để xóa.";
        $_SESSION['message_type'] = "warning";
        header("Location: cart_view.php"); // Chuyển về giỏ hàng
        exit();
    }

    if (isset($_SESSION['cart'][$mahp])) {
        $removed_name = $_SESSION['cart'][$mahp]['TenHP']; // Lấy tên trước khi xóa
        unset($_SESSION['cart'][$mahp]); // Xóa học phần cụ thể khỏi session
        $_SESSION['message'] = "Đã xóa học phần '" . htmlspecialchars($removed_name) . "' khỏi giỏ.";
        $_SESSION['message_type'] = "warning";
    } else {
        $_SESSION['message'] = "Học phần không có trong giỏ.";
        $_SESSION['message_type'] = "info";
    }
     // Chuyển hướng về trang giỏ hàng sau khi xóa
    header("Location: cart_view.php");
    exit();
}
// --- CLEAR Action ---
elseif ($action === 'clear') {
     // *** KHÔNG KIỂM TRA mahp ở đây ***
     // *** DÒNG NÀY SẼ XÓA TOÀN BỘ GIỎ HÀNG ***
     unset($_SESSION['cart']);

     $_SESSION['message'] = "Giỏ đăng ký đã được xóa.";
     $_SESSION['message_type'] = "info";
     header("Location: cart_view.php"); // Chuyển về giỏ hàng (lúc này sẽ trống)
     exit();
}
// --- Hành động không hợp lệ ---
else {
    $_SESSION['message'] = "Hành động không hợp lệ.";
    $_SESSION['message_type'] = "danger";
    header("Location: " . $redirect_page);
    exit();
}

?>