<?php
require 'db_connect.php';
include 'auth_check.php'; // Redirect to login if not logged in
include 'header.php';

// --- Pagination Logic ---
$results_per_page = 4; // Số sinh viên mỗi trang (Câu 1f)
$sql_total = "SELECT COUNT(*) AS total FROM SinhVien";
$result_total = $conn->query($sql_total);
$row_total = $result_total->fetch_assoc();
$total_results = $row_total['total'];
$total_pages = ceil($total_results / $results_per_page);

// Determine current page number
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
if ($page > $total_pages && $total_pages > 0) $page = $total_pages;

// Calculate the starting limit for results
$start_limit = ($page - 1) * $results_per_page;
// --- End Pagination Logic ---


// Fetch students for the current page
$sql = "SELECT sv.MaSV, sv.HoTen, sv.GioiTinh, sv.NgaySinh, sv.Hinh, nh.TenNganh
        FROM SinhVien sv
        LEFT JOIN NganhHoc nh ON sv.MaNganh = nh.MaNganh
        ORDER BY sv.MaSV
        LIMIT ?, ?";

$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
}
$stmt->bind_param("ii", $start_limit, $results_per_page);
$stmt->execute();
$result = $stmt->get_result();

?>

<h2>TRANG DANH SÁCH SINH VIÊN</h2>
<p><a href="student_add.php" class="btn btn-success btn-sm">Add Student</a></p>

<table class="table table-bordered table-striped table-hover align-middle">
    <thead class="table-light">
        <tr>
            <th>Mã SV</th>
            <th>Họ Tên</th>
            <th>Giới Tính</th>
            <th>Ngày Sinh</th>
            <th>Hình</th>
            <th>Ngành Học</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['MaSV']); ?></td>
                    <td><?php echo htmlspecialchars($row['HoTen']); ?></td>
                    <td><?php echo htmlspecialchars($row['GioiTinh']); ?></td>
                    <td><?php echo ($row['NgaySinh'] ? date("d/m/Y", strtotime($row['NgaySinh'])) : ''); ?></td>
                    <td>
                        <?php if (!empty($row['Hinh'])): // Kiểm tra xem cột Hinh có giá trị không ?>
                            <img src="/KtraGK/uploads/<?php echo htmlspecialchars(basename($row['Hinh'])); ?>" alt="Hình SV" class="student-img-list">

                        <?php else: // Nếu cột Hinh rỗng ?>
                            (No image)
                        <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($row['TenNganh']); ?></td>
                    <td class="action-links">
                        <a href="student_edit.php?id=<?php echo htmlspecialchars($row['MaSV']); ?>" class="btn btn-primary btn-sm">Edit</a> |
                        <a href="student_details.php?id=<?php echo htmlspecialchars($row['MaSV']); ?>" class="btn btn-info btn-sm">Details</a> |
                        <a href="student_delete.php?id=<?php echo htmlspecialchars($row['MaSV']); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this student?');">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="7" class="text-center">Không có sinh viên nào.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<?php $stmt->close(); ?>

<!-- Pagination Links -->
<?php if ($total_pages > 1): ?>
<nav aria-label="Page navigation">
  <ul class="pagination justify-content-center">
    <!-- Previous Button -->
    <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
      <a class="page-link" href="?page=<?php echo $page - 1; ?>">Previous</a>
    </li>

    <!-- Page Number Links -->
    <?php for($i = 1; $i <= $total_pages; $i++): ?>
    <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
        <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
    </li>
    <?php endfor; ?>

    <!-- Next Button -->
     <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
      <a class="page-link" href="?page=<?php echo $page + 1; ?>">Next</a>
    </li>
  </ul>
</nav>
<?php endif; ?>


<?php include 'footer.php'; ?>