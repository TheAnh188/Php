<?php
require 'db_connect.php';
include 'auth_check.php';

$masv_details = $_GET['id'] ?? null;
if (!$masv_details) {
    $_SESSION['message'] = "Mã sinh viên không hợp lệ.";
    $_SESSION['message_type'] = "warning";
    header("Location: student_list.php");
    exit();
}

// Fetch student data including NganhHoc TenNganh
$sql = "SELECT sv.*, nh.TenNganh
        FROM SinhVien sv
        LEFT JOIN NganhHoc nh ON sv.MaNganh = nh.MaNganh
        WHERE sv.MaSV = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $masv_details);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    $_SESSION['message'] = "Không tìm thấy sinh viên với mã: " . htmlspecialchars($masv_details);
    $_SESSION['message_type'] = "danger";
    header("Location: student_list.php");
    exit();
}
$student = $result->fetch_assoc();
$stmt->close();

include 'header.php';
?>

<h2>Thông tin chi tiết sinh viên</h2>

<div class="card">
    <div class="row g-0">
        <div class="col-md-4 text-center p-3">
            <?php if (!empty($student['Hinh']) && file_exists(ltrim($student['Hinh'], '/'))): ?>
                <img src="<?php echo htmlspecialchars($student['Hinh']); ?>" alt="Hình SV" class="student-img-details img-thumbnail mb-3">
            <?php else: ?>
                <div class="border p-5 text-muted mb-3">(No image)</div>
            <?php endif; ?>
        </div>
        <div class="col-md-8">
            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-4">Mã Sinh Viên:</dt>
                    <dd class="col-sm-8"><?php echo htmlspecialchars($student['MaSV']); ?></dd>

                    <dt class="col-sm-4">Họ Tên:</dt>
                    <dd class="col-sm-8"><?php echo htmlspecialchars($student['HoTen']); ?></dd>

                    <dt class="col-sm-4">Giới Tính:</dt>
                    <dd class="col-sm-8"><?php echo htmlspecialchars($student['GioiTinh'] ?? 'N/A'); ?></dd>

                    <dt class="col-sm-4">Ngày Sinh:</dt>
                    <dd class="col-sm-8"><?php echo ($student['NgaySinh'] ? date("d/m/Y", strtotime($student['NgaySinh'])) : 'N/A'); ?></dd>

                    <dt class="col-sm-4">Ngành Học:</dt>
                    <dd class="col-sm-8"><?php echo htmlspecialchars($student['TenNganh'] ?? 'N/A'); ?> (<?php echo htmlspecialchars($student['MaNganh']); ?>)</dd>
                </dl>
                 <a href="student_edit.php?id=<?php echo htmlspecialchars($student['MaSV']); ?>" class="btn btn-primary">Edit</a>
                 <a href="student_list.php" class="btn btn-secondary">Back to List</a>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>