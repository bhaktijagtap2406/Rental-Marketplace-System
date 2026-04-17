<?php
include 'includes/header.php';
if (!$currentUser) { header('Location: /login.php'); exit; }
?>

<div class="container">
    <div class="page-header">
        <div>
            <h1>My Bookings</h1>
            <p class="text-muted">All your past and upcoming rental bookings.</p>
        </div>
    </div>

    <div id="bookingsWrap">
        <div class="skeleton" style="height:100px; margin-bottom:12px;"></div>
        <div class="skeleton" style="height:100px; margin-bottom:12px;"></div>
        <div class="skeleton" style="height:100px;"></div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', async () => {
    const wrap = document.getElementById('bookingsWrap');
    const res  = await App.api('/bookings.php');

    if (res.error) {
        wrap.innerHTML = `<div class="form-error" style="display:block;">${res.error}</div>`;
        return;
    }

    const bookings = res.data?.data || [];

    if (!bookings.length) {
        wrap.innerHTML = `
            <div class="empty-state">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="width:56px;height:56px;color:var(--muted-foreground);margin-bottom:16px;"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                <p>You have no bookings yet.</p>
                <a href="/index.php" class="btn btn-secondary" style="margin-top:14px;">Browse items</a>
            </div>`;
        return;
    }

    wrap.innerHTML = '';
    bookings.forEach(b => {
        const item = b.item || {};
        const el   = document.createElement('div');
        el.className = 'booking-card';

        const statusColor = { confirmed:'var(--primary)', cancelled:'var(--danger)', pending:'#f59e0b' };
        const sc = statusColor[b.status] || '#aaa';

        el.innerHTML = `
            <div class="booking-thumb" style="${item.image_url ? 'background-image:url('+item.image_url+');background-size:cover;background-position:center;' : ''}">
                ${!item.image_url ? '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="width:32px;height:32px;color:var(--muted-foreground);"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>' : ''}
            </div>
            <div class="booking-info">
                <div style="display:flex; gap:8px; align-items:center; flex-wrap:wrap; margin-bottom:6px;">
                    <h3 style="font-size:1.1rem;">${item.title || 'Unknown Item'}</h3>
                    ${item.category ? `<span class="cat-tag">${item.category}</span>` : ''}
                </div>
                <div class="booking-meta">
                    <span><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:13px;height:13px;"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg> ${b.start_date} → ${b.end_date}</span>
                    <span><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:13px;height:13px;"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg> ${b.days} day${b.days>1?'s':''}</span>
                    ${item.location ? `<span><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:13px;height:13px;"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg> ${item.location}</span>` : ''}
                </div>
                ${b.discount_pct > 0 ? `<div style="margin-top:6px; font-size:0.82rem; color:var(--primary); font-weight:600;">✨ ${b.discount_pct}% discount applied — saved ₹${Number(b.discount_amt).toLocaleString()}</div>` : ''}
            </div>
            <div class="booking-right">
                <div class="price" style="font-size:1.5rem; margin-bottom:2px;">₹${Number(b.total_price).toLocaleString()}</div>
                ${b.discount_amt > 0 ? `<div style="font-size:0.8rem; color:var(--muted-foreground); text-decoration:line-through;">₹${Number(b.base_price).toLocaleString()}</div>` : ''}
                <div style="margin-top:10px;">
                    <span class="status-badge status-${b.status}">${b.status.toUpperCase()}</span>
                </div>
                ${b.status === 'confirmed' ? `
                <button onclick="cancelBooking('${b.id}',this)" class="btn btn-ghost-danger" style="margin-top:10px; font-size:0.8rem; padding:6px 12px; width:100%;">Cancel</button>` : ''}
            </div>`;
        wrap.appendChild(el);
    });
});

async function cancelBooking(id, btn) {
    if (!confirm('Cancel this booking?')) return;
    btn.disabled = true; btn.textContent = '…';
    const res = await App.api('/bookings.php', { method:'PUT', body: JSON.stringify({ id, status:'cancelled' }) });
    if (res.error) { App.toast(res.error, 'error'); btn.disabled = false; btn.textContent = 'Cancel'; }
    else { App.toast('Booking cancelled.'); location.reload(); }
}
</script>

<?php include 'includes/footer.php'; ?>
