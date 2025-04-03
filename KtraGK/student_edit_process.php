<?php
require 'db_connect.php';
include 'auth_check.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $masv_original = $_POST['masv_original']; // Get the original MaSV
    $masv = trim($_POST['masv']); // Should be the same as original, as it's readonly
    $hoten = trim($_POST['hoten']);
    $password = $_POST['password']; // New password, if provided
    $gioitinh = $_POST['gioitinh'] ?? null;
    $ngaysinh = !empty($_POST['ngaysinh']) ? $_POST['ngaysinh'] : null;
    $manganh = $_POST['manganh'];
    $current_image = $_POST['current_image'] ?? null;
    $hinh_path = $current_image; // Default to current image path

    // --- Basic Validation ---
    $errors = [];
    if ($masv !== $masv_original) $errors[] = "Không được phép thay đổi Mã SV."; // Security check
    if (empty($hoten)) $errors[] = "Họ tên là bắt buộc.";
    if (empty($manganh)) $errors[] = "Ngành học là bắt buộc.";

    // --- Image Upload Handling (if new image provided) ---
    if (isset($_FILES['hinh']) && $_FILES['hinh']['error'] == UPLOAD_ERR_OK) {
        $target_dir = "uploads/";
        $imageFileType = strtolower(pathinfo($_FILES["hinh"]["name"], PATHINFO_EXTENSION));
        $target_file = $target_dir . "sv_" . $masv . "_" . uniqid() . '.' . $imageFileType;
        $uploadOk = 1;

        // Perform checks (same as add process)
        $check = getimagesize($_FILES["hinh"]["tmp_name"]);
        if($check === false) { $errors[] = "File không phải là hình ảnh."; $uploadOk = 0; }
        if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" ) { $errors[] = "Chỉ cho phép file JPG, JPEG, PNG & GIF."; $uploadOk = 0; }
        if ($_FILES["hinh"]["size"] > 5000000) { $errors[] = "File quá lớn (lớn hơn 5MB)."; $uploadOk = 0; }

        if ($uploadOk == 1) {
            // Attempt to delete old image if it exists and is different
            if (!empty($current_image) && file_exists(ltrim($current_image, '/'))) {
                 @unlink(ltrim($current_image, '/')); // Use @ to suppress errors if file not found
            }
            // Move the new file
            if (move_uploaded_file($_FILES["hinh"]["tmp_name"], $target_file)) {
                $hinh_path = "/" . $target_file; // Update path for DB
            } else {
                 $errors[] = "Lỗi khi upload file mới.";
            }
        }
    } elseif (isset($_FILES['hinh']) && $_FILES['hinh']['error'] != UPLOAD_ERR_NO_FILE) {
        $errors[] = "Lỗi upload hình ảnh: " . $_FILES['hinh']['error'];
    }

    // --- Update Database ---
    if (empty($errors)) {
        // Build the update query dynamically based on whether password is changed
        $params = [];
        $types = "";
        $sql = "UPDATE SinhVien SET HoTen = ?, GioiTinh = ?, NgaySinh = ?, Hinh = ?, MaNganh = ?";
        $params[] = $hoten; $types .= "s";
        $params[] = $gioitinh; $types .= "s";
        $params[] = $ngaysinh; $types .= "s";
        $params[] = $hinh_path; $types .= "s";
        $params[] = $manganh; $types .= "s";

        if (!empty($password)) {
            $sql .= ", Password = ?";
            // **IMPORTANT**: Hash the password in a real application!
            // $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            // $params[] = $hashed_password; $types .= "s";
             $params[] = $password; $types .= "s"; // Plaintext for now
        }

        $sql .= " WHERE MaSV = ?";
        $params[] = $masv_original; $types .= "s";

        $stmt = $conn->prepare($sql);

        if ($stmt === false) {
             die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
        }

        $stmt->bind_param($types, ...$params); // Use splat operator (...)

        if ($stmt->execute()) {
             $_SESSION['message'] = "Cập nhật thông tin sinh viên thành công!";
             $_SESSION['message_type'] = "success";
             header("Location: student_list.php");
             exit();
        } else {
            $_SESSION['message'] = "Lỗi khi cập nhật: " . $stmt->error;
            $_SESSION['message_type'] = "danger";
            // Redirect back to edit form with error
            header("Location: student_edit.php?id=" . urlencode($masv_original));
            exit();
        }
        $stmt->close();

    } else {
        // Store errors and redirect back to edit form
        $_SESSION['message'] = "Cập nhật thất bại. Vui lòng kiểm tra lỗi.";
        $_SESSION['message_type'] = "danger";
        // You might want to store errors and input data in session here too
        header("Location: student_edit.php?id=" . urlencode($masv_original));
        exit();
    }

} else {
    // Redirect if accessed directly
    header("Location: student_list.php");
    exit();
}
?>