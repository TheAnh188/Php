<?php
require 'db_connect.php';
include 'auth_check.php'; // Redirect to login if not logged in
include 'header.php';

// Fetch all courses
$sql = "SELECT MaHP, TenHP, SoTinChi, SoLuong FROM HocPhan ORDER BY MaHP";
$result = $conn->query($sql);

?>

<h2>DANH SÁCH HỌC PHẦN</h2>

<table class="table table-bordered table-striped table-hover">
    <thead class="table-light">
        <tr>
            <th>Mã Học Phần</th>
            <th>Tên Học Phần</th>
            <th>Số Tín Chỉ</th>
            <th>Số Lượng Dự Kiến</th> <!-- Câu 6 -->
            <th>Đăng Ký</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()):
                 $is_in_cart = isset($_SESSION['cart'][$row['MaHP']]);
                 $can_register = $row['SoLuong'] > 0;
            ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['MaHP']); ?></td>
                    <td><?php echo htmlspecialchars($row['TenHP']); ?></td>
                    <td class="text-center"><?php echo htmlspecialchars($row['SoTinChi']); ?></td>
                    <td class="text-center"><?php echo htmlspecialchars($row['SoLuong']); ?></td>
                    <td class="text-center">
                        <?php if ($is_in_cart): ?>
                             <span class="badge bg-secondary">Đã thêm</span>
                        <?php elseif (!$can_register): ?>
                             <span class="badge bg-danger">Hết chỗ</span>
                        <?php else: ?>
                            <a href="cart_action.php?action=add&mahp=<?php echo htmlspecialchars($row['MaHP']); ?>" class="btn btn-success btn-sm">Đăng Ký</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="5" class="text-center">Chưa có học phần nào.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<?php include 'footer.php'; ?>