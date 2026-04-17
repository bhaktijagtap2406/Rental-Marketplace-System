<?php include 'includes/header.php'; ?>

<div class="container">

    <!-- ─── Hero ─────────────────────────────────────────────────────────── -->
    <section class="hero">
        <div class="hero-glow"></div>
        <div class="hero-content">
            <div class="hero-badge">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:13px;height:13px;"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                Smart Rental Marketplace
            </div>
            <h1>Rent Anything,<br><span class="gradient-text">Anytime.</span></h1>
            <p>Browse cameras, bikes, drones and more. Save money by renting — not buying.</p>
            <div class="hero-search">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:18px;height:18px;flex-shrink:0;color:var(--muted-foreground)"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" id="searchInput" placeholder="Search by name or description…" autocomplete="off">
            </div>
        </div>
        <div class="hero-stats">
            <div class="hero-stat"><strong id="heroItemCount">—</strong><span>Items Listed</span></div>
            <div class="hero-stat-divider"></div>
            <div class="hero-stat"><strong>8</strong><span>Cities</span></div>
            <div class="hero-stat-divider"></div>
            <div class="hero-stat"><strong>5</strong><span>Categories</span></div>
        </div>
    </section>

    <!-- ─── Filters ──────────────────────────────────────────────────────── -->
    <div class="filter-bar" id="filterBar">
        <div class="filter-group">
            <label class="filter-label">Category</label>
            <select id="filterCategory" class="form-select" onchange="applyFilters()">
                <option value="">All Categories</option>
                <option>Electronics</option><option>Sports</option>
                <option>Outdoors</option><option>Gaming</option>
                <option>Tools</option><option>Vehicles</option>
                <option>Fashion</option><option>Other</option>
            </select>
        </div>
        <div class="filter-group">
            <label class="filter-label">Location</label>
            <select id="filterLocation" class="form-select" onchange="applyFilters()">
                <option value="">All Locations</option>
            </select>
        </div>
        <div class="filter-group">
            <label class="filter-label">Max Price: <strong id="priceLabel">₹10,000</strong>/day</label>
            <input type="range" id="filterPrice" min="100" max="10000" value="10000" step="100"
                   oninput="document.getElementById('priceLabel').textContent='₹'+Number(this.value).toLocaleString(); applyFilters()">
        </div>
        <button class="btn btn-secondary" onclick="resetFilters()" style="align-self:flex-end;">Clear All</button>
    </div>

    <!-- ─── Results Row ──────────────────────────────────────────────────── -->
    <div class="results-row">
        <span id="resultsCount" class="text-muted" style="font-size:0.9rem;"></span>
        <button class="btn btn-secondary" id="filterToggleBtn" onclick="toggleFilters()" style="font-size:0.85rem; padding:7px 14px;">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:15px;height:15px;"><line x1="4" y1="6" x2="20" y2="6"/><line x1="8" y1="12" x2="16" y2="12"/><line x1="11" y1="18" x2="13" y2="18"/></svg>
            Filter
        </button>
    </div>

    <!-- ─── Item Grid ─────────────────────────────────────────────────────── -->
    <div class="card-grid" id="itemsGrid">
        <?php for($i=0;$i<8;$i++): ?>
        <div class="skeleton"></div>
        <?php endfor; ?>
    </div>
</div>

<!-- ─── Booking Modal ─────────────────────────────────────────────────────── -->
<div class="modal-overlay" id="bookingModal" onclick="handleOverlayClick(event)">
    <div class="modal">
        <div class="modal-header">
            <div>
                <h3 id="modalTitle">Book Item</h3>
                <p id="modalSubtitle" class="text-muted" style="font-size:0.9rem; margin-top:3px;"></p>
            </div>
            <button class="modal-close" onclick="closeModal()">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>

        <form id="bookingForm">
            <input type="hidden" id="bookItemId">

            <div class="date-grid">
                <div class="form-group">
                    <label class="form-label">Start Date</label>
                    <input type="date" id="bookStart" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">End Date</label>
                    <input type="date" id="bookEnd" class="form-input" required>
                </div>
            </div>

            <div id="availabilityBox" style="min-height:90px;"></div>

            <button type="submit" class="btn btn-primary" id="bookBtn" style="width:100%; margin-top:8px;" disabled>
                Confirm Booking
            </button>
        </form>
    </div>
