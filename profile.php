<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

require_login();

$page_title = 'Profile - Public Complaint Management System';
$user_id = (int) $_SESSION['user_id'];
$profile_errors = [];
$password_errors = [];
$profile_success = '';
$password_success = '';
$user = [
    'name' => '',
    'email' => '',
    'password' => '',
    'role' => '',
    'created_at' => ''
];

function load_profile_user($conn, $user_id)
{
    $sql = 'SELECT name, email, password, role, created_at FROM users WHERE id = ? LIMIT 1';
    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        error_log('Profile load prepare failed: ' . mysqli_error($conn));
        return null;
    }

    mysqli_stmt_bind_param($stmt, 'i', $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    return $user;
}

$loaded_user = load_profile_user($conn, $user_id);

if (!$loaded_user) {
    redirect('/complaint-system/logout.php');
}

$user = $loaded_user;
$name = $user['name'];
$email = $user['email'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form_type = $_POST['form_type'] ?? '';

    if ($form_type === 'profile') {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');

        if ($name === '') {
            $profile_errors['name'] = 'Full name is required.';
        }

        if ($email === '') {
            $profile_errors['email'] = 'Email is required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $profile_errors['email'] = 'Please enter a valid email address.';
        }

        if (empty($profile_errors)) {
            $check_sql = 'SELECT id FROM users WHERE email = ? AND id <> ? LIMIT 1';
            $check_stmt = mysqli_prepare($conn, $check_sql);

            if ($check_stmt) {
                mysqli_stmt_bind_param($check_stmt, 'si', $email, $user_id);
                mysqli_stmt_execute($check_stmt);
                mysqli_stmt_store_result($check_stmt);

                if (mysqli_stmt_num_rows($check_stmt) > 0) {
                    $profile_errors['email'] = 'This email is already used by another account.';
                }

                mysqli_stmt_close($check_stmt);
            } else {
                error_log('Profile duplicate email prepare failed: ' . mysqli_error($conn));
                $profile_errors['general'] = 'Unable to validate email right now.';
            }
        }

        if (empty($profile_errors)) {
            $update_sql = 'UPDATE users SET name = ?, email = ? WHERE id = ? LIMIT 1';
            $update_stmt = mysqli_prepare($conn, $update_sql);

            if ($update_stmt) {
                mysqli_stmt_bind_param($update_stmt, 'ssi', $name, $email, $user_id);

                if (mysqli_stmt_execute($update_stmt)) {
                    $_SESSION['user_name'] = $name;
                    $profile_success = 'Profile information updated successfully.';
                    $user['name'] = $name;
                    $user['email'] = $email;
                } else {
                    error_log('Profile update failed: ' . mysqli_stmt_error($update_stmt));
                    $profile_errors['general'] = 'Profile update failed. Please try again.';
                }

                mysqli_stmt_close($update_stmt);
            } else {
                error_log('Profile update prepare failed: ' . mysqli_error($conn));
                $profile_errors['general'] = 'Profile update is temporarily unavailable.';
            }
        }
    }

    if ($form_type === 'password') {
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_new_password = $_POST['confirm_new_password'] ?? '';

        if ($current_password === '') {
            $password_errors['current_password'] = 'Current password is required.';
        } elseif (!password_verify($current_password, $user['password'])) {
            $password_errors['current_password'] = 'Current password is incorrect.';
        }

        if ($new_password === '') {
            $password_errors['new_password'] = 'New password is required.';
        } elseif (strlen($new_password) < 6) {
            $password_errors['new_password'] = 'New password must be at least 6 characters.';
        }

        if ($confirm_new_password === '') {
            $password_errors['confirm_new_password'] = 'Please confirm the new password.';
        } elseif ($new_password !== $confirm_new_password) {
            $password_errors['confirm_new_password'] = 'Password confirmation does not match.';
        }

        if (empty($password_errors)) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $password_sql = 'UPDATE users SET password = ? WHERE id = ? LIMIT 1';
            $password_stmt = mysqli_prepare($conn, $password_sql);

            if ($password_stmt) {
                mysqli_stmt_bind_param($password_stmt, 'si', $hashed_password, $user_id);

                if (mysqli_stmt_execute($password_stmt)) {
                    $password_success = 'Password changed successfully.';
                    $user['password'] = $hashed_password;
                } else {
                    error_log('Password update failed: ' . mysqli_stmt_error($password_stmt));
                    $password_errors['general'] = 'Password update failed. Please try again.';
                }

                mysqli_stmt_close($password_stmt);
            } else {
                error_log('Password update prepare failed: ' . mysqli_error($conn));
                $password_errors['general'] = 'Password update is temporarily unavailable.';
            }
        }
    }
}

