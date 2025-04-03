<?php
require 'db_connect.php';
include 'auth_check.php'; // Redirect to login if not logged in

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $masv = trim($_POST['masv']);
    $hoten = trim($_POST['hoten']);
    $password = $_POST['password']; // Get password (DO NOT store plaintext in production!)
    $gioitinh = $_POST['gioitinh'] ?? null;
    $ngaysinh = !empty($_POST['ngaysinh']) ? $_POST['ngaysinh'] : null;
    $manganh = $_POST['manganh'];
    $hinh_path = null; // Path to store in DB

    // --- Basic Validation ---
    $errors = [];
    if (empty($masv)) $errors[] = "Mã SV là bắt buộc.";
    if (strlen($masv) > 10) $errors[] = "Mã SV không được quá 10 ký tự.";
    if (empty($hoten)) $errors[] = "Họ tên là bắt buộc.";
    if (empty($password)) $errors[] = "Mật khẩu là bắt buộc."; // Basic check
    if (empty($manganh)) $errors[] = "Ngành học là bắt buộc.";

    // Check if MaSV already exists
    $stmt_check = $conn->prepare("SELECT MaSV FROM SinhVien WHERE MaSV = ?");
    $stmt_check->bind_param("s", $masv);
    $stmt_check->execute();
    $stmt_check->store_result();
    if ($stmt_check->num_rows > 0) {
        $errors[] = "Mã sinh viên '" . htmlspecialchars($masv) . "' đã tồn tại.";
    }
    $stmt_check->close();

    // --- Image Upload Handling ---
    if (isset($_FILES['hinh']) && $_FILES['hinh']['error'] == UPLOAD_ERR_OK) {
        $target_dir = "uploads/"; // Make sure this directory exists and is writable
        $imageFileType = strtolower(pathinfo($_FILES["hinh"]["name"], PATHINFO_EXTENSION));
        // Generate a unique name to prevent overwriting
        $target_file = $target_dir . "sv_" . $masv . "_" . uniqid() . '.' . $imageFileType;
        $uploadOk = 1;

        // Check if image file is a actual image or fake image
        $check = getimagesize($_FILES["hinh"]["tmp_name"]);
        if($check === false) {
            $errors[] = "File không phải là hình ảnh.";
            $uploadOk = 0;
        }

        // Allow certain file formats
        if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
        && $imageFileType != "gif" ) {
            $errors[] = "Chỉ cho phép file JPG, JPEG, PNG & GIF.";
            $uploadOk = 0;
        }

        // Check file size (e.g., 5MB limit)
        if ($_FILES["hinh"]["size"] > 5000000) {
            $errors[] = "Xin lỗi, file của bạn quá lớn (lớn hơn 5MB).";
            $uploadOk = 0;
        }

        // Try to upload file if all checks pass
        if ($uploadOk == 1) {
            if (move_uploaded_file($_FILES["hinh"]["tmp_name"], $target_file)) {
                $hinh_path = "/" . $target_file; // Store relative path starting with /
            } else {
                 $errors[] = "Xin lỗi, có lỗi khi upload file của bạn.";
            }
        }
    } elseif (isset($_FILES['hinh']) && $_FILES['hinh']['error'] != UPLOAD_ERR_NO_FILE) {
        // Handle other upload errors
        $errors[] = "Lỗi upload hình ảnh: " . $_FILES['hinh']['error'];
    }

    // --- Insert into Database ---
    if (empty($errors)) {
        // **IMPORTANT**: Hash the password before storing in a real application!
        // $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO SinhVien (MaSV, HoTen, GioiTinh, NgaySinh, Hinh, MaNganh, Password)
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);

        if ($stmt === false) {
             die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
        }

        // Bind parameters (use $password directly here for plaintext, use $hashed_password for hashed)
        $stmt->bind_param("sssssss", $masv, $hoten, $gioitinh, $ngaysinh, $hinh_path, $manganh, $password);

        if ($stmt->execute()) {
            $_SESSION['message'] = "Thêm sinh viên thành công!";
            $_SESSION['message_type'] = "success";
            header("Location: student_list.php");
            exit();
        } else {
            $_SESSION['message'] = "Lỗi khi thêm sinh viên: " . $stmt->error;
            $_SESSION['message_type'] = "danger";
            // Redirect back to form with error (consider passing back input data too)
            header("Location: student_add.php");
            exit();
        }
        $stmt->close();

    } else {
        // Store errors in session and redirect back to form
        $_SESSION['message'] = "Thêm thất bại. Vui lòng kiểm tra lỗi.";
        $_SESSION['message_type'] = "danger";
        $_SESSION['form_errors'] = $errors; // Optionally store errors
        $_SESSION['form_data'] = $_POST;    // Optionally store input data
        header("Location: student_add.php");
        exit();
    }

} else {
    // Redirect if accessed directly without POST
    header("Location: student_add.php");
    exit();
}
?>