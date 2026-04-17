<?php
require_once 'config.php';

$itemId    = trim($_GET['item_id']    ?? '');
$startDate = trim($_GET['start_date'] ?? '');
$endDate   = trim($_GET['end_date']   ?? '');

if (!$itemId || !$startDate || !$endDate) {
    http_response_code(400);
    echo json_encode(['error' => 'item_id, start_date and end_date are required.']);
    exit;
}

if (strtotime($endDate) <= strtotime($startDate)) {
    http_response_code(400);
    echo json_encode(['error' => 'End date must be after start date.']);
    exit;
}

$bookings  = readJSON(DATA_DIR . 'bookings.json');
$items     = readJSON(DATA_DIR . 'items.json');

$available = checkAvailability($bookings, $itemId, $startDate, $endDate);

$result = ['available' => $available];

// Find item and calculate price breakdown if available
$item = null;
foreach ($items as $i) {
    if ($i['id'] === $itemId) { $item = $i; break; }
}

if ($item) {
    $days        = (int)ceil((strtotime($endDate) - strtotime($startDate)) / 86400);
    $basePrice   = $days * $item['price_per_day'];
    $discountPct = calculateDiscount($days);
    $discountAmt = round(($basePrice * $discountPct) / 100, 2);
    $finalPrice  = round($basePrice - $discountAmt, 2);

    $result['item_title']   = $item['title'];
    $result['days']         = $days;
    $result['price_per_day']= $item['price_per_day'];
    $result['base_price']   = $basePrice;
    $result['discount_pct'] = $discountPct;
    $result['discount_amt'] = $discountAmt;
    $result['final_price']  = $finalPrice;
}

echo json_encode($result);
?>