include 'includes/header.php';
?>

<section class="page-section">
    <div class="container">
        <div class="page-heading">
            <h1 class="h3 mb-1">Profile</h1>
            <p class="text-muted mb-0">Manage your account details and password.</p>
        </div>

        <div class="row g-4">
            <div class="col-lg-4">
                <div class="card app-card h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center gap-3 mb-4">
                            <div class="profile-initial">
                                <?php echo htmlspecialchars(strtoupper(substr($user['name'], 0, 1))); ?>
                            </div>
                            <div>
                                <h2 class="h5 mb-1"><?php echo htmlspecialchars($user['name']); ?></h2>
                                <p class="text-muted small mb-0"><?php echo htmlspecialchars($user['email']); ?></p>
                            </div>
                        </div>

                        <dl class="mb-0">
                            <dt class="text-muted small">Role</dt>
                            <dd class="mb-3"><?php echo htmlspecialchars(ucfirst($user['role'])); ?></dd>

                            <dt class="text-muted small">Member Since</dt>
                            <dd class="mb-0"><?php echo htmlspecialchars(format_date($user['created_at'])); ?></dd>
                        </dl>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card app-card mb-4">
                    <div class="card-body p-4">
                        <h2 class="h5 mb-3">Account Information</h2>

                        <?php if ($profile_success !== ''): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <?php echo htmlspecialchars($profile_success); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($profile_errors['general'])): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <?php echo htmlspecialchars($profile_errors['general']); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="profile.php" novalidate>
                            <input type="hidden" name="form_type" value="profile">

                            <div class="mb-3">
                                <label for="name" class="form-label">Full Name</label>
                                <input type="text" class="form-control <?php echo isset($profile_errors['name']) ? 'is-invalid' : ''; ?>" id="name" name="name" value="<?php echo sanitize_input($name); ?>" required>
                                <?php if (isset($profile_errors['name'])): ?>
                                    <div class="invalid-feedback"><?php echo htmlspecialchars($profile_errors['name']); ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="mb-4">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control <?php echo isset($profile_errors['email']) ? 'is-invalid' : ''; ?>" id="email" name="email" value="<?php echo sanitize_input($email); ?>" required>
                                <?php if (isset($profile_errors['email'])): ?>
                                    <div class="invalid-feedback"><?php echo htmlspecialchars($profile_errors['email']); ?></div>
                                <?php endif; ?>
                            </div>

                            <button type="submit" class="btn btn-primary">Save Profile</button>
                        </form>
                    </div>
                </div>

                <div class="card app-card">
                    <div class="card-body p-4">
                        <h2 class="h5 mb-3">Change Password</h2>

                        <?php if ($password_success !== ''): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <?php echo htmlspecialchars($password_success); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($password_errors['general'])): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <?php echo htmlspecialchars($password_errors['general']); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="profile.php" novalidate>
                            <input type="hidden" name="form_type" value="password">

                            <div class="mb-3">
                                <label for="current_password" class="form-label">Current Password</label>
                                <input type="password" class="form-control <?php echo isset($password_errors['current_password']) ? 'is-invalid' : ''; ?>" id="current_password" name="current_password" required>
                                <?php if (isset($password_errors['current_password'])): ?>
                                    <div class="invalid-feedback"><?php echo htmlspecialchars($password_errors['current_password']); ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="new_password" class="form-label">New Password</label>
                                    <input type="password" class="form-control <?php echo isset($password_errors['new_password']) ? 'is-invalid' : ''; ?>" id="new_password" name="new_password" required>
                                    <?php if (isset($password_errors['new_password'])): ?>
                                        <div class="invalid-feedback"><?php echo htmlspecialchars($password_errors['new_password']); ?></div>
                                    <?php endif; ?>
                                </div>

                                <div class="col-md-6 mb-4">
                                    <label for="confirm_new_password" class="form-label">Confirm New Password</label>
                                    <input type="password" class="form-control <?php echo isset($password_errors['confirm_new_password']) ? 'is-invalid' : ''; ?>" id="confirm_new_password" name="confirm_new_password" required>
                                    <?php if (isset($password_errors['confirm_new_password'])): ?>
                                        <div class="invalid-feedback"><?php echo htmlspecialchars($password_errors['confirm_new_password']); ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary">Change Password</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
