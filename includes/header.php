<?php
if (session_status() === PHP_SESSION_NONE) session_start();

// Resolve current user from session
$currentUser = null;
if (!empty($_SESSION['user_id'])) {
    $dataDir    = __DIR__ . '/../data/';
    $usersFile  = $dataDir . 'users.json';
    if (file_exists($usersFile)) {
        $users = json_decode(file_get_contents($usersFile), true) ?: [];
        foreach ($users as $u) {
            if ($u['id'] === $_SESSION['user_id']) { $currentUser = $u; break; }
        }
    }
}
$role        = $currentUser['role'] ?? null;
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RentSmart — Premium Rental Marketplace</title>
    <meta name="description" content="Rent anything, anytime. Cameras, bikes, drones and more on RentSmart.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,600;9..40,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>

<nav class="navbar">
    <div class="container nav-inner">
        <a href="/index.php" class="brand">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>
            RentSmart
        </a>

        <div class="nav-links">
            <a href="/index.php"    class="nav-link <?= $currentPage==='index.php'    ?'active':'' ?>">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                Browse
            </a>

            <?php if ($role === 'owner' || $role === 'admin'): ?>
            <a href="/add-item.php" class="nav-link <?= $currentPage==='add-item.php' ?'active':'' ?>">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
                Add Item
            </a>
            <a href="/dashboard.php" class="nav-link <?= $currentPage==='dashboard.php'?'active':'' ?>">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                Dashboard
            </a>
            <?php endif; ?>

            <?php if ($role === 'renter' || $role === 'admin'): ?>
            <a href="/history.php"  class="nav-link <?= $currentPage==='history.php' ?'active':'' ?>">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                My Bookings
            </a>
            <?php endif; ?>

            <?php if ($role === 'admin'): ?>
            <a href="/admin.php"    class="nav-link <?= $currentPage==='admin.php'   ?'active':'' ?>">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                Admin
            </a>
            <?php endif; ?>

            <?php if ($currentUser): ?>
                <div class="nav-user">
                    <span class="role-badge role-<?= $role ?>"><?= ucfirst($role) ?></span>
                    <span class="nav-username"><?= htmlspecialchars($currentUser['name']) ?></span>
                </div>
                <a href="/logout.php" class="btn btn-ghost-danger">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:15px;height:15px;"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                    Logout
                </a>
            <?php else: ?>
                <a href="/login.php" class="btn btn-primary">Login</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<main>