</div>

<script>
var allItems = [];
var isLoggedIn = <?= $currentUser ? 'true' : 'false' ?>;
var userRole   = <?= $currentUser ? '"'.htmlspecialchars($role).'"' : 'null' ?>;

// ── Load Items ────────────────────────────────────────────────────────────────
async function loadItems() {
    const res = await App.api('/items.php');
    if (res.error) {
        document.getElementById('itemsGrid').innerHTML =
            '<p class="empty-state" style="color:var(--danger)">Could not load items. Is the PHP server running?</p>';
        return;
    }
    allItems = res.data.data || [];
    document.getElementById('heroItemCount').textContent = allItems.length;

    // Populate location filter
    const locs = [...new Set(allItems.map(i => i.location).filter(Boolean))].sort();
    const sel  = document.getElementById('filterLocation');
    locs.forEach(l => { const o = document.createElement('option'); o.value = o.textContent = l; sel.appendChild(o); });

    renderItems(allItems);
}

function renderItems(items) {
    const grid = document.getElementById('itemsGrid');
    const count = document.getElementById('resultsCount');
    count.textContent = `${items.length} item${items.length !== 1 ? 's' : ''} found`;

    if (!items.length) {
        grid.innerHTML = '<div class="empty-state" style="grid-column:1/-1"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="width:56px;height:56px;color:var(--muted-foreground);margin-bottom:16px;"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg><p>No items match your filters.</p><p style="font-size:0.85rem; margin-top:6px;">Try adjusting or clearing filters.</p></div>';
        return;
    }

    grid.innerHTML = '';
    items.forEach(item => {
        const card = document.createElement('div');
        card.className = 'card';

        const imgHtml = item.image_url
            ? `<div class="card-img" style="background-image:url('${item.image_url}')"></div>`
            : `<div class="card-img card-img-placeholder"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="width:48px;height:48px;"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg></div>`;

        let actionHtml = '';
        if (!isLoggedIn) {
            actionHtml = `<a href="/login.php" class="btn btn-secondary" style="font-size:0.8rem;padding:6px 14px;">Login to Book</a>`;
        } else if (userRole === 'renter') {
            actionHtml = `<button class="btn btn-primary" style="font-size:0.8rem;padding:6px 14px;" onclick="openModal('${item.id}','${item.title.replace(/'/g,"\\'")}',${item.price_per_day})">Book Now</button>`;
        }

        card.innerHTML = `
            ${imgHtml}
            <div class="card-body">
                <div class="card-tags">
                    <span class="cat-tag">${item.category}</span>
                    <span class="loc-tag"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:11px;height:11px;"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>${item.location}</span>
                </div>
                <h3 class="card-title">${item.title}</h3>
                <p class="card-desc">${item.description || ''}</p>
                <div class="card-footer">
                    <div class="price">₹${Number(item.price_per_day).toLocaleString()} <span>/day</span></div>
                    ${actionHtml}
                </div>
            </div>`;
        grid.appendChild(card);
    });
}

// ── Filters ────────────────────────────────────────────────────────────────
function applyFilters() {
    const q   = document.getElementById('searchInput').value.toLowerCase();
    const cat = document.getElementById('filterCategory').value;
    const loc = document.getElementById('filterLocation').value;
    const max = parseInt(document.getElementById('filterPrice').value);
    const filtered = allItems.filter(i =>
        (!cat || i.category === cat) &&
        (!loc || i.location === loc) &&
        i.price_per_day <= max &&
        (!q || i.title.toLowerCase().includes(q) || (i.description||'').toLowerCase().includes(q))
    );
    renderItems(filtered);
}

function toggleFilters() {
    document.getElementById('filterBar').classList.toggle('open');
}

