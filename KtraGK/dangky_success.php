<?php
require 'db_connect.php';
include 'auth_check.php';

$madk = $_GET['madk'] ?? null;

if (!$madk || !is_numeric($madk)) {
     $_SESSION['message'] = "Mã đăng ký không hợp lệ.";
     $_SESSION['message_type'] = "warning";
     header("Location: hocphan_list.php"); // Or student dashboard
     exit();
}

// Fetch Registration Details (DangKy + SinhVien)
$sql_dk = "SELECT dk.MaDK, dk.NgayDK, dk.MaSV, sv.HoTen
           FROM DangKy dk
           JOIN SinhVien sv ON dk.MaSV = sv.MaSV
           WHERE dk.MaDK = ? AND dk.MaSV = ?"; // Ensure it belongs to logged in user
$stmt_dk = $conn->prepare($sql_dk);
$stmt_dk->bind_param("is", $madk, $_SESSION['masv']);
$stmt_dk->execute();
$result_dk = $stmt_dk->get_result();

if ($result_dk->num_rows !== 1) {
    $_SESSION['message'] = "Không tìm thấy thông tin đăng ký hoặc bạn không có quyền xem.";
    $_SESSION['message_type'] = "danger";
    header("Location: hocphan_list.php");
    exit();
}
$dangky_info = $result_dk->fetch_assoc();
$stmt_dk->close();


// Fetch Registered Courses (ChiTietDangKy + HocPhan)
$sql_ct = "SELECT ctdk.MaHP, hp.TenHP, hp.SoTinChi
           FROM ChiTietDangKy ctdk
           JOIN HocPhan hp ON ctdk.MaHP = hp.MaHP
           WHERE ctdk.MaDK = ?";
$stmt_ct = $conn->prepare($sql_ct);
$stmt_ct->bind_param("i", $madk);
$stmt_ct->execute();
$result_ct = $stmt_ct->get_result();
$registered_courses = [];
if ($result_ct->num_rows > 0) {
    while($row = $result_ct->fetch_assoc()) {
        $registered_courses[] = $row;
    }
}
$stmt_ct->close();


include 'header.php';
?>

<h2>THÔNG TIN HỌC PHẦN ĐÃ LƯU</h2>

<div class="alert alert-success">Đăng ký học phần thành công!</div>

<p><a href="hocphan_list.php" class="btn btn-link">« Về trang chủ</a></p>

<h4>Kết quả sau khi đăng ký học phần:</h4>

<div class="card mb-4">
     <div class="card-header">Thông tin Đăng Ký Chung</div>
     <div class="card-body">
         <table class="table table-sm">
            <tr>
                <th style="width: 20%;">Mã Đăng Ký (MaDK):</th>
                <td><?php echo htmlspecialchars($dangky_info['MaDK']); ?></td>
            </tr>
             <tr>
                <th>Ngày Đăng Ký:</th>
                <td><?php echo date("d/m/Y", strtotime($dangky_info['NgayDK'])); ?></td>
            </tr>
             <tr>
                <th>Mã Sinh Viên:</th>
                <td><?php echo htmlspecialchars($dangky_info['MaSV']); ?></td>
            </tr>
             <tr>
                <th>Họ Tên Sinh Viên:</th>
                <td><?php echo htmlspecialchars($dangky_info['HoTen']); ?></td>
            </tr>
         </table>
    </div>
</div>


<div class="card">
    <div class="card-header">Chi Tiết Học Phần Đã Đăng Ký</div>
    <div class="card-body p-0"> <!-- Remove padding for table flush -->
         <table class="table table-striped table-bordered mb-0">
             <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Mã HP</th>
                    <th>Tên Học Phần</th>
                    <th class="text-center">Số Tín Chỉ</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($registered_courses)):
                    $count = 1;
                    $total_credits = 0;
                    foreach ($registered_courses as $course):
                         $total_credits += $course['SoTinChi'];
                ?>
                    <tr>
                        <td><?php echo $count++; ?></td>
                        <td><?php echo htmlspecialchars($course['MaHP']); ?></td>
                        <td><?php echo htmlspecialchars($course['TenHP']); ?></td>
                        <td class="text-center"><?php echo htmlspecialchars($course['SoTinChi']); ?></td>
                    </tr>
                <?php endforeach; ?>
                 <tr class="fw-bold">
                     <td colspan="3" class="text-end">Tổng số tín chỉ đăng ký:</td>
                     <td class="text-center"><?php echo $total_credits; ?></td>
                 </tr>
                <?php else: ?>
                    <tr><td colspan="4" class="text-center">Không có chi tiết học phần nào được tìm thấy cho đăng ký này.</td></tr>
                <?php endif; ?>
            </tbody>
         </table>
    </div>
</div>


<?php include 'footer.php'; ?>