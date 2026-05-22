<?php
require_once __DIR__ . '/functions.php';
start_secure_session();

$page_title = $page_title ?? 'Public Complaint Management System';
$base_url = '/complaint-system/';
$current_path = $_SERVER['SCRIPT_NAME'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo $base_url; ?>assets/css/style.css" rel="stylesheet">
    <?php echo $extra_head ?? ''; ?>
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-semibold" href="<?php echo $base_url; ?>index.php">Complaint System</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#main_navbar" aria-controls="main_navbar" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="main_navbar">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link <?php echo str_ends_with($current_path, '/index.php') ? 'active' : ''; ?>" href="<?php echo $base_url; ?>index.php">Home</a>
                    </li>
                    <?php if (is_logged_in()): ?>
                        <?php if (is_admin()): ?>
                            <li class="nav-item">
                                <a class="nav-link <?php echo str_contains($current_path, '/admin/dashboard.php') ? 'active' : ''; ?>" href="<?php echo $base_url; ?>admin/dashboard.php">Dashboard</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo str_contains($current_path, '/admin/manage_reports.php') || str_contains($current_path, '/admin/report_details.php') ? 'active' : ''; ?>" href="<?php echo $base_url; ?>admin/manage_reports.php">Manage Reports</a>
                            </li>
                        <?php else: ?>
                            <li class="nav-item">
                                <a class="nav-link <?php echo str_contains($current_path, '/submit_report.php') ? 'active' : ''; ?>" href="<?php echo $base_url; ?>submit_report.php">Submit Report</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo str_contains($current_path, '/my_reports.php') || str_contains($current_path, '/report_details.php') ? 'active' : ''; ?>" href="<?php echo $base_url; ?>my_reports.php">My Reports</a>
                            </li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo str_contains($current_path, '/profile.php') ? 'active' : ''; ?>" href="<?php echo $base_url; ?>profile.php">Profile</a>
                        </li>
                        <li class="nav-item">
                            <span class="navbar-text text-white me-lg-3">
                                <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'User'); ?>
                            </span>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $base_url; ?>logout.php">Logout</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo str_contains($current_path, '/login.php') ? 'active' : ''; ?>" href="<?php echo $base_url; ?>login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo str_contains($current_path, '/register.php') ? 'active' : ''; ?>" href="<?php echo $base_url; ?>register.php">Register</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <main>
