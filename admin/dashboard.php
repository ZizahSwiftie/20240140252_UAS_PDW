<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

require_admin();

$stats = [
    'total_reports' => 0,
    'pending_reports' => 0,
    'resolved_reports' => 0,
    'rejected_reports' => 0
];
$dashboard_error = '';

$count_sql = "SELECT
        COUNT(*) AS total_reports,
        COALESCE(SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END), 0) AS pending_reports,
        COALESCE(SUM(CASE WHEN status = 'Resolved' THEN 1 ELSE 0 END), 0) AS resolved_reports,
        COALESCE(SUM(CASE WHEN status = 'Rejected' THEN 1 ELSE 0 END), 0) AS rejected_reports
    FROM reports";
$count_result = mysqli_query($conn, $count_sql);

if ($count_result) {
    $stats = mysqli_fetch_assoc($count_result);
} else {
    error_log('Admin dashboard count query failed: ' . mysqli_error($conn));
    $dashboard_error = 'Dashboard statistics are temporarily unavailable.';
}

$page_title = 'Admin Dashboard - Public Complaint Management System';
include __DIR__ . '/../includes/header.php';
?>

<section class="page-section">
    <div class="container">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <h1 class="h3 mb-1">Admin Dashboard</h1>
                <p class="text-muted mb-0">Overview of public complaint reports.</p>
            </div>
            <a href="manage_reports.php" class="btn btn-primary">Manage Reports</a>
        </div>

        <?php if ($dashboard_error !== ''): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo htmlspecialchars($dashboard_error); ?>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <div class="col-sm-6 col-xl-3">
                <div class="card app-card stat-card h-100">
                    <div class="card-body">
                        <p class="text-muted mb-1">Total Reports</p>
                        <h2 class="display-6 fw-bold mb-0"><?php echo (int) $stats['total_reports']; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card app-card stat-card h-100">
                    <div class="card-body">
                        <p class="text-muted mb-1">Pending Reports</p>
                        <h2 class="display-6 fw-bold mb-0 text-secondary"><?php echo (int) $stats['pending_reports']; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card app-card stat-card h-100">
                    <div class="card-body">
                        <p class="text-muted mb-1">Resolved Reports</p>
                        <h2 class="display-6 fw-bold mb-0 text-success"><?php echo (int) $stats['resolved_reports']; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card app-card stat-card h-100">
                    <div class="card-body">
                        <p class="text-muted mb-1">Rejected Reports</p>
                        <h2 class="display-6 fw-bold mb-0 text-danger"><?php echo (int) $stats['rejected_reports']; ?></h2>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
