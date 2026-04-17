<?php
require_once 'config.php';

$bookingsFile = DATA_DIR . 'bookings.json';
$itemsFile    = DATA_DIR . 'items.json';
$method       = $_SERVER['REQUEST_METHOD'];

// ── GET: list bookings (role-filtered) ──────────────────────────────────────
if ($method === 'GET') {
    $user     = requireAuth();
    $bookings = readJSON($bookingsFile);
    $items    = readJSON($itemsFile);

    // Build a quick item lookup
    $itemMap = [];
    foreach ($items as $i) $itemMap[$i['id']] = $i;

    // Role-based scoping
    if ($user['role'] === 'renter') {
        $bookings = array_values(array_filter($bookings, fn($b) => $b['renter_id'] === $user['id']));
    } elseif ($user['role'] === 'owner') {
        $ownerItemIds = array_map(fn($i) => $i['id'], array_filter($items, fn($i) => $i['owner_id'] === $user['id']));
        $bookings     = array_values(array_filter($bookings, fn($b) => in_array($b['item_id'], $ownerItemIds)));
    }
    // admin → all bookings

    // Enrich with item data
    foreach ($bookings as &$b) {
        $b['item'] = $itemMap[$b['item_id']] ?? null;
    }
    unset($b);

    // Sort newest first
    usort($bookings, fn($a, $b) => strcmp($b['created_at'], $a['created_at']));

    echo json_encode(['success' => true, 'data' => $bookings]);
    exit;
}

// ── POST: create booking (renter only) ──────────────────────────────────────
if ($method === 'POST') {
    $user  = requireRole('renter');
    $input = json_decode(file_get_contents('php://input'), true) ?? [];

    $itemId    = trim($input['item_id']    ?? '');
    $startDate = trim($input['start_date'] ?? '');
    $endDate   = trim($input['end_date']   ?? '');

    if (!$itemId || !$startDate || !$endDate) {
        http_response_code(400);
        echo json_encode(['error' => 'item_id, start_date and end_date are required.']);
        exit;
    }

    // Validate dates
    $today = strtotime(date('Y-m-d'));
    if (strtotime($startDate) < $today) {
        http_response_code(400);
        echo json_encode(['error' => 'Start date cannot be in the past.']);
        exit;
    }
    if (strtotime($endDate) <= strtotime($startDate)) {
        http_response_code(400);
        echo json_encode(['error' => 'End date must be after start date.']);
        exit;
    }

    // Fetch item
    $items = readJSON($itemsFile);
    $item  = null;
    foreach ($items as $i) {
        if ($i['id'] === $itemId) { $item = $i; break; }
    }
    if (!$item) {
        http_response_code(404);
        echo json_encode(['error' => 'Item not found.']);
        exit;
    }

    // Availability check
    $bookings = readJSON($bookingsFile);
    if (!checkAvailability($bookings, $itemId, $startDate, $endDate)) {
        http_response_code(409);
        echo json_encode(['error' => 'This item is already booked for the selected dates.']);
        exit;
    }

    // Pricing + discount
    $days        = (int)ceil((strtotime($endDate) - strtotime($startDate)) / 86400);
    $basePrice   = $days * $item['price_per_day'];
    $discountPct = calculateDiscount($days);
    $discountAmt = round(($basePrice * $discountPct) / 100, 2);
    $finalPrice  = round($basePrice - $discountAmt, 2);

    $newBooking = [
        'id'           => generateID('b'),
        'item_id'      => $itemId,
        'renter_id'    => $user['id'],
        'renter_name'  => $user['name'],
        'start_date'   => $startDate,
        'end_date'     => $endDate,
        'days'         => $days,
        'base_price'   => $basePrice,
        'discount_pct' => $discountPct,
        'discount_amt' => $discountAmt,
        'total_price'  => $finalPrice,
        'status'       => 'confirmed',
        'created_at'   => date('Y-m-d H:i:s'),
    ];

    $bookings[] = $newBooking;
    writeJSON($bookingsFile, $bookings);

    echo json_encode(['success' => true, 'data' => $newBooking]);
    exit;
}

// ── PUT: update booking status (cancel, etc.) ────────────────────────────────
if ($method === 'PUT') {
    $user  = requireAuth();
    $input = json_decode(file_get_contents('php://input'), true) ?? [];
    $id    = $input['id'] ?? null;

    if (!$id) {
        http_response_code(400);
        echo json_encode(['error' => 'Booking ID required.']);
        exit;
    }

    $bookings = readJSON($bookingsFile);
    $found    = false;

    foreach ($bookings as &$b) {
        if ($b['id'] !== $id) continue;
        if ($b['renter_id'] !== $user['id'] && $user['role'] !== 'admin') {
            http_response_code(403);
            echo json_encode(['error' => 'Access denied.']);
            exit;
        }
        $b['status'] = $input['status'] ?? 'cancelled';
        $found = true;
        break;
    }
    unset($b);

    if (!$found) {
        http_response_code(404);
        echo json_encode(['error' => 'Booking not found.']);
        exit;
    }

    writeJSON($bookingsFile, $bookings);
    echo json_encode(['success' => true]);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed.']);
?>
