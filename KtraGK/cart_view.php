<?php
require 'db_connect.php';
include 'auth_check.php';
include 'header.php';

$cart_items = $_SESSION['cart'] ?? [];
$total_credits = 0;
$total_courses = count($cart_items);

?>

<h2>ĐĂNG KÍ HỌC PHẦN (GIỎ HÀNG)</h2>

<?php if ($total_courses > 0): ?>
    <table class="table table-bordered table-striped">
        <thead class="table-light">
            <tr>
                <th>Mã HP</th>
                <th>Tên Học Phần</th>
                <th class="text-center">Số Tín Chỉ</th>
                <th class="text-center">Hành động</th>
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
                    <td class="text-center">
                        <a href="cart_action.php?action=remove&mahp=<?php echo htmlspecialchars($mahp); ?>" class="btn btn-danger btn-sm" title="Xóa">Xóa</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2" class="text-end"><strong>Tổng số học phần:</strong></td>
                <td class="text-center"><strong><?php echo $total_courses; ?></strong></td>
                <td></td>
            </tr>
             <tr>
                <td colspan="2" class="text-end"><strong>Tổng số tín chỉ:</strong></td>
                <td class="text-center"><strong><?php echo $total_credits; ?></strong></td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <div class="mt-3 d-flex justify-content-between">
    <a href="cart_action.php?action=clear" class="btn btn-outline-danger" 
    onclick="return confirmClearCart();">Xóa Giỏ Hàng</a>
        <a href="dangky_confirm.php" class="btn btn-primary">Xác Nhận Đăng Ký</a> <!-- Link to confirmation page -->
    </div>
    <script>
function confirmClearCart() {
    let error = new URLSearchParams(window.location.search).get("error");
    if (error === "invalid_subject") {
        alert("Mã học phần không hợp lệ.");
        return false;
    }
    return confirm('Bạn có chắc muốn xóa toàn bộ giỏ đăng ký?');
}
</script>

<?php else: ?>
    <div class="alert alert-info">Giỏ đăng ký của bạn đang trống. Vui lòng chọn học phần từ <a href="hocphan_list.php">danh sách học phần</a>.</div>
<?php endif; ?>



<?php include 'footer.php'; ?>