function resetFilters() {
    ['filterCategory','filterLocation'].forEach(id => document.getElementById(id).value = '');
    document.getElementById('filterPrice').value = 10000;
    document.getElementById('priceLabel').textContent = '₹10,000';
    document.getElementById('searchInput').value = '';
    renderItems(allItems);
}

document.getElementById('searchInput').addEventListener('input', applyFilters);

// ── Booking Modal ─────────────────────────────────────────────────────────
const modal = document.getElementById('bookingModal');

function openModal(id, title, pricePerDay) {
    document.getElementById('bookItemId').value = id;
    document.getElementById('modalTitle').textContent = title;
    document.getElementById('modalSubtitle').textContent = '₹' + Number(pricePerDay).toLocaleString() + ' / day';
    document.getElementById('availabilityBox').innerHTML = '';
    document.getElementById('bookBtn').disabled = true;
    document.getElementById('bookingForm').reset();
    document.getElementById('bookItemId').value = id;
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('bookStart').min = today;
    document.getElementById('bookEnd').min   = today;
    modal.classList.add('active');
}

function closeModal() { modal.classList.remove('active'); }
function handleOverlayClick(e) { if (e.target === modal) closeModal(); }

async function checkAvail() {
    const id    = document.getElementById('bookItemId').value;
    const start = document.getElementById('bookStart').value;
    const end   = document.getElementById('bookEnd').value;
    const box   = document.getElementById('availabilityBox');
    const btn   = document.getElementById('bookBtn');

    if (!start || !end) return;
    if (new Date(end) <= new Date(start)) {
        box.innerHTML = '<div class="avail-error"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:15px;height:15px;"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg> End date must be after start date.</div>';
        btn.disabled = true; return;
    }

    box.innerHTML = '<div style="color:var(--muted-foreground);font-size:0.9rem;padding:12px 0;">Checking availability…</div>';

    const res = await App.api(`/availability.php?item_id=${id}&start_date=${start}&end_date=${end}`);
    if (res.error) { box.innerHTML = `<div class="avail-error">${res.error}</div>`; btn.disabled = true; return; }

    const d = res.data;
    if (!d.available) {
        box.innerHTML = '<div class="avail-error"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:15px;height:15px;"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg> Not available for selected dates.</div>';
        btn.disabled = true; return;
    }

    const discountRow = d.discount_pct > 0
        ? `<div class="price-row discount-row"><span>Discount (${d.discount_pct}%)</span><span>−₹${Number(d.discount_amt).toLocaleString()}</span></div>` : '';

    box.innerHTML = `
        <div class="avail-ok"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:15px;height:15px;"><polyline points="20 6 9 17 4 12"/></svg> Available!</div>
        <div class="price-breakdown">
            <div class="price-row"><span>Duration</span><span>${d.days} day${d.days>1?'s':''}</span></div>
            <div class="price-row"><span>Base price</span><span>₹${Number(d.base_price).toLocaleString()}</span></div>
            ${discountRow}
            <div class="price-row total-row"><span>Total</span><strong>₹${Number(d.final_price).toLocaleString()}</strong></div>
        </div>
        ${d.discount_pct > 0 ? '<div class="discount-badge">🎉 '+d.discount_pct+'% long-stay discount applied!</div>' : ''}`;
    btn.disabled = false;
}

document.getElementById('bookStart').addEventListener('change', checkAvail);
document.getElementById('bookEnd').addEventListener('change', checkAvail);

document.getElementById('bookingForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = document.getElementById('bookBtn');
    btn.textContent = 'Booking…'; btn.disabled = true;

    const res = await App.api('/bookings.php', {
        method: 'POST',
        body: JSON.stringify({ item_id: document.getElementById('bookItemId').value, start_date: document.getElementById('bookStart').value, end_date: document.getElementById('bookEnd').value })
    });

    if (res.error) {
        App.toast(res.error, 'error');
        btn.textContent = 'Confirm Booking'; btn.disabled = false;
    } else {
        App.toast('Booking confirmed! 🎉');
        closeModal();
        setTimeout(() => window.location.href = '/history.php', 1600);
    }
});

document.addEventListener('DOMContentLoaded', loadItems);
</script>

<?php include 'includes/footer.php'; ?>
