<?php
require_once 'config.php';

$itemsFile = DATA_DIR . 'items.json';
$method    = $_SERVER['REQUEST_METHOD'];

// ── GET: list items (with optional filters) ──────────────────────────────────
if ($method === 'GET') {
    $items    = readJSON($itemsFile);
    $ownerId  = $_GET['owner_id']  ?? null;
    $category = $_GET['category']  ?? null;
    $location = $_GET['location']  ?? null;
    $maxPrice = isset($_GET['max_price']) ? (float)$_GET['max_price'] : null;
    $search   = $_GET['search']    ?? null;
    $id       = $_GET['id']        ?? null;

    // Single item lookup
    if ($id) {
        foreach ($items as $item) {
            if ($item['id'] === $id) {
                echo json_encode(['success' => true, 'data' => $item]);
                exit;
            }
        }
        http_response_code(404);
        echo json_encode(['error' => 'Item not found.']);
        exit;
    }

    // Filter
    if ($ownerId)  $items = array_values(array_filter($items, fn($i) => $i['owner_id'] === $ownerId));
    if ($category) $items = array_values(array_filter($items, fn($i) => $i['category'] === $category));
    if ($location) $items = array_values(array_filter($items, fn($i) => $i['location'] === $location));
    if ($maxPrice !== null) $items = array_values(array_filter($items, fn($i) => $i['price_per_day'] <= $maxPrice));
    if ($search) {
        $q     = strtolower($search);
        $items = array_values(array_filter($items, fn($i) =>
            str_contains(strtolower($i['title']),            $q) ||
            str_contains(strtolower($i['description'] ?? ''), $q)
        ));
    }

    echo json_encode(['success' => true, 'data' => $items]);
    exit;
}

// ── POST: create item (owner only) ───────────────────────────────────────────
if ($method === 'POST') {
    $user  = requireRole('owner');
    $input = json_decode(file_get_contents('php://input'), true) ?? [];

    $title       = trim($input['title']       ?? '');
    $description = trim($input['description'] ?? '');
    $category    = trim($input['category']    ?? '');
    $price       = (float)($input['price_per_day'] ?? 0);
    $location    = trim($input['location']    ?? '');
    $imageUrl    = trim($input['image_url']   ?? '');

    if (!$title || !$category || !$price || !$location) {
        http_response_code(400);
        echo json_encode(['error' => 'Title, category, price and location are required.']);
        exit;
    }

    $items   = readJSON($itemsFile);
    $newItem = [
        'id'          => generateID('i'),
        'title'       => $title,
        'description' => $description,
        'category'    => $category,
        'price_per_day'=> $price,
        'location'    => $location,
        'image_url'   => $imageUrl,
        'owner_id'    => $user['id'],
        'created_at'  => date('Y-m-d H:i:s'),
    ];
    $items[] = $newItem;
    writeJSON($itemsFile, $items);

    echo json_encode(['success' => true, 'data' => $newItem]);
    exit;
}

// ── DELETE: remove item ──────────────────────────────────────────────────────
if ($method === 'DELETE') {
    $user = requireRole('owner');
    $id   = $_GET['id'] ?? null;

    if (!$id) {
        http_response_code(400);
        echo json_encode(['error' => 'Item ID required.']);
        exit;
    }

    $items = readJSON($itemsFile);
    $found = false;
    $items = array_values(array_filter($items, function ($item) use ($id, $user, &$found) {
        if ($item['id'] !== $id) return true;
        if ($item['owner_id'] !== $user['id'] && $user['role'] !== 'admin') {
            http_response_code(403);
            echo json_encode(['error' => 'You can only delete your own items.']);
            exit;
        }
        $found = true;
        return false;
    }));

    if (!$found) {
        http_response_code(404);
        echo json_encode(['error' => 'Item not found.']);
        exit;
    }

    writeJSON($itemsFile, $items);
    echo json_encode(['success' => true]);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed.']);
?>
