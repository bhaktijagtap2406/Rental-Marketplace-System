<?php
include 'includes/header.php';
if (!$currentUser || $role !== 'admin') { header('Location: /login.php'); exit; }
?>

<div class="container">
    <div class="page-header">
        <div>
            <h1>Admin Panel</h1>
            <p class="text-muted">Platform-wide overview of items, bookings, and users.</p>
        </div>
    </div>

    <!-- Stats -->
    <div class="stats-row" id="adminStats">
        <div class="stat-card"><div class="stat-value" id="aItems">—</div><div class="stat-label">Total Items</div></div>
        <div class="stat-card"><div class="stat-value" id="aBookings">—</div><div class="stat-label">Total Bookings</div></div>
        <div class="stat-card"><div class="stat-value" id="aConfirmed">—</div><div class="stat-label">Confirmed</div></div>
        <div class="stat-card"><div class="stat-value" id="aRevenue">—</div><div class="stat-label">Total Revenue</div></div>
    </div>

    <!-- Tabs -->
    <div class="tab-bar" style="margin-bottom:24px;">
        <button class="tab-btn active" id="tItems"    onclick="showTab('items')">Items</button>
        <button class="tab-btn"        id="tBookings" onclick="showTab('bookings')">Bookings</button>
        <button class="tab-btn"        id="tUsers"    onclick="showTab('users')">Users</button>
    </div>

    <div id="pItems"></div>
    <div id="pBookings" class="hidden"></div>
    <div id="pUsers"    class="hidden"></div>
</div>

<script>
function showTab(t) {
    ['items','bookings','users'].forEach(n => {
        document.getElementById('p' + n.charAt(0).toUpperCase() + n.slice(1)).classList.toggle('hidden', n!==t);
        document.getElementById('t' + n.charAt(0).toUpperCase() + n.slice(1)).classList.toggle('active', n===t);
    });
}

document.addEventListener('DOMContentLoaded', async () => {
    const [ir, br] = await Promise.all([App.api('/items.php'), App.api('/bookings.php')]);
    const items    = ir.data?.data    || [];
    const bookings = br.data?.data    || [];
    const conf     = bookings.filter(b => b.status === 'confirmed');
    const revenue  = conf.reduce((s,b) => s + (b.total_price||0), 0);

    document.getElementById('aItems').textContent     = items.length;
    document.getElementById('aBookings').textContent  = bookings.length;
    document.getElementById('aConfirmed').textContent = conf.length;
    document.getElementById('aRevenue').textContent   = '₹' + revenue.toLocaleString();

    // Items table
    document.getElementById('pItems').innerHTML = items.length ? `
        <div class="table-wrap"><table class="data-table">
            <thead><tr><th>Title</th><th>Category</th><th>Location</th><th>Price/Day</th><th>Owner</th><th>Action</th></tr></thead>
            <tbody>${items.map(i=>`
                <tr>
                    <td><strong>${i.title}</strong></td>
                    <td><span class="cat-tag">${i.category}</span></td>
                    <td>${i.location}</td>
                    <td>₹${Number(i.price_per_day).toLocaleString()}</td>
                    <td style="font-size:0.8rem; color:var(--muted-foreground);">${i.owner_id}</td>
                    <td><button onclick="adminDeleteItem('${i.id}',this)" class="btn btn-ghost-danger" style="font-size:0.78rem; padding:4px 10px;">Delete</button></td>
                </tr>`).join('')}
            </tbody>
        </table></div>` : '<div class="empty-state"><p>No items found.</p></div>';

    // Bookings table
    document.getElementById('pBookings').innerHTML = bookings.length ? `
        <div class="table-wrap"><table class="data-table">
            <thead><tr><th>ID</th><th>Item</th><th>Renter</th><th>Dates</th><th>Days</th><th>Discount</th><th>Total</th><th>Status</th><th>Action</th></tr></thead>
            <tbody>${bookings.map(b=>{
                const sc = {confirmed:'var(--primary)',cancelled:'var(--danger)'}[b.status]||'#aaa';
                return `<tr>
                    <td style="font-size:0.72rem;color:var(--muted-foreground);">${b.id.slice(0,10)}…</td>
                    <td><strong>${b.item?.title||b.item_id.slice(0,8)}</strong></td>
                    <td>${b.renter_name||b.renter_id.slice(0,8)}</td>
                    <td style="white-space:nowrap;">${b.start_date} → ${b.end_date}</td>
                    <td>${b.days}</td>
                    <td>${b.discount_pct>0 ? b.discount_pct+'%' : '—'}</td>
                    <td><strong>₹${Number(b.total_price).toLocaleString()}</strong></td>
                    <td><span class="status-badge status-${b.status}">${b.status}</span></td>
                    <td>${b.status==='confirmed' ? `<button onclick="adminCancel('${b.id}',this)" class="btn btn-ghost-danger" style="font-size:0.78rem;padding:4px 10px;">Cancel</button>` : '—'}</td>
                </tr>`;
            }).join('')}</tbody>
        </table></div>` : '<div class="empty-state"><p>No bookings found.</p></div>';

    // Users (static — predefined)
    document.getElementById('pUsers').innerHTML = `
        <div class="table-wrap"><table class="data-table">
            <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Note</th></tr></thead>
            <tbody>
                <tr><td>John Owner</td><td>owner@rent.com</td><td><span class="role-badge role-owner">Owner</span></td><td class="text-muted" style="font-size:0.82rem;">Predefined seed user</td></tr>
                <tr><td>Jane Renter</td><td>renter@rent.com</td><td><span class="role-badge role-renter">Renter</span></td><td class="text-muted" style="font-size:0.82rem;">Predefined seed user</td></tr>
                <tr><td>Admin User</td><td>admin@rent.com</td><td><span class="role-badge role-admin">Admin</span></td><td class="text-muted" style="font-size:0.82rem;">Predefined seed user</td></tr>
            </tbody>
        </table></div>
        <p class="text-muted" style="margin-top:12px; font-size:0.85rem;">Registered users are stored in <code>data/users.json</code>. Additional registrations will also appear there.</p>`;
});

async function adminDeleteItem(id, btn) {
    if (!confirm('Delete this item?')) return;
    btn.disabled = true;
    const r = await App.api('/items.php?id='+id, {method:'DELETE'});
    if (r.error) { App.toast(r.error,'error'); btn.disabled=false; }
    else { App.toast('Item deleted.'); location.reload(); }
}

async function adminCancel(id, btn) {
    if (!confirm('Cancel this booking?')) return;
    btn.disabled = true;
    const r = await App.api('/bookings.php', {method:'PUT', body:JSON.stringify({id, status:'cancelled'})});
    if (r.error) { App.toast(r.error,'error'); btn.disabled=false; }
    else { App.toast('Booking cancelled.'); location.reload(); }
}
</script>

<?php include 'includes/footer.php'; ?>
