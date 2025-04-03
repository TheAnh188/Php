<footer class="mt-5 text-center text-muted">
    <p>© <?php echo date("Y"); ?> Quản Lý Sinh Viên</p>
</footer>

<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
// Close the database connection if it was opened
if (isset($conn) && $conn instanceof mysqli) {
    $conn->close();
}
?>