<?php
require 'db_connect.php'; // Ensures session_start() is called

$error_message = '';

// Redirect if already logged in
if (isset($_SESSION['masv'])) {
    header("Location: hocphan_list.php"); // Redirect to course list or dashboard
    exit();
}
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $masv = trim($_POST['username']); // Username is MaSV
    $password = trim($_POST['password']); // Plain text password

    if (empty($masv) || empty($password)) {
        $error_message = "Vui lòng nhập Mã Sinh Viên và Mật khẩu.";
    } else {
        // Prepare statement to prevent SQL injection
        $stmt = $conn->prepare("SELECT MaSV, HoTen, Password FROM SinhVien WHERE MaSV = ?");
        if ($stmt === false) {
             die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
        }

        $stmt->bind_param("s", $masv);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $student = $result->fetch_assoc();
            // Direct password comparison (NOT SECURE for production)
            // In production, use password_verify($password, $student['Password']) if password was hashed
            if ($password === $student['Password']) {
                // Login successful
                $_SESSION['masv'] = $student['MaSV'];
                $_SESSION['hoten'] = $student['HoTen'];
                // Regenerate session ID for security
                session_regenerate_id(true);
                header("Location: hocphan_list.php"); // Redirect to course registration page
                exit();
            } else {
                $error_message = "Mật khẩu không đúng.";
            }
        } else {
            $error_message = "Mã sinh viên không tồn tại.";
        }
        $stmt->close();
    }
}

// Include header AFTER potential redirects
include 'header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-4">
        <div class="card">
            <div class="card-header text-center">
                <h4>Đăng Nhập</h4>
            </div>
            <div class="card-body">
                <?php if ($error_message): ?>
                    <div class="alert alert-danger"><?php echo $error_message; ?></div>
                <?php endif; ?>
                <form action="login.php" method="post">
                    <div class="mb-3">
                        <label for="username" class="form-label"><i class="fas fa-user"></i> Username (Mã SV)</label>
                        <input type="text" class="form-control" id="username" name="username" required value="<?php echo isset($masv) ? htmlspecialchars($masv) : ''; ?>">
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label"><i class="fas fa-lock"></i> Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Login</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>