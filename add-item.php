<?php
include 'includes/header.php';
if (!$currentUser || !in_array($role, ['owner','admin'])) { header('Location: /login.php'); exit; }
?>

<div class="container">
    <div class="page-header">
        <div>
            <h1>List a New Item</h1>
            <p class="text-muted">Fill in the details below to publish your rental listing.</p>
        </div>
        <a href="/dashboard.php" class="btn btn-secondary">← Back to Dashboard</a>
    </div>

    <div class="form-card">
        <form id="addItemForm">

            <!-- Image Upload -->
            <div class="form-group">
                <label class="form-label">Item Photo</label>
                <div id="uploadArea" class="upload-area" onclick="document.getElementById('imgFile').click()">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="width:40px;height:40px;color:var(--muted-foreground);"><polyline points="16 16 12 12 8 16"/><line x1="12" y1="12" x2="12" y2="21"/><path d="M20.39 18.39A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.3"/></svg>
                    <p style="margin-top:10px; color:var(--muted-foreground);">Click or drag to upload a photo</p>
                    <p style="font-size:0.8rem; color:var(--muted-foreground); margin-top:4px;">JPEG, PNG, WebP — max 5 MB</p>
                </div>
                <input type="file" id="imgFile" accept="image/*" style="display:none;" onchange="uploadImage(this)">
                <input type="hidden" id="imageUrl">
                <img id="imgPreview" style="display:none; width:100%; max-height:220px; object-fit:cover; border-radius:12px; margin-top:12px;" alt="Preview">
            </div>

            <div class="form-group">
                <label class="form-label">Item Title <span style="color:var(--danger)">*</span></label>
                <input type="text" id="fTitle" class="form-input" required placeholder="e.g. Sony A7 III Camera" maxlength="100">
            </div>

            <div class="form-group">
                <label class="form-label">Description <span style="color:var(--danger)">*</span></label>
                <textarea id="fDesc" class="form-textarea" rows="4" required placeholder="Describe the item, its condition, and what's included…"></textarea>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                <div class="form-group">
                    <label class="form-label">Category <span style="color:var(--danger)">*</span></label>
                    <select id="fCat" class="form-select" required>
                        <option value="">Select…</option>
                        <option>Electronics</option><option>Sports</option>
                        <option>Outdoors</option><option>Gaming</option>
                        <option>Tools</option><option>Vehicles</option>
                        <option>Fashion</option><option>Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Location <span style="color:var(--danger)">*</span></label>
                    <input type="text" id="fLoc" class="form-input" required placeholder="City, Area">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Price Per Day (₹) <span style="color:var(--danger)">*</span></label>
                <input type="number" id="fPrice" class="form-input" required placeholder="500" min="1" step="1">
            </div>

            <div id="formError" class="form-error" style="display:none; margin-top:4px;"></div>

            <button type="submit" class="btn btn-primary" style="width:100%; margin-top:8px; font-size:1rem; padding:14px;" id="submitBtn">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Publish Listing
            </button>
        </form>
    </div>
</div>

<script>
async function uploadImage(input) {
    const file    = input.files[0]; if (!file) return;
    const area    = document.getElementById('uploadArea');
    const preview = document.getElementById('imgPreview');

    preview.src = URL.createObjectURL(file);
    preview.style.display = 'block';
    area.innerHTML = '<p style="color:var(--muted-foreground);">Uploading…</p>';

    const fd = new FormData(); fd.append('image', file);
    try {
        const r = await fetch('/api/upload.php', { method:'POST', credentials:'same-origin', body: fd });
        const d = await r.json();
        if (d.url) {
            document.getElementById('imageUrl').value = d.url;
            area.innerHTML = '<p style="color:var(--primary); font-weight:600;">✓ Uploaded</p>';
        } else {
            App.toast(d.error || 'Upload failed', 'error');
            area.innerHTML = '<p style="color:var(--muted-foreground);">Click to re-upload</p>';
        }
    } catch(e) { App.toast('Upload failed', 'error'); area.innerHTML = '<p style="color:var(--muted-foreground);">Click to retry</p>'; }
}

document.getElementById('addItemForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = document.getElementById('submitBtn');
    const err = document.getElementById('formError');
    err.style.display = 'none';
    btn.disabled = true; btn.textContent = 'Publishing…';

    const res = await App.api('/items.php', {
        method: 'POST',
        body: JSON.stringify({
            title:       document.getElementById('fTitle').value,
            description: document.getElementById('fDesc').value,
            category:    document.getElementById('fCat').value,
            price_per_day: parseFloat(document.getElementById('fPrice').value),
            location:    document.getElementById('fLoc').value,
            image_url:   document.getElementById('imageUrl').value,
        })
    });

    btn.disabled = false; btn.textContent = 'Publish Listing';
    if (res.error) { err.textContent = res.error; err.style.display = ''; }
    else { App.toast('Item listed! 🎉'); setTimeout(() => window.location.href = '/dashboard.php', 1200); }
});
</script>

<?php include 'includes/footer.php'; ?>
