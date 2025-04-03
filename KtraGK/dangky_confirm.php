<?php
require 'db_connect.php';
include 'auth_check.php';

$cart_items = $_SESSION['cart'] ?? [];
if (empty($cart_items)) {
    $_SESSION['message'] = "Giỏ đăng ký trống, không thể xác nhận.";
    $_SESSION['message_type'] = "warning";
    header("Location: cart_view.php");
    exit();
}

// Get logged-in student details
$masv_logged_in = $_SESSION['masv'];
$stmt_sv = $conn->prepare("SELECT HoTen, NgaySinh, MaNganh FROM SinhVien WHERE MaSV = ?");
$stmt_sv->bind_param("s", $masv_logged_in);
$stmt_sv->execute();
$result_sv = $stmt_sv->get_result();
$student = $result_sv->fetch_assoc();
$stmt_sv->close();

$total_credits = 0;
$total_courses = count($cart_items);

include 'header.php';
?>

<h2>XÁC NHẬN THÔNG TIN ĐĂNG KÝ</h2>

<div class="card mb-4">
    <div class="card-header">Thông tin Sinh viên</div>
    <div class="card-body">
        <dl class="row">
            <dt class="col-sm-3">Mã số sinh viên:</dt>
            <dd class="col-sm-9"><?php echo htmlspecialchars($masv_logged_in); ?></dd>

            <dt class="col-sm-3">Họ Tên Sinh viên:</dt>
            <dd class="col-sm-9"><?php echo htmlspecialchars($student['HoTen']); ?></dd>

            <dt class="col-sm-3">Ngày Sinh:</dt>
            <dd class="col-sm-9"><?php echo ($student['NgaySinh'] ? date("d/m/Y", strtotime($student['NgaySinh'])) : 'N/A'); ?></dd>

             <dt class="col-sm-3">Ngành học:</dt>
             <dd class="col-sm-9"><?php echo htmlspecialchars($student['MaNganh']); // Could join NganhHoc table here too ?></dd>

             <dt class="col-sm-3">Ngày Đăng Ký:</dt>
             <dd class="col-sm-9"><?php echo date("d/m/Y"); // Current date ?></dd>
        </dl>
    </div>
</div>


<div class="card">
     <div class="card-header">Học phần đăng ký</div>
     <div class="card-body">
         <table class="table table-sm table-bordered">
            <thead class="table-light">
                <tr>
                    <th>Mã HP</th>
                    <th>Tên Học Phần</th>
                    <th class="text-center">Số Tín Chỉ</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cart_items as $mahp => $item):
                    $total_credits += $item['SoTinChi'];
                ?>
                    <tr>
                        <td><?php echo htmlspecialchars($mahp); ?></td>
                        <td><?php echo htmlspecialchars($item['TenHP']); ?></td>
                        <td class="text-center"><?php echo htmlspecialchars($item['SoTinChi']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
             <tfoot>
                <tr>
                    <td colspan="2" class="text-end"><strong>Tổng số học phần:</strong></td>
                    <td class="text-center"><strong><?php echo $total_courses; ?></strong></td>
                </tr>
                 <tr>
                    <td colspan="2" class="text-end"><strong>Tổng số tín chỉ:</strong></td>
                    <td class="text-center"><strong><?php echo $total_credits; ?></strong></td>
                </tr>
            </tfoot>
         </table>

        <div class="mt-3 text-center">
             <form action="dangky_process.php" method="post">
                <!-- Optional: Add CSRF token here for better security -->
                <button type="submit" name="confirm_registration" class="btn btn-success btn-lg">Xác Nhận & Lưu Đăng Ký</button>
                <a href="cart_view.php" class="btn btn-secondary">Quay lại Giỏ hàng</a>
             </form>
        </div>
     </div>
</div>


<?php include 'footer.php'; ?>