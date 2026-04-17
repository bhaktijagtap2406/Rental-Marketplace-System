<?php
if (session_status() === PHP_SESSION_NONE) session_start();

define('DATA_DIR', __DIR__ . '/../data/');
define('UPLOAD_DIR', __DIR__ . '/../uploads/');

if (!is_dir(DATA_DIR))   mkdir(DATA_DIR,   0777, true);
if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0777, true);

// ─── Utility Functions ────────────────────────────────────────────────────────

function readJSON($file) {
    if (!file_exists($file)) return [];
    $d = json_decode(file_get_contents($file), true);
    return is_array($d) ? $d : [];
}

function writeJSON($file, $data) {
    file_put_contents($file, json_encode(array_values($data), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

function generateID($prefix = '') {
    return $prefix . substr(str_replace('.', '', uniqid('', true)), 0, 14);
}

function getCurrentUser() {
    if (empty($_SESSION['user_id'])) return null;
    $users = readJSON(DATA_DIR . 'users.json');
    foreach ($users as $u) {
        if ($u['id'] === $_SESSION['user_id']) return $u;
    }
    return null;
}

function requireAuth() {
    $u = getCurrentUser();
    if (!$u) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized. Please log in.']);
        exit;
    }
    return $u;
}

function requireRole($role) {
    $u = requireAuth();
    if ($u['role'] !== $role && $u['role'] !== 'admin') {
        http_response_code(403);
        echo json_encode(['error' => "Access denied. Role required: $role"]);
        exit;
    }
    return $u;
}

/**
 * Discount tiers (applied to total base price):
 *  3–6 days  → 5%
 *  7–13 days → 10%
 *  14+ days  → 15%
 */
function calculateDiscount($days) {
    if ($days >= 14) return 15;
    if ($days >= 7)  return 10;
    if ($days >= 3)  return 5;
    return 0;
}

/**
 * Returns true if item has no confirmed bookings that overlap [startDate, endDate].
 */
function checkAvailability($bookings, $itemId, $startDate, $endDate, $excludeId = null) {
    $s = strtotime($startDate);
    $e = strtotime($endDate);
    foreach ($bookings as $b) {
        if ($b['item_id']          !== $itemId)      continue;
        if (($b['status'] ?? '')   === 'cancelled')  continue;
        if ($excludeId && $b['id'] === $excludeId)   continue;
        $bs = strtotime($b['start_date']);
        $be = strtotime($b['end_date']);
        if ($s <= $be && $e >= $bs) return false;   // overlap detected
    }
    return true;
}

// ─── Seed Data ────────────────────────────────────────────────────────────────

$usersFile    = DATA_DIR . 'users.json';
$itemsFile    = DATA_DIR . 'items.json';
$bookingsFile = DATA_DIR . 'bookings.json';

if (!file_exists($usersFile)) {
    writeJSON($usersFile, [
        ['id'=>'u1','name'=>'John Owner', 'email'=>'owner@rent.com', 'password'=>password_hash('owner123', PASSWORD_DEFAULT), 'role'=>'owner'],
        ['id'=>'u2','name'=>'Jane Renter','email'=>'renter@rent.com','password'=>password_hash('renter123',PASSWORD_DEFAULT),'role'=>'renter'],
        ['id'=>'u3','name'=>'Admin User', 'email'=>'admin@rent.com', 'password'=>password_hash('admin123', PASSWORD_DEFAULT), 'role'=>'admin'],
    ]);
}

if (!file_exists($itemsFile)) {
    writeJSON($itemsFile, [
        ['id'=>'i1','title'=>'Sony A7 III Camera',    'category'=>'Electronics','description'=>'Professional mirrorless camera with 24.2 MP full-frame sensor and exceptional low-light performance.','price_per_day'=>1500,'location'=>'Mumbai',    'image_url'=>'','owner_id'=>'u1','created_at'=>date('Y-m-d H:i:s')],
        ['id'=>'i2','title'=>'DJI Mini 3 Pro Drone',  'category'=>'Electronics','description'=>'Lightweight drone under 249 g with 4K/60fps camera and 34-minute flight time.','price_per_day'=>2000,'location'=>'Delhi',     'image_url'=>'','owner_id'=>'u1','created_at'=>date('Y-m-d H:i:s')],
        ['id'=>'i3','title'=>'Trek Mountain Bike',     'category'=>'Sports',     'description'=>'Premium hardtail mountain bike for trail adventures and off-road cycling.','price_per_day'=>500, 'location'=>'Pune',      'image_url'=>'','owner_id'=>'u1','created_at'=>date('Y-m-d H:i:s')],
        ['id'=>'i4','title'=>'4-Person Camping Tent',  'category'=>'Outdoors',   'description'=>'Waterproof family camping tent — sets up in 10 minutes, sleeps four.','price_per_day'=>300, 'location'=>'Bangalore', 'image_url'=>'','owner_id'=>'u1','created_at'=>date('Y-m-d H:i:s')],
        ['id'=>'i5','title'=>'Canon EOS R5',           'category'=>'Electronics','description'=>'45 MP mirrorless camera with 8K video, IBIS, and dual card slots.','price_per_day'=>2500,'location'=>'Hyderabad', 'image_url'=>'','owner_id'=>'u1','created_at'=>date('Y-m-d H:i:s')],
        ['id'=>'i6','title'=>'PS5 Gaming Console',     'category'=>'Gaming',     'description'=>'Sony PlayStation 5 with DualSense controller and 2 games included.','price_per_day'=>800, 'location'=>'Chennai',   'image_url'=>'','owner_id'=>'u1','created_at'=>date('Y-m-d H:i:s')],
        ['id'=>'i7','title'=>'GoPro Hero 12 Black',    'category'=>'Electronics','description'=>'Action camera with 5.3K video, HyperSmooth 6.0 stabilization, and waterproofing.','price_per_day'=>600, 'location'=>'Goa',       'image_url'=>'','owner_id'=>'u1','created_at'=>date('Y-m-d H:i:s')],
        ['id'=>'i8','title'=>'Camping Kayak (2-seat)',  'category'=>'Outdoors',   'description'=>'Tandem sit-on-top kayak with paddles and life jackets. Perfect for rivers and lakes.','price_per_day'=>700, 'location'=>'Kerala',    'image_url'=>'','owner_id'=>'u1','created_at'=>date('Y-m-d H:i:s')],
    ]);
}

if (!file_exists($bookingsFile)) writeJSON($bookingsFile, []);

header('Content-Type: application/json');
?>
