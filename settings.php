<?php
session_start();
require_once 'php/db.php';

// Get user data - fetch from session user or default to user ID 1
$user_name = 'Demo User';
$user_email = 'demo@teamly.com';
$user_workspace = 'Semester Project Team';

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1;

if ($conn) {
    $stmt = $conn->prepare("SELECT name, email FROM users WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $user_name = $user['name'];
            $user_email = $user['email'];
        }
        $stmt->close();
    }
}
?><!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Settings — Teamly</title><link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet"><link rel="stylesheet" href="assets/css/home.css"></head><body><header class="glass-header">
  <a href="index.html" class="brand"><span class="brand-mark">T</span><span>Teamly</span></a>
  <nav class="nav-menu" id="navMenu">
    <a href="index.html">Home</a><a href="features.html">Features</a><a href="integrations.html">Integrations</a><a href="pricing.html">Pricing</a><a href="about.html">About</a>
  </nav>
  <div class="nav-actions"><a href="login.html" class="btn btn-light">Login</a><a href="signup.html" class="btn btn-dark">Start free</a><button class="hamburger" id="hamburger" aria-label="Open menu"><span></span><span></span><span></span></button></div>
</header><main><section class="page-hero section-shell"><span class="eyebrow">Settings</span><h1>Manage your Teamly profile</h1><p>Simple profile settings page for semester project completeness.</p></section><section class="form-page"><form class="auth-card" id="settingsForm"><h1>Profile settings</h1><div id="message" style="display: none; padding: 10px; margin-bottom: 15px; border-radius: 4px; font-size: 14px;"></div><div class="field"><label>Full name</label><input type="text" name="name" id="fullName" value="<?php echo htmlspecialchars($user_name); ?>" required></div><div class="field"><label>Email</label><input type="email" name="email" id="userEmail" value="<?php echo htmlspecialchars($user_email); ?>" required></div><div class="field"><label>Workspace</label><input type="text" name="workspace" id="workspace" value="<?php echo htmlspecialchars($user_workspace); ?>" placeholder="Your workspace name"></div><button class="btn btn-dark" type="submit" id="saveBtn">Save changes</button><p id="loadingText" style="display: none; margin-top: 10px; color: #666; font-size: 14px;">Saving...</p></form></section></main><footer class="creative-footer">
  <div class="footer-glow"></div>
  <div class="footer-top">
    <div class="footer-brand">
      <a href="index.html" class="brand footer-brand-logo"><span class="brand-mark">T</span><span>Teamly</span></a>
      <p>A ClickUp-style productivity operating system for students and teams. Built using HTML, CSS, JavaScript, jQuery, PHP, MySQL and XAMPP.</p>
      <div class="socials"><a>in</a><a>gh</a><a>dr</a><a>tw</a></div>
    </div>
    <div class="footer-links">
      <div><h4>Product</h4><a href="features.html">Features</a><a href="dashboard.php">Dashboard</a><a href="tasks.php">Tasks</a><a href="notes.php">Notes</a></div>
      <div><h4>Workspace</h4><a href="calendar.html">Calendar</a><a href="integrations.html">Integrations</a><a href="settings.php">Settings</a><a href="pricing.html">Pricing</a></div>
      <div><h4>Account</h4><a href="login.html">Login</a><a href="signup.html">Signup</a><a href="about.html">About</a><a href="#">Support</a></div>
    </div>
  </div>
  <div class="footer-bottom"><span>© 2026 Teamly Semester Project</span><span>Designed for productivity, focus and presentation.</span></div>
</footer><script src="https://code.jquery.com/jquery-3.7.1.min.js"></script><script src="assets/js/home.js"></script><script>
// Handle form submission
document.addEventListener('DOMContentLoaded', function() {
    // Handle form submission
    document.getElementById('settingsForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const name = document.getElementById('fullName').value.trim();
        const email = document.getElementById('userEmail').value.trim();
        const workspace = document.getElementById('workspace').value.trim();
        
        // Validation
        if (!name || !email) {
            showMessage('Name and email are required', false);
            return;
        }
        
        if (!validateEmail(email)) {
            showMessage('Invalid email format', false);
            return;
        }
        
        // Show loading state
        document.getElementById('saveBtn').disabled = true;
        document.getElementById('loadingText').style.display = 'block';
        
        // Submit form
        const formData = new FormData();
        formData.append('name', name);
        formData.append('email', email);
        formData.append('workspace', workspace);
        
        fetch('php/api/update_settings.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            document.getElementById('saveBtn').disabled = false;
            document.getElementById('loadingText').style.display = 'none';
            
            if (data.success) {
                showMessage('✓ Settings saved successfully!', true);
                // Refresh page after delay
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                showMessage('✗ ' + (data.message || 'Error saving settings'), false);
            }
        })
        .catch(error => {
            document.getElementById('saveBtn').disabled = false;
            document.getElementById('loadingText').style.display = 'none';
            console.error('Error:', error);
            showMessage('✗ Network error. Please try again.', false);
        });
    });
    
    function showMessage(message, isSuccess) {
        const msgDiv = document.getElementById('message');
        msgDiv.textContent = message;
        msgDiv.style.display = 'block';
        msgDiv.style.backgroundColor = isSuccess ? '#d4edda' : '#f8d7da';
        msgDiv.style.color = isSuccess ? '#155724' : '#721c24';
        msgDiv.style.border = '1px solid ' + (isSuccess ? '#c3e6cb' : '#f5c6cb');
        
        if (!isSuccess) {
            setTimeout(() => {
                msgDiv.style.display = 'none';
            }, 5000);
        }
    }
    
    function validateEmail(email) {
        const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return regex.test(email);
    }
});
</script></body></html>
