<?php
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];

// ── GET: check current session ──────────────────────────────────────────────
if ($method === 'GET') {
    $user = getCurrentUser();
    if ($user) {
        unset($user['password']);
        echo json_encode(['success' => true, 'user' => $user]);
    } else {
        echo json_encode(['success' => false, 'user' => null]);
    }
    exit;
}

// ── DELETE: logout ──────────────────────────────────────────────────────────
if ($method === 'DELETE') {
    session_unset();
    session_destroy();
    echo json_encode(['success' => true]);
    exit;
}

// ── POST: login or register ─────────────────────────────────────────────────
if ($method !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed.']);
    exit;
}

$input  = json_decode(file_get_contents('php://input'), true) ?? [];
$action = strtolower(trim($input['action'] ?? 'login'));
$email  = strtolower(trim($input['email'] ?? ''));
$pass   = $input['password'] ?? '';

if (!$email || !$pass) {
    http_response_code(400);
    echo json_encode(['error' => 'Email and password are required.']);
    exit;
}

$usersFile = DATA_DIR . 'users.json';
$users     = readJSON($usersFile);

// ── Register ─────────────────────────────────────────────────────────────────
if ($action === 'register') {
    foreach ($users as $u) {
        if ($u['email'] === $email) {
            http_response_code(400);
            echo json_encode(['error' => 'This email is already registered.']);
            exit;
        }
    }

    if (strlen($pass) < 6) {
        http_response_code(400);
        echo json_encode(['error' => 'Password must be at least 6 characters.']);
        exit;
    }

    $newUser = [
        'id'         => generateID('u'),
        'name'       => trim($input['name'] ?? explode('@', $email)[0]),
        'email'      => $email,
        'password'   => password_hash($pass, PASSWORD_DEFAULT),
        'role'       => in_array($input['role'] ?? '', ['owner','renter']) ? $input['role'] : 'renter',
        'created_at' => date('Y-m-d H:i:s'),
    ];

    $users[] = $newUser;
    writeJSON($usersFile, $users);

    $_SESSION['user_id'] = $newUser['id'];
    unset($newUser['password']);
    echo json_encode(['success' => true, 'user' => $newUser]);
    exit;
}

// ── Login ─────────────────────────────────────────────────────────────────────
foreach ($users as $u) {
    if ($u['email'] === $email && password_verify($pass, $u['password'])) {
        $_SESSION['user_id'] = $u['id'];
        unset($u['password']);
        echo json_encode(['success' => true, 'user' => $u]);
        exit;
    }
}

http_response_code(401);
echo json_encode(['error' => 'Invalid email or password.']);
?>
