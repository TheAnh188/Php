<?php
require 'db_connect.php';
include 'auth_check.php';

$masv_edit = $_GET['id'] ?? null;
if (!$masv_edit) {
    $_SESSION['message'] = "Mã sinh viên không hợp lệ.";
    $_SESSION['message_type'] = "warning";
    header("Location: student_list.php");
    exit();
}

// Fetch current student data
$stmt = $conn->prepare("SELECT * FROM SinhVien WHERE MaSV = ?");
$stmt->bind_param("s", $masv_edit);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows !== 1) {
    $_SESSION['message'] = "Không tìm thấy sinh viên với mã: " . htmlspecialchars($masv_edit);
    $_SESSION['message_type'] = "danger";
    header("Location: student_list.php");
    exit();
}
$student = $result->fetch_assoc();
$stmt->close();

// Fetch Nganh Hoc for dropdown
$nganh_hoc_list = [];
$sql_nganh = "SELECT MaNganh, TenNganh FROM NganhHoc ORDER BY TenNganh";
$result_nganh = $conn->query($sql_nganh);
if ($result_nganh->num_rows > 0) {
    while($row = $result_nganh->fetch_assoc()) {
        $nganh_hoc_list[] = $row;
    }
}

// Process form submission in sinhvien_edit_process.php
include 'header.php';
?>

<h2>HIỆU CHỈNH THÔNG TIN SINH VIÊN</h2>

<form action="student_edit_process.php" method="post" enctype="multipart/form-data" class="row g-3">
    <!-- Hidden field to pass the original MaSV -->
    <input type="hidden" name="masv_original" value="<?php echo htmlspecialchars($student['MaSV']); ?>">

    <div class="col-md-6">
        <label for="masv" class="form-label">Mã SV</label>
        <input type="text" class="form-control" id="masv" name="masv" required maxlength="10" value="<?php echo htmlspecialchars($student['MaSV']); ?>" readonly>
         <small class="text-muted">Mã sinh viên không thể thay đổi.</small>
    </div>
    <div class="col-md-6">
        <label for="hoten" class="form-label">Họ Tên (*)</label>
        <input type="text" class="form-control" id="hoten" name="hoten" required maxlength="50" value="<?php echo htmlspecialchars($student['HoTen']); ?>">
    </div>
     <div class="col-md-6">
        <label for="password" class="form-label">Password</label>
        <input type="password" class="form-control" id="password" name="password" value="">
        <small class="form-text text-muted">Để trống nếu không muốn thay đổi mật khẩu.</small>
    </div>
    <div class="col-md-6">
        <label for="gioitinh" class="form-label">Giới Tính</label>
        <select id="gioitinh" name="gioitinh" class="form-select">
            <option value="" <?php echo ($student['GioiTinh'] == '') ? 'selected' : ''; ?>>-- Chọn --</option>
            <option value="Nam" <?php echo ($student['GioiTinh'] == 'Nam') ? 'selected' : ''; ?>>Nam</option>
            <option value="Nữ" <?php echo ($student['GioiTinh'] == 'Nữ') ? 'selected' : ''; ?>>Nữ</option>
             <option value="Khác" <?php echo ($student['GioiTinh'] == 'Khác') ? 'selected' : ''; ?>>Khác</option>
        </select>
    </div>
    <div class="col-md-6">
        <label for="ngaysinh" class="form-label">Ngày Sinh</label>
        <input type="date" class="form-control" id="ngaysinh" name="ngaysinh" value="<?php echo htmlspecialchars($student['NgaySinh'] ?? ''); ?>">
    </div>
     <div class="col-md-6">
        <label for="manganh" class="form-label">Ngành Học (*)</label>
        <select id="manganh" name="manganh" class="form-select" required>
            <option value="">-- Chọn Ngành --</option>
            <?php foreach ($nganh_hoc_list as $nganh): ?>
                <option value="<?php echo htmlspecialchars($nganh['MaNganh']); ?>" <?php echo ($student['MaNganh'] == $nganh['MaNganh']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($nganh['TenNganh']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
     <div class="col-12">
        <label for="hinh" class="form-label">Hình Ảnh Mới</label>
        <input class="form-control" type="file" id="hinh" name="hinh" accept="image/*">
         <small class="form-text text-muted">Chọn file mới để thay đổi hình ảnh hiện tại.</small>
         <?php if (!empty($student['Hinh']) && file_exists(ltrim($student['Hinh'], '/'))): ?>
             <div class="mt-2">
                 <p>Hình hiện tại:</p>
                 <img src="<?php echo htmlspecialchars($student['Hinh']); ?>" alt="Hình SV" class="student-img-details img-thumbnail">
                 <input type="hidden" name="current_image" value="<?php echo htmlspecialchars($student['Hinh']); ?>">
             </div>
         <?php endif; ?>
    </div>

    <div class="col-12">
        <button type="submit" class="btn btn-primary">Lưu Thay Đổi</button>
        <a href="student_list.php" class="btn btn-secondary">Hủy</a>
    </div>
</form>

<?php include 'footer.php'; ?>