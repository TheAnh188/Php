<?php
require 'db_connect.php';
include 'auth_check.php';

// Lấy lỗi và dữ liệu cũ từ session (nếu có) sau khi redirect từ process
$form_errors = $_SESSION['form_errors'] ?? [];
$form_data = $_SESSION['form_data'] ?? [];

// Xóa lỗi và dữ liệu khỏi session để không hiển thị lại ở lần load sau
unset($_SESSION['form_errors']);
unset($_SESSION['form_data']);


// Fetch Nganh Hoc for dropdown
$nganh_hoc_list = [];
$sql_nganh = "SELECT MaNganh, TenNganh FROM NganhHoc ORDER BY TenNganh";
$result_nganh = $conn->query($sql_nganh);
if ($result_nganh->num_rows > 0) {
    while($row = $result_nganh->fetch_assoc()) {
        $nganh_hoc_list[] = $row;
    }
}

// Gán dữ liệu cũ vào biến để điền lại form
$masv = $form_data['masv'] ?? '';
$hoten = $form_data['hoten'] ?? '';
$gioitinh = $form_data['gioitinh'] ?? '';
$ngaysinh = $form_data['ngaysinh'] ?? '';
$manganh = $form_data['manganh'] ?? '';
// Không điền lại password

include 'header.php';
?>

<h2>THÊM SINH VIÊN</h2>

<?php if (!empty($form_errors)): // KIỂM TRA LỖI TỪ SESSION ?>
    <div class="alert alert-danger">
        <strong>Lỗi!</strong> Vui lòng kiểm tra lại thông tin:
        <ul>
            <?php foreach ($form_errors as $error): // HIỂN THỊ LỖI TỪ SESSION ?>
                <li><?php echo htmlspecialchars($error); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form action="sinhvien_add_process.php" method="post" enctype="multipart/form-data" class="row g-3">
   <!-- Các input field giữ nguyên, nhưng value dùng các biến đã gán ở trên -->
   <div class="col-md-6">
        <label for="masv" class="form-label">Mã SV (*)</label>
        <input type="text" class="form-control <?php echo isset($form_errors['masv']) ? 'is-invalid' : ''; ?>" id="masv" name="masv" required maxlength="10" value="<?php echo htmlspecialchars($masv); ?>">
         <?php if (isset($form_errors['masv'])): ?>
            <div class="invalid-feedback"><?php echo htmlspecialchars($form_errors['masv']); ?></div>
        <?php endif; ?>
    </div>
     <div class="col-md-6">
        <label for="hoten" class="form-label">Họ Tên (*)</label>
        <input type="text" class="form-control <?php echo isset($form_errors['hoten']) ? 'is-invalid' : ''; ?>" id="hoten" name="hoten" required maxlength="50" value="<?php echo htmlspecialchars($hoten); ?>">
         <?php if (isset($form_errors['hoten'])): ?>
            <div class="invalid-feedback"><?php echo htmlspecialchars($form_errors['hoten']); ?></div>
        <?php endif; ?>
    </div>
    <div class="col-md-6">
        <label for="password" class="form-label">Password (*)</label>
        <input type="password" class="form-control <?php echo isset($form_errors['password']) ? 'is-invalid' : ''; ?>" id="password" name="password" required value="">
        <small class="form-text text-muted">Mật khẩu mặc định hoặc mới.</small>
         <?php if (isset($form_errors['password'])): ?>
            <div class="invalid-feedback"><?php echo htmlspecialchars($form_errors['password']); ?></div>
        <?php endif; ?>
    </div>
    <!-- Tương tự thêm class is-invalid và div invalid-feedback cho các trường khác nếu cần -->
     <div class="col-md-6">
        <label for="gioitinh" class="form-label">Giới Tính</label>
        <select id="gioitinh" name="gioitinh" class="form-select">
            <option value="" <?php echo ($gioitinh == '') ? 'selected' : ''; ?>>-- Chọn --</option>
            <option value="Nam" <?php echo ($gioitinh == 'Nam') ? 'selected' : ''; ?>>Nam</option>
            <option value="Nữ" <?php echo ($gioitinh == 'Nữ') ? 'selected' : ''; ?>>Nữ</option>
            <option value="Khác" <?php echo ($gioitinh == 'Khác') ? 'selected' : ''; ?>>Khác</option>
        </select>
    </div>
    <div class="col-md-6">
        <label for="ngaysinh" class="form-label">Ngày Sinh</label>
        <input type="date" class="form-control" id="ngaysinh" name="ngaysinh" value="<?php echo htmlspecialchars($ngaysinh); ?>">
    </div>
     <div class="col-md-6">
        <label for="manganh" class="form-label">Ngành Học (*)</label>
        <select id="manganh" name="manganh" class="form-select <?php echo isset($form_errors['manganh']) ? 'is-invalid' : ''; ?>" required>
            <option value="">-- Chọn Ngành --</option>
            <?php foreach ($nganh_hoc_list as $nganh): ?>
                <option value="<?php echo htmlspecialchars($nganh['MaNganh']); ?>" <?php echo ($manganh == $nganh['MaNganh']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($nganh['TenNganh']); ?>
                </option>
            <?php endforeach; ?>
        </select>
         <?php if (isset($form_errors['manganh'])): ?>
            <div class="invalid-feedback"><?php echo htmlspecialchars($form_errors['manganh']); ?></div>
        <?php endif; ?>
    </div>
    <div class="col-12">
        <label for="hinh" class="form-label">Hình Ảnh</label>
        <input class="form-control <?php echo isset($form_errors['hinh']) ? 'is-invalid' : ''; ?>" type="file" id="hinh" name="hinh" accept="image/*">
        <small class="form-text text-muted">Để trống nếu không muốn thêm/thay đổi hình.</small>
         <?php if (isset($form_errors['hinh'])): ?>
            <div class="invalid-feedback"><?php echo htmlspecialchars($form_errors['hinh']); ?></div>
        <?php endif; ?>
    </div>

    <div class="col-12">
        <button type="submit" class="btn btn-primary">Thêm Sinh Viên</button>
        <a href="student_list.php" class="btn btn-secondary">Hủy</a>
    </div>
</form>

<?php include 'footer.php'; ?>