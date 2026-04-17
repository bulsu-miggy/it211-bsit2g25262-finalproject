<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Lasa Filipina</title>
  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Inter', sans-serif;
      background-color: rgba(255, 255, 255, 0.3);
      min-height: 100vh;
    }
    
   
    .bg-orange-custom { background-color:  #422006; }
    .bg-yellow-deep { background-color: #422006; }
    .bg-yellow-dark { background-color: #854d0e; }
    .text-orange-custom { color: #fed7aa; }
    .text-yellow-deep { color: #422006; }
    
    .btn-login {
      background-color: #fed7aa;
      color: #422006;
      border-radius: 9999px;
      padding: 8px 24px;
      font-weight: bold;
      transition: all 0.3s ease;
      border: none;
    }
    
    .btn-login:hover {
      background-color: #fec887;
    }
    
    .btn-signup {
      background-color: #854d0e;
      color: #fed7aa;
      border-radius: 9999px;
      padding: 8px 24px;
      font-weight: bold;
      transition: all 0.3s ease;
      border: none;
    }
    
    .btn-signup:hover {
      background-color: #713f12;
    }
    
    .form-input {
      background-color: #fed7aa;
      color: #422006;
      border: none;
      border-radius: 9999px;
      padding: 12px 20px;
      width: 100%;
    }
    
    .form-input:focus {
      outline: none;
      box-shadow: 0 0 0 2px #fec887;
      background-color: #fed7aa;
    }
    
    .form-input::placeholder {
      color: #854d0e;
    }
    
    /* Checkbox styling */
    .checkbox-custom {
      width: 18px;
      height: 18px;
      cursor: pointer;
      accent-color: #fed7aa;
    }
    
    .terms-link {
      color: #fed7aa;
      text-decoration: underline;
      cursor: pointer;
      font-weight: 600;
      transition: all 0.3s ease;
    }
    
    .terms-link:hover {
      color: white;
    }
    
    /* Modal styling */
    .terms-modal .modal-content,
    .forgot-modal .modal-content {
      border-radius: 20px;
      max-height: 80vh;
      background-color: #fffdfa;
      border: none;
    }
    
    .terms-modal .modal-body,
    .forgot-modal .modal-body {
      max-height: 60vh;
      overflow-y: auto;
      padding: 24px;
    }
    
    .terms-modal .modal-header,
    .forgot-modal .modal-header {
      background: linear-gradient(135deg, #fed7aa, #fec887);
      border-radius: 20px 20px 0 0;
      border-bottom: none;
    }
    
    .terms-modal .modal-title,
    .forgot-modal .modal-title {
      color: #422006;
      font-weight: bold;
    }

    .forgot-modal .modal-body {
      padding-top: 1.5rem;
    }

    .forgot-modal .form-label {
      color: #422006;
      font-weight: 600;
    }

    .forgot-modal .form-input {
      background-color: #fff8f1;
      color: #422006;
      border: 1px solid #f2d9b3;
    }

    .forgot-modal .modal-footer {
      background: transparent;
      border-top: none;
      padding: 1rem 1.5rem 1.5rem;
    }

    .forgot-modal .btn-secondary {
      background-color: #854d0e;
      border-color: #854d0e;
      color: #fed7aa;
    }

    .forgot-modal .btn-secondary:hover {
      background-color: #713f12;
      border-color: #713f12;
    }

    .forgot-password-link a {
      color: #fed7aa;
      text-decoration: underline;
    }

    .forgot-password-link a:hover {
      color: #ffffff;
    }
    
    .terms-section {
      margin-bottom: 20px;
      padding-bottom: 15px;
      border-bottom: 1px solid #eee;
    }
    
    .terms-section h6 {
      color: #422006;
      font-weight: bold;
      margin-bottom: 10px;
    }
    
    .terms-section p, .terms-section li {
      color: #555;
      font-size: 14px;
      line-height: 1.6;
    }
    
    /* Fixed Navigation Bar */
    .navbar-custom {
      width: 100%;
      max-width: 100%;
      margin: 0 auto;
      padding: 16px 24px;
      background: rgba(255, 248, 240, 0.98);
      border-radius: 25px;
      box-shadow: 0 2px 20px rgba(0, 0, 0, 0.05);
    }
    
    .navbar-inner {
      display: flex;
      align-items: center;
      justify-content: space-between;
      flex-wrap: wrap;
      gap: 1rem;
    }
    
    .navbar-brand-custom {
      font-family: 'Times New Roman', serif;
      font-size: 1.6rem;
      font-weight: 700;
      color: #2f241b;
      text-decoration: none;
      transition: transform 0.3s ease;
    }
    
    .navbar-brand-custom:hover {
      transform: scale(1.02);
      color: #bc6f3b;
    }
    
    .nav-links-custom {
      display: flex;
      gap: 1.5rem;
      margin: 0;
      padding: 0;
      list-style: none;
    }
    
    .nav-links-custom a {
      text-decoration: none;
      color: #2f241b;
      font-size: 1rem;
      font-weight: 500;
      transition: color 0.3s ease;
      padding: 0.5rem 1rem;
      border-radius: 12px;
    }
    
    .nav-links-custom a:hover {
      color: #bc6f3b;
      background-color: rgba(188, 111, 59, 0.1);
    }
    
    .nav-links-custom a.active {
      background-color: #bc6f3b;
      color: white;
    }
    
    .navbar-actions {
      display: flex;
      align-items: center;
      gap: 1rem;
      flex-wrap: wrap;
    }
    
    .cart-icon-btn {
      background: none;
      border: none;
      font-size: 1.25rem;
      color: #bc6f3b;
      cursor: pointer;
      padding: 0.6rem;
      text-decoration: none;
      position: relative;
      display: inline-flex;
      align-items: center;
      transition: transform 0.2s;
    }
    
    .cart-icon-btn:hover {
      transform: scale(1.05);
    }
    
    .cart-count {
      position: absolute;
      top: -5px;
      right: -8px;
      background-color: #dc3545;
      color: white;
      border-radius: 50%;
      padding: 0.15rem 0.45rem;
      font-size: 0.7rem;
      font-weight: 700;
      min-width: 1.2rem;
      text-align: center;
    }
    
    .since-badge {
      font-size: 0.75rem;
      letter-spacing: 0.12rem;
      text-transform: uppercase;
      color: #8b735b;
      font-weight: 600;
    }
    
    .avatar-icon {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      overflow: hidden;
      cursor: pointer;
      transition: transform 0.3s ease;
      background: #f0e2d6;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    
    .avatar-icon:hover {
      transform: scale(1.05);
    }

    .avatar-icon.dropdown-toggle {
      border: none;
      background: none;
      padding: 0;
      width: 40px;
      height: 40px;
    }

    .avatar-icon.dropdown-toggle::after {
      display: none;
    }

    .dropdown {
      position: relative;
    }

    .dropdown-menu {
      z-index: 2000;
      min-width: 160px;
    }

    .avatar-img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }
    
    .main-container {
      width: 100%;
      max-width: 100%;
      margin: 0 auto;
      padding: 2rem 1rem 3rem;
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: calc(100vh - 130px);
    }
    
    .hero-wrapper {
      width: 100%;
      background: #fffdfa;
      border-radius: 24px;
      overflow: hidden;
      box-shadow: 0 30px 80px rgba(0, 0, 0, 0.12);
      position: relative;
      display: flex;
      flex-wrap: wrap;
      align-items: stretch;
      min-height: 600px;
    }
    
    .hero-wrapper .row {
      min-height: 100%;
      width: 100%;
    }
    
    .hero-left-img {
      width: 100%;
      max-width: 520px;
      height: auto;
      object-fit: contain;
      border-radius: 0 0 0 24px;
    }
    
    .hero-side {
      background: #fff7ef;
      position: relative;
      z-index: 1;
      color: #2f241b;
    }
    
    /* Disabled button styling */
    .btn-login:disabled {
      opacity: 0.5;
      cursor: not-allowed;
    }
    
    @media (max-width: 768px) {
      .navbar-custom {
        padding: 16px 20px;
        flex-direction: column;
        gap: 15px;
      }
      .nav-links {
        gap: 20px;
        flex-wrap: wrap;
        justify-content: center;
      }
      .right-section {
        margin-top: 10px;
      }
      .hero-wrapper {
        min-height: auto;
        max-height: none;
        border-radius: 16px;
      }
      .hero-wrapper .row {
        min-height: auto;
      }
      .hero-left-img {
        max-width: 320px;
      }
      .hero-side {
        padding-top: 1rem;
      }
    }
  </style>
</head>
<body>

  <!-- Main Content -->
  <div class="main-container">
    <div class="hero-wrapper mt-4">
      
      <!-- Background Image -->
      <img 
        class="position-absolute w-100 h-100 object-fit-cover opacity-30" 
        style="object-fit: cover; opacity: 0.3; inset: 0;"
        src="../images/LANDING PAGE.jpg"
        alt="Restaurant background"
      />
      
      <div class="row g-0 position-relative" style="min-height: 600px;">
        
        <!-- Left Side - Decorative Image -->
        <div class="col-12 col-lg-6 d-flex align-items-center justify-content-center p-4 p-md-5">
          <img 
            class="rounded-circle hero-left-img"
            src="../images/logi.png"
            alt="Logo"
          />
        </div>

        <!-- Right Side - Auth Card -->
        <div class="col-12 col-lg-6 p-4 p-md-5 d-flex flex-column align-items-center justify-content-center hero-side" style="background-color: #422006;">
          
          <h1 class="text-orange-custom text-center mb-2 fw-extrabold" style="font-size: 2rem; font-weight: 800;">
            MABUHAY, DEAR CUSTOMERS!
          </h1>
          <p class="text-orange-custom text-center mb-4" style="font-size: 1.5rem;">
            Sign up or log in to continue
          </p>

          <!-- Auth Toggle Buttons -->
          <div class="d-flex gap-3 mb-4">
            <button 
              onclick="setAuthMode('login')"
              id="loginBtn"
              class="btn-login"
            >
              LOG IN
            </button>
            <button 
              onclick="setAuthMode('signup')"
              id="signupBtn"
              class="btn-signup"
            >
              SIGN UP
            </button>
          </div>

          <!-- Form -->
          <div class="w-100" style="max-width: 400px;">
            <div id="fullNameField" class="d-none mb-3">
              <input 
                type="text" 
                placeholder="Full Name"
                class="form-input"
                id="fullName"
              />
            </div>
            <div class="mb-3">
              <input 
                type="email" 
                placeholder="Email Address"
                class="form-input"
                id="email"
              />
            </div>
            <div class="mb-3">
              <input 
                type="password" 
                placeholder="Password"
                class="form-input"
                id="password"
              />
            </div>
            <div id="forgotPasswordLink" class="text-end mb-3 d-none">
              <a href="#" onclick="showForgotPasswordModal(); return false;" class="text-orange-custom" style="text-decoration: underline;">
                Forgot password?
              </a>
            </div>
            <div id="confirmPasswordField" class="d-none mb-3">
              <input 
                type="password" 
                placeholder="Confirm Password"
                class="form-input"
                id="confirmPassword"
              />
            </div>
            
            <!-- Checkbox and Terms & Conditions -->
            <div class="mb-4 d-flex align-items-center gap-2">
              <input 
                type="checkbox" 
                id="termsCheckbox" 
                class="checkbox-custom"
                onchange="toggleSubmitButton()"
              />
              <label for="termsCheckbox" class="text-white small mb-0">
                I agree to the 
                <span class="terms-link" onclick="showTermsModal()">Terms and Conditions</span>
              </label>
            </div>
            
           <!-- Update your button - remove the <a> tag wrapper -->
<button id="submitBtn" class="btn-login w-100 py-2 fw-bold" disabled>
    LOG IN
</button>
          </div>

          <!-- Terms text (updated with checkbox) -->
          <p class="text-white-50 text-center small mt-4">
            <i class="bi bi-shield-check me-1"></i>
            By signing up, you agree to our 
            <span class="terms-link" onclick="showTermsModal()" style="color: #fed7aa;">Terms and Conditions</span> 
            and Privacy Policy.
          </p>
        </div>
      </div>
    </div>
  </div>

  <!-- Forgot Password Modal -->
  <div class="modal fade forgot-modal" id="forgotPasswordModal" tabindex="-1" aria-labelledby="forgotPasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="forgotPasswordModalLabel">Reset Password</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="resetEmail" class="form-label">Email Address</label>
            <input type="email" id="resetEmail" class="form-input" placeholder="Enter your email" />
          </div>
          <div class="mb-3">
            <label for="resetPassword" class="form-label">New Password</label>
            <input type="password" id="resetPassword" class="form-input" placeholder="Enter new password" />
          </div>
          <div class="mb-3">
            <label for="resetConfirmPassword" class="form-label">Confirm Password</label>
            <input type="password" id="resetConfirmPassword" class="form-input" placeholder="Confirm new password" />
          </div>
        </div>
        <div class="modal-footer">
          <!-- <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button> -->
          <button type="button" class="btn btn-login w-100" onclick="resetPassword()">Reset Password</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Terms & Conditions Modal -->
  <div class="modal fade terms-modal" id="termsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">📋 Terms and Conditions</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="terms-section">
            <p><strong>Effective Date:</strong> April 5, 2026</p>
            <p><strong>App Name:</strong> La Casa Filipina</p>
            <p><strong>Developer:</strong> Group2 Inc.</p>
            <p>Welcome to La Casa Filipina. By downloading, installing, or using our app, you agree to these Terms and Conditions. If you do not agree, please do not use the app.</p>
          </div>

          <div class="terms-section">
            <h6>1. Use of the App</h6>
            <p>La Casa Filipina is intended for personal, non-commercial use. Users must be at least 13 years old. You agree to use the app in compliance with all applicable laws and regulations.</p>
          </div>

          <div class="terms-section">
            <h6>2. Account Registration</h6>
            <p>Some features may require creating an account. You are responsible for keeping your account credentials confidential. You must provide accurate and up-to-date information when registering.</p>
          </div>

          <div class="terms-section">
            <h6>3. Intellectual Property</h6>
            <p>All content, designs, logos, and materials in La Casa Filipina are owned by Group2 Inc. or its licensors. You may not copy, distribute, modify, or create derivative works without written permission.</p>
          </div>

          <div class="terms-section">
            <h6>4. User Content</h6>
            <p>Users may submit content such as reviews or comments. By submitting content, you grant Group2 Inc. a non-exclusive, worldwide license to use, display, and modify your content. You are solely responsible for any content you provide.</p>
          </div>

          <div class="terms-section">
            <h6>5. Privacy</h6>
            <p>Your use of the app is subject to our Privacy Policy, which explains how we collect, use, and protect your data.</p>
          </div>

          <div class="terms-section">
            <h6>6. Prohibited Activities</h6>
            <p>You agree not to:</p>
            <ul>
              <li>Hack, reverse engineer, or interfere with the app.</li>
              <li>Use the app to transmit illegal, harmful, or offensive material.</li>
              <li>Impersonate others or violate anyone's rights.</li>
            </ul>
          </div>

          <div class="terms-section">
            <h6>7. Payments and Subscriptions (if applicable)</h6>
            <p>Any paid features or in-app purchases are final unless otherwise stated. You authorize Group2 Inc. to charge your chosen payment method for fees. Refunds are at the discretion of Group2 Inc.</p>
          </div>

          <div class="terms-section">
            <h6>8. Limitation of Liability</h6>
            <p>The app is provided "as is" without warranties of any kind. Group2 Inc. is not liable for any damages, loss of data, or other consequences from your use of the app.</p>
          </div>

          <div class="terms-section">
            <h6>9. Termination</h6>
            <p>Group2 Inc. may suspend or terminate your access for violating these terms. You may uninstall or stop using the app at any time.</p>
          </div>

          <div class="terms-section">
            <h6>10. Changes to Terms</h6>
            <p>Group2 Inc. may update these Terms at any time. Continued use of La Casa Filipina constitutes acceptance of the updated terms.</p>
          </div>

          <div class="terms-section">
            <h6>11. Governing Law</h6>
            <p>These Terms are governed by the laws of the Philippines. Any disputes will be resolved in the courts of the nation.</p>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-login" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  <script>
    let currentMode = 'login';

    function setAuthMode(mode) {
        // ... keep your existing mode switching code ...
        currentMode = mode;
        const loginBtn = document.getElementById('loginBtn');
        const signupBtn = document.getElementById('signupBtn');
        const fullNameField = document.getElementById('fullNameField');
        const confirmPasswordField = document.getElementById('confirmPasswordField');
        const forgotPasswordLink = document.getElementById('forgotPasswordLink');
        const submitBtn = document.getElementById('submitBtn');

        if (mode === 'login') {
            loginBtn.className = 'btn-login';
            signupBtn.className = 'btn-signup';
            fullNameField.classList.add('d-none');
            confirmPasswordField.classList.add('d-none');
            forgotPasswordLink.classList.remove('d-none');
            submitBtn.textContent = 'LOG IN';
        } else {
            loginBtn.className = 'btn-signup';
            signupBtn.className = 'btn-login';
            fullNameField.classList.remove('d-none');
            confirmPasswordField.classList.remove('d-none');
            forgotPasswordLink.classList.add('d-none');
            submitBtn.textContent = 'CREATE ACCOUNT';
        }
        
        const termsCheckbox = document.getElementById('termsCheckbox');
        if (termsCheckbox) {
            termsCheckbox.checked = true;
            toggleSubmitButton();
        }
    }

    // Initialize login mode on page load so the forgot password link is visible
    setAuthMode('login');

    function toggleSubmitButton() {
        const checkbox = document.getElementById('termsCheckbox');
        const submitBtn = document.getElementById('submitBtn');
        submitBtn.disabled = !checkbox.checked;
    }

    function showTermsModal() {
        const modal = new bootstrap.Modal(document.getElementById('termsModal'));
        modal.show();
    }

    function showForgotPasswordModal() {
        const modal = new bootstrap.Modal(document.getElementById('forgotPasswordModal'));
        modal.show();
    }

    async function resetPassword() {
        const email = document.getElementById('resetEmail').value.trim();
        const password = document.getElementById('resetPassword').value;
        const confirmPassword = document.getElementById('resetConfirmPassword').value;

        if (!email || !password || !confirmPassword) {
            alert('Please fill in all fields.');
            return;
        }

        if (password !== confirmPassword) {
            alert('Passwords do not match.');
            return;
        }

        if (password.length < 6) {
            alert('Password must be at least 6 characters.');
            return;
        }

        try {
            const response = await fetch('/finalproject/action/reset_password.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    email: email,
                    password: password,
                    confirmPassword: confirmPassword
                })
            });

            if (!response.ok) {
                alert('Reset request failed.');
                return;
            }

            const result = await response.json();
            if (result.success) {
                alert('Password changed successfully. Please log in with your new password.');
                const modal = bootstrap.Modal.getInstance(document.getElementById('forgotPasswordModal'));
                modal.hide();
                setAuthMode('login');
            } else {
                alert(result.message || 'Password reset failed.');
            }
        } catch (error) {
            console.error(error);
            alert('Unable to reset password. Please try again later.');
        }
    }

    // REAL LOGIN HANDLER with AJAX
    document.getElementById('submitBtn').addEventListener('click', async function(e) {
        e.preventDefault();
        
        const checkbox = document.getElementById('termsCheckbox');
        if (!checkbox.checked) {
            alert('Please agree to the Terms and Conditions to continue.');
            return;
        }
        
        if (currentMode === 'login') {
            // LOGIN FLOW - Real data submission
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            
            if (!email || !password) {
                alert('Please enter your email and password.');
                return;
            }
            
            // Disable button to prevent double submission
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.disabled = true;
            submitBtn.textContent = 'LOGGING IN...';
            
            try {
                // Send data to backend
                const response = await fetch('/finalproject/action/tologin.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        email: email,
                        password: password
                    }),
                    signal: AbortSignal.timeout(10000) // 10 second timeout
                });

                // Get response text once
                const responseText = await response.text();

                if (!response.ok) {
                    alert('Login failed: ' + response.status + ' ' + response.statusText + '\n' + responseText);
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'LOG IN';
                    return;
                }

                let result;
                try {
                    result = JSON.parse(responseText);
                } catch (parseError) {
                    alert('Login error: invalid server response. ' + responseText);
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'LOG IN';
                    return;
                }

                if (result.success) {
                    // Login successful - redirect to home.php
                    window.location.href = result.redirect || 'home.php';
                } else {
                    // Login failed - show error
                    alert(result.message);
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'LOG IN';
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Network error: ' + error.message + '. Please check if the server is running and try again.');
                submitBtn.disabled = false;
                submitBtn.textContent = 'LOG IN';
            }
            
        } else {
            // SIGNUP FLOW - similar implementation for signup.php
            const fullName = document.getElementById('fullName')?.value;
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirmPassword')?.value;
            
            if (!fullName || !email || !password || !confirmPassword) {
                alert('Please fill in all fields.');
                return;
            }
            
            if (password !== confirmPassword) {
                alert('Passwords do not match.');
                return;
            }
            
            // Send to signup.php (create this file similarly)
            try {
                const response = await fetch('/finalproject/action/signup.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        fullName: fullName,
                        email: email,
                        password: password
                    })
                });

                if (!response.ok) {
                    const errorText = await response.text();
                    alert('Signup failed: ' + response.status + ' ' + response.statusText + '\n' + errorText);
                    return;
                }

                const result = await response.json();
                
                if (result.success) {
                    alert('Account created successfully! Please log in.');
                    setAuthMode('login');
                } else {
                    alert(result.message);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Network error. Please try again.');
            }
        }
    });
</script>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>