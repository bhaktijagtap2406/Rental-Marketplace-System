<?php
include 'includes/header.php';
if (!$currentUser || !in_array($role, ['owner','admin'])) { header('Location: /login.php'); exit; }
$ownerId = $currentUser['id'];
?>

<div class="container">
    <div class="page-header">
        <div>
            <h1>Owner Dashboard</h1>
            <p class="text-muted">Manage your listings and view booking activity.</p>
        </div>
        <a href="/add-item.php" class="btn btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:15px;height:15px;"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Add New Item
        </a>
    </div>

    <!-- Stats Row -->
    <div class="stats-row" id="statsRow">
        <div class="stat-card"><div class="stat-icon stat-blue"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:22px;height:22px;"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg></div><div><div class="stat-value" id="sListings">—</div><div class="stat-label">Active Listings</div></div></div>
        <div class="stat-card"><div class="stat-icon stat-green"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:22px;height:22px;"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg></div><div><div class="stat-value" id="sBookings">—</div><div class="stat-label">Active Bookings</div></div></div>
        <div class="stat-card"><div class="stat-icon stat-gold"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:22px;height:22px;"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg></div><div><div class="stat-value" id="sRevenue">—</div><div class="stat-label">Total Revenue</div></div></div>
    </div>

    <!-- Bookings for owner's items -->
    <div id="bookingSection" style="margin-bottom:40px; display:none;">
        <h2 style="margin-bottom:16px;">Incoming Bookings</h2>
        <div id="bookingList"></div>
    </div>

    <!-- Listings Grid -->
    <h2 style="margin-bottom:16px;">My Listings</h2>
    <div class="card-grid" id="ownerGrid">
        <div class="skeleton"></div><div class="skeleton"></div><div class="skeleton"></div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', async () => {
    const [itemsRes, bookingsRes] = await Promise.all([
        App.api('/items.php?owner_id=<?= $ownerId ?>'),
        App.api('/bookings.php')
    ]);

    const items    = itemsRes.data?.data    || [];
    const bookings = bookingsRes.data?.data || [];
    const active   = bookings.filter(b => b.status === 'confirmed');
    const revenue  = active.reduce((s, b) => s + (b.total_price || 0), 0);

    document.getElementById('sListings').textContent = items.length;
    document.getElementById('sBookings').textContent = active.length;
    document.getElementById('sRevenue').textContent  = '₹' + revenue.toLocaleString();

    // Incoming bookings table
    if (bookings.length > 0) {
        const sec  = document.getElementById('bookingSection');
        const list = document.getElementById('bookingList');
        sec.style.display = '';
        list.innerHTML = `
            <div class="table-wrap">
                <table class="data-table">
                    <thead><tr><th>Item</th><th>Renter</th><th>Dates</th><th>Days</th><th>Total</th><th>Status</th></tr></thead>
                    <tbody>${bookings.map(b => `
                        <tr>
                            <td><strong>${b.item?.title || '—'}</strong></td>
                            <td>${b.renter_name || '—'}</td>
                            <td style="white-space:nowrap;">${b.start_date} → ${b.end_date}</td>
                            <td>${b.days}</td>
                            <td><strong>₹${Number(b.total_price).toLocaleString()}</strong></td>
                            <td><span class="status-badge status-${b.status}">${b.status}</span></td>
                        </tr>`).join('')}
                    </tbody>
                </table>
            </div>`;
    }

    // Listings grid
    const grid = document.getElementById('ownerGrid');
    if (!items.length) {
        grid.innerHTML = `<div class="empty-state" style="grid-column:1/-1"><p>No listings yet.</p><a href="/add-item.php" class="btn btn-secondary" style="margin-top:12px;">Create first listing</a></div>`;
        return;
    }

    grid.innerHTML = '';
    items.forEach(item => {
        const card = document.createElement('div'); card.className = 'card';
        card.innerHTML = `
            ${item.image_url
                ? `<div class="card-img" style="background-image:url('${item.image_url}')"></div>`
                : `<div class="card-img card-img-placeholder"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="width:44px;height:44px;"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg></div>`}
            <div class="card-body">
                <div class="card-tags"><span class="cat-tag">${item.category}</span><span class="loc-tag">${item.location}</span></div>
                <h3 class="card-title">${item.title}</h3>
                <p class="card-desc">${item.description || ''}</p>
                <div class="card-footer">
                    <div class="price">₹${Number(item.price_per_day).toLocaleString()} <span>/day</span></div>
                    <button onclick="deleteItem('${item.id}',this)" class="btn btn-ghost-danger" style="font-size:0.8rem; padding:6px 12px;">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg>
                        Delete
                    </button>
                </div>
            </div>`;
        grid.appendChild(card);
    });
});

async function deleteItem(id, btn) {
    if (!confirm('Delete this listing permanently?')) return;
    btn.disabled = true; btn.textContent = '…';
    const res = await App.api('/items.php?id=' + id, { method: 'DELETE' });
    if (res.error) { App.toast(res.error, 'error'); btn.disabled = false; btn.textContent = 'Delete'; }
    else { App.toast('Listing deleted.'); btn.closest('.card').remove(); }
}
</script>

<?php include 'includes/footer.php'; ?>
