<?php
function start_secure_session()
{
    if (session_status() === PHP_SESSION_NONE) {
        $session_path = __DIR__ . '/../sessions';

        if (is_dir($session_path) && is_writable($session_path)) {
            session_save_path($session_path);
        }

        session_set_cookie_params([
            'httponly' => true,
            'samesite' => 'Lax'
        ]);

        if (!@session_start()) {
            error_log('Session could not be started.');
            die('Session error. Please refresh the page and try again.');
        }
    }
}

function sanitize_input($value)
{
    return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
}

function redirect($url)
{
    header('Location: ' . $url);
    exit;
}

function is_logged_in()
{
    start_secure_session();
    return isset($_SESSION['user_id']);
}

function is_admin()
{
    start_secure_session();
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function require_login()
{
    if (!is_logged_in()) {
        redirect('/complaint-system/login.php');
    }
}

function require_admin()
{
    require_login();

    if (!is_admin()) {
        redirect('/complaint-system/index.php');
    }
}

function get_status_badge_class($status)
{
    if ($status === 'In Progress') {
        return 'bg-warning text-dark';
    }

    if ($status === 'Resolved') {
        return 'bg-success';
    }

    if ($status === 'Rejected') {
        return 'bg-danger';
    }

    return 'bg-secondary';
}

function format_datetime($value)
{
    if (empty($value)) {
        return '-';
    }

    return date('d M Y, H:i', strtotime($value));
}

function format_date($value)
{
    if (empty($value)) {
        return '-';
    }

    return date('d M Y', strtotime($value));
}
?>
