<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

require_admin();

$page_title = 'Manage Reports - Public Complaint Management System';
$categories = ['Infrastructure', 'Cleanliness', 'Security', 'Public Service', 'Environment'];
$statuses = ['Pending', 'In Progress', 'Resolved', 'Rejected'];
$search = trim($_GET['search'] ?? '');
$category_filter = trim($_GET['category'] ?? '');
$status_filter = trim($_GET['status'] ?? '');
$reports = [];
$error_message = '';
$where_parts = [];
$params = [];
$types = '';

$sql = "SELECT r.id, r.title, r.category, r.status, r.image, r.created_at, u.name AS user_name
        FROM reports r
        INNER JOIN users u ON r.user_id = u.id";

if ($search !== '') {
    $where_parts[] = '(r.title LIKE ? OR r.category LIKE ? OR u.name LIKE ?)';
    $search_value = '%' . $search . '%';
    $params[] = $search_value;
    $params[] = $search_value;
    $params[] = $search_value;
    $types .= 'sss';
}

if ($category_filter !== '' && in_array($category_filter, $categories, true)) {
    $where_parts[] = 'r.category = ?';
    $params[] = $category_filter;
    $types .= 's';
}

if ($status_filter !== '' && in_array($status_filter, $statuses, true)) {
    $where_parts[] = 'r.status = ?';
    $params[] = $status_filter;
    $types .= 's';
}

if (count($where_parts) > 0) {
    $sql .= ' WHERE ' . implode(' AND ', $where_parts);
}

$sql .= ' ORDER BY r.created_at DESC';
$stmt = mysqli_prepare($conn, $sql);

if ($stmt) {
    if (count($params) > 0) {
        $bind_params = [$types];

        foreach ($params as $index => $value) {
            $bind_params[] = &$params[$index];
        }

        call_user_func_array([$stmt, 'bind_param'], $bind_params);
    }

    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_assoc($result)) {
        $reports[] = $row;
    }

    mysqli_stmt_close($stmt);
} else {
    error_log('Admin manage reports query prepare failed: ' . mysqli_error($conn));
    $error_message = 'Unable to load reports right now. Please try again later.';
}

include __DIR__ . '/../includes/header.php';
?>

<section class="page-section">
    <div class="container">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <h1 class="h3 mb-1">Manage Reports</h1>
                <p class="text-muted mb-0">View and manage all public complaint reports.</p>
            </div>
            <a href="dashboard.php" class="btn btn-outline-secondary">Dashboard</a>
        </div>

        <div class="card app-card mb-4">
            <div class="card-body p-4">
                <form method="GET" action="manage_reports.php" class="row g-3">
                    <div class="col-lg-5">
                        <label for="search" class="form-label">Search</label>
                        <input type="text" class="form-control" id="search" name="search" value="<?php echo sanitize_input($search); ?>" placeholder="Title, category, or user name">
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <label for="category" class="form-label">Category</label>
                        <select class="form-select" id="category" name="category">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo htmlspecialchars($category); ?>" <?php echo $category_filter === $category ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6 col-lg-2">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">All Statuses</option>
                            <?php foreach ($statuses as $status): ?>
                                <option value="<?php echo htmlspecialchars($status); ?>" <?php echo $status_filter === $status ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($status); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-lg-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card app-card">
            <div class="card-body p-0">
                <?php if ($error_message !== ''): ?>
                    <div class="p-4">
                        <div class="alert alert-danger mb-0" role="alert">
                            <?php echo htmlspecialchars($error_message); ?>
                        </div>
                    </div>
                <?php elseif (count($reports) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Image</th>
                                    <th>User Name</th>
                                    <th>Title</th>
                                    <th>Category</th>
                                    <th>Status</th>
                                    <th>Created Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($reports as $report): ?>
                                    <tr>
                                        <td><?php echo (int) $report['id']; ?></td>
                                        <td style="width: 100px;">
                                            <?php if (!empty($report['image'])): ?>
                                                <a href="/complaint-system/<?php echo htmlspecialchars($report['image']); ?>" target="_blank">
                                                    <img src="/complaint-system/<?php echo htmlspecialchars($report['image']); ?>" alt="Report image" class="img-thumbnail report-thumb">
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted small">No image</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($report['user_name']); ?></td>
                                        <td><?php echo htmlspecialchars($report['title']); ?></td>
                                        <td><?php echo htmlspecialchars($report['category']); ?></td>
                                        <td>
                                            <span class="badge status-badge <?php echo get_status_badge_class($report['status']); ?>">
                                                <?php echo htmlspecialchars($report['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars(format_datetime($report['created_at'])); ?></td>
                                        <td>
                                            <a href="report_details.php?id=<?php echo (int) $report['id']; ?>" class="btn btn-sm btn-outline-primary">View</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state text-center">
                        <h2 class="h5 mb-2">No reports found</h2>
                        <p class="text-muted mb-0">Try adjusting the search text, category, or status filter.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
