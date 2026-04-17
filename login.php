<?php
include 'includes/header.php';
// Already logged in → redirect
if ($currentUser) { header('Location: /index.php'); exit; }
?>

<div class="container">
    <div class="auth-wrap">

        <!-- Demo Credentials Card -->
        <div class="demo-card">
            <h3 style="margin-bottom:14px; color:var(--primary);">Demo Credentials</h3>
            <div class="demo-row" onclick="fillLogin('owner@rent.com','owner123')">
                <span class="role-badge role-owner">Owner</span>
                <div>
                    <div style="font-weight:600; font-size:0.9rem;">John Owner</div>
                    <div class="text-muted" style="font-size:0.8rem;">owner@rent.com · owner123</div>
                </div>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;margin-left:auto;color:var(--muted-foreground)"><polyline points="9 18 15 12 9 6"/></svg>
            </div>
            <div class="demo-row" onclick="fillLogin('renter@rent.com','renter123')">
                <span class="role-badge role-renter">Renter</span>
                <div>
                    <div style="font-weight:600; font-size:0.9rem;">Jane Renter</div>
                    <div class="text-muted" style="font-size:0.8rem;">renter@rent.com · renter123</div>
                </div>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;margin-left:auto;color:var(--muted-foreground)"><polyline points="9 18 15 12 9 6"/></svg>
            </div>
            <div class="demo-row" onclick="fillLogin('admin@rent.com','admin123')">
                <span class="role-badge role-admin">Admin</span>
                <div>
                    <div style="font-weight:600; font-size:0.9rem;">Admin User</div>
                    <div class="text-muted" style="font-size:0.8rem;">admin@rent.com · admin123</div>
                </div>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;margin-left:auto;color:var(--muted-foreground)"><polyline points="9 18 15 12 9 6"/></svg>
            </div>
        </div>

        <!-- Auth Form Card -->
        <div class="auth-card">
            <h2 style="margin-bottom:4px;">Welcome to RentSmart</h2>
            <p class="text-muted" style="margin-bottom:24px; font-size:0.95rem;">Login or create a new account</p>

            <!-- Tabs -->
            <div class="tab-bar">
                <button class="tab-btn active" id="loginTab" onclick="setMode('login')">Login</button>
                <button class="tab-btn" id="registerTab" onclick="setMode('register')">Register</button>
            </div>

            <form id="authForm">
                <div class="form-group" id="nameField" style="display:none;">
                    <label class="form-label">Full Name</label>
                    <input type="text" id="authName" class="form-input" placeholder="Your name">
                </div>

                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <input type="email" id="authEmail" class="form-input" required placeholder="you@example.com" autocomplete="email">
                </div>

                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" id="authPassword" class="form-input" required placeholder="••••••••" autocomplete="current-password">
                </div>

                <div class="form-group" id="roleField" style="display:none;">
                    <label class="form-label">Account Type</label>
                    <select id="authRole" class="form-select">
                        <option value="renter">Renter — browse &amp; book items</option>
                        <option value="owner">Owner — list items for rent</option>
                    </select>
                </div>

                <div id="authError" class="form-error" style="display:none;"></div>

                <button type="submit" class="btn btn-primary" style="width:100%; margin-top:12px; font-size:1rem; padding:14px;" id="authBtn">
                    Login
                </button>
            </form>
        </div>
    </div>
</div>

<script>
var mode = 'login';

function setMode(m) {
    mode = m;
    const isReg = m === 'register';
    document.getElementById('loginTab').classList.toggle('active', !isReg);
    document.getElementById('registerTab').classList.toggle('active', isReg);
    document.getElementById('nameField').style.display = isReg ? '' : 'none';
    document.getElementById('roleField').style.display = isReg ? '' : 'none';
    document.getElementById('authBtn').textContent     = isReg ? 'Create Account' : 'Login';
    document.getElementById('authError').style.display = 'none';
}

function fillLogin(email, pass) {
    document.getElementById('authEmail').value    = email;
    document.getElementById('authPassword').value = pass;
    setMode('login');
}

document.getElementById('authForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = document.getElementById('authBtn');
    const err = document.getElementById('authError');
    err.style.display = 'none';
    btn.disabled = true; btn.textContent = 'Please wait…';

    const payload = { action: mode, email: document.getElementById('authEmail').value, password: document.getElementById('authPassword').value };
    if (mode === 'register') { payload.name = document.getElementById('authName').value; payload.role = document.getElementById('authRole').value; }

    const res = await App.api('/auth.php', { method: 'POST', body: JSON.stringify(payload) });

    btn.disabled = false; btn.textContent = mode === 'login' ? 'Login' : 'Create Account';

    if (res.error) {
        err.textContent = res.error; err.style.display = '';
    } else {
        App.toast('Welcome, ' + res.data.user.name + '! 👋');
        setTimeout(() => window.location.href = '/index.php', 900);
    }
});
</script>

<?php include 'includes/footer.php'; ?>
