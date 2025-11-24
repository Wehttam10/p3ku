<?php
/**
 * Authentication and Authorization Utility Functions
 */

// Define role constants
define('ROLE_ADMIN', 'admin');
define('ROLE_PARENT', 'parent');
define('ROLE_PARTICIPANT', 'participant');
define('ROLE_GUEST', 'guest');


function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function get_user_role() {
    if (is_logged_in() && isset($_SESSION['user_role'])) {
        $role = strtolower($_SESSION['user_role']);
        if (in_array($role, [ROLE_ADMIN, ROLE_PARENT, ROLE_PARTICIPANT])) {
            return $role;
        }
    }
    return ROLE_GUEST;
}

/**
 * Enforces role-based access control.
 */
function check_access($required_roles, $redirect_path = URL_ROOT) {
    // NOTE: Uses URL_ROOT for redirects
    if ($redirect_path === '/p3ku-main/' || $redirect_path === '/p3ku-main/home') {
        $redirect_path = URL_ROOT;
    }

    if (!is_array($required_roles)) {
        $required_roles = [$required_roles];
    }

    $current_role = get_user_role();

    if (!in_array($current_role, $required_roles)) {
        header('Location: ' . $redirect_path);
        exit();
    }
}

function is_admin() {
    return get_user_role() === ROLE_ADMIN;
}

function is_parent() {
    return get_user_role() === ROLE_PARENT;
}

function is_participant() {
    return get_user_role() === ROLE_PARTICIPANT;
}

/**
 * Generates and retrieves the CSRF token.
 */
function get_csrf_token() {
    // Session is assumed to be started by errorHandler.php
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); 
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validates the token submitted via POST request against the session token.
 */
function validate_csrf_token($submitted_token) {
    // We rely on the global session being available.
    return !empty($submitted_token) && 
           !empty($_SESSION['csrf_token']) && 
           hash_equals($_SESSION['csrf_token'], $submitted_token);
}