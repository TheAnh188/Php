<?php
require 'db_connect.php';
include 'auth_check.php'; // Ensure user is logged in

$masv_delete = $_GET['id'] ?? null;

if (!$masv_delete) {
    $_SESSION['message'] = "Mã sinh viên không hợp lệ.";
    $_SESSION['message_type'] = "warning";
    header("Location: student_list.php");
    exit();
}

// Optional: Add a confirmation step here before deleting.
// For simplicity, this directly deletes.

// Fetch image path BEFORE deleting the record to delete the file
$stmt_img = $conn->prepare("SELECT Hinh FROM SinhVien WHERE MaSV = ?");
$stmt_img->bind_param("s", $masv_delete);
$stmt_img->execute();
$result_img = $stmt_img->get_result();
$image_path = null;
if($result_img->num_rows === 1) {
    $row_img = $result_img->fetch_assoc();
    $image_path = $row_img['Hinh'];
}
$stmt_img->close();

// Prepare delete statement
// ON DELETE CASCADE in DB schema should handle DangKy and ChiTietDangKy automatically
$stmt = $conn->prepare("DELETE FROM SinhVien WHERE MaSV = ?");
if ($stmt === false) {
    die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
}
$stmt->bind_param("s", $masv_delete);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        // Delete the image file if it exists
        if (!empty($image_path) && file_exists(ltrim($image_path, '/'))) {
             @unlink(ltrim($image_path, '/'));
        }
        $_SESSION['message'] = "Xóa sinh viên thành công!";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Không tìm thấy sinh viên để xóa hoặc xóa không thành công.";
        $_SESSION['message_type'] = "warning";
    }
} else {
    $_SESSION['message'] = "Lỗi khi xóa sinh viên: " . $stmt->error;
    // Check for foreign key constraints if ON DELETE CASCADE is not set/working
    if ($conn->errno == 1451) { // Foreign key constraint fails
       $_SESSION['message'] .= " Sinh viên này có thể đã đăng ký học phần. Không thể xóa.";
    }
    $_SESSION['message_type'] = "danger";
}

$stmt->close();
header("Location: student_list.php");
exit();
?>