<?php
session_start();
if (!isset($_SESSION["username"]) || !isset($_SESSION["password"])) {
  header('Location: login.php');
  exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>LYNX - Profile</title>
    <link rel="stylesheet" href="css/login.css">
    <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@400;700&family=Rubik+Mono+One&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <style>
      .main {
        min-height: calc(100vh - 80px);
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 40px 20px;
        background-color: #f0f2f5;
      }
      .profile-container {
          width: 500px;
      }
      .section {
        background: white;
        padding: 30px 40px;
        border-radius: 15px;
        box-shadow: 0 4px 40px rgba(0,0,0,0.2);
        margin-bottom: 30px;
      }
      
      .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 16px;
        text-align: center;
      }
      .section-header h2 {
        font-family: 'Rubik Mono One', sans-serif;
        font-size: 24px;
        margin: 0 auto;
        color: #000;
      }
      .section-header .material-symbols-outlined {
        cursor: pointer;
        color: #606770;
        font-size: 28px;
      }
      .edit-btn-group {
        display: none;
        gap: 10px;
      }
      .edit-btn-group.show {
        display: flex;
      }
      .edit-btn-group button {
        padding: 8px 16px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-weight: bold;
        font-size: 14px;
      }
      .save-btn {
        background: black;
        color: white;
      }
      .cancel-btn {
        background: #e9ebee;
        color: black;
      }
      .form-input {
        display: none;
        padding: 8px 12px;
        border: 2px solid #ddd;
        border-radius: 8px;
        font-size: 14px;
        width: 100%;
      }
      .form-input.show {
        display: block;
      }
      .info-value.hidden {
        display: none;
      }
      
      .info-item, .address-item {
        display: flex;
        align-items: flex-start;
        padding: 16px 0;
        border-bottom: 1px solid #e9ebee;
        gap: 20px;
        justify-content: space-between;
      }
      .info-item:last-child, .address-item:last-child {
        border-bottom: none;
      }
      
      .info-label { font-weight: bold; font-size: 14px; color: #606770; width: 150px; flex-shrink: 0; }
      .info-value { font-size: 14px; color: #1c1e21; }
      .form-input { width: 100%; max-width: 300px; }

    </style>
</head>
<body>
  <header class="header">
    <a href="index.php" style="text-decoration: none; color: black;"><h1 class="logo">LYNX</h1></a>
<nav class="nav" style="display: none;">
      <a href="women.php">WOMEN</a>
      <a href="men.php">MEN</a>
      <a href="addproduct.php">ADD PRODUCT</a>
    </nav>
    <div class="icons">
        <span class="material-symbols-outlined" onclick="openSearchModal()" style="cursor: pointer; transition: all 0.3s;" title="Search">search</span>
        <span class="material-symbols-outlined">shopping_cart</span>
        <a href="profiles.php" title="Profile" style="color: black; text-decoration: none;">
            <span class="material-symbols-outlined">account_circle</span>
        </a>
        <a href="#" id="logout-trigger" style="color: black; text-decoration: none;">
            <span class="material-symbols-outlined">logout</span>
        </a>
    </div>
  </header>

  <!-- SEARCH MODAL -->
  <div id="searchModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 1000; animation: fadeIn 0.3s ease;">
    <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 40px; border-radius: 15px; width: 90%; max-width: 600px; box-shadow: 0 10px 50px rgba(0,0,0,0.3); animation: slideUp 0.3s ease;">
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2 style="font-family: 'Rubik Mono One', sans-serif; font-size: 1.8rem; margin: 0; color: black;">SEARCH</h2>
        <span class="material-symbols-outlined" onclick="closeSearchModal()" style="cursor: pointer; font-size: 28px; color: #666;">close</span>
      </div>
      <form id="searchForm" onsubmit="performSearch(event)" style="display: flex; gap: 10px;">
        <input type="text" id="searchInput" placeholder="Search products..." style="flex: 1; padding: 15px 20px; border: 2px solid #ddd; border-radius: 8px; font-size: 1rem; font-family: 'Rubik', sans-serif;" autofocus>
        <button type="submit" style="padding: 15px 30px; background: black; color: white; border: none; border-radius: 8px; cursor: pointer; font-family: 'Rubik', sans-serif; font-weight: bold; transition: all 0.3s; white-space: nowrap;">SEARCH</button>
      </form>
    </div>
  </div>

  <style>
    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }
    @keyframes slideUp {
      from { transform: translate(-50%, -40%); opacity: 0; }
      to { transform: translate(-50%, -50%); opacity: 1; }
    }
  </style>

  <main class="main">
    <div class="profile-container">
      <!-- Profile Section -->
      <div class="section">
          <div class="section-header">
              <h2>PROFILE</h2>
              <div style="display: flex; gap: 10px; align-items: center;">
                <span class="material-symbols-outlined" id="profileEditBtn" onclick="toggleProfileEdit()">edit</span>
                <div class="edit-btn-group" id="profileBtnGroup">
                  <button class="save-btn" onclick="saveProfileChanges()">Save</button>
                  <button class="cancel-btn" onclick="cancelProfileEdit()">Cancel</button>
                </div>
              </div>
          </div>
          <div class="info-item">
              <div class="info-label">USERNAME</div>
              <div style="flex-grow: 1;">
                <div class="info-value" id="profile-username"><?php echo htmlspecialchars($_SESSION['username'] ?? ''); ?></div>
                <input type="text" class="form-input" id="profileUsernameInput" value="<?php echo htmlspecialchars($_SESSION['username'] ?? ''); ?>">
              </div>
          </div>
          <div class="info-item">
              <div class="info-label">FIRST NAME</div>
              <div style="flex-grow: 1;">
                <div class="info-value" id="profile-firstName">Keith Alissa</div>
                <input type="text" class="form-input" id="profileFirstNameInput" value="Keith Alissa">
              </div>
          </div>
          <div class="info-item">
              <div class="info-label">LAST NAME</div>
              <div style="flex-grow: 1;">
                <div class="info-value" id="profile-lastName">Dela Cruz</div>
                <input type="text" class="form-input" id="profileLastNameInput" value="Dela Cruz">
              </div>
          </div>
          <div class="info-item">
              <div class="info-label">EMAIL</div>
              <div style="flex-grow: 1;">
                <div class="info-value" id="profile-email">user@gmail.com</div>
                <input type="email" class="form-input" id="profileEmailInput" value="user@gmail.com">
              </div>
          </div>

          <div class="info-item">
              <div class="info-label">PHONE</div>
              <div style="flex-grow: 1;">
                <div class="info-value" id="profile-phone">+63 9123456789</div>
                <input type="tel" class="form-input" id="profilePhoneInput" value="+63 9123456789">
              </div>
          </div>
          <div class="info-item">
              <div class="info-label">PASSWORD</div>
              <div style="flex-grow: 1;">
                <div class="info-value" id="profile-password">**************</div>
                <input type="password" class="form-input" id="profilePasswordInput" placeholder="Leave blank to keep current password">
              </div>
          </div>
      </div>

      <!-- Addresses Section -->
      <div class="section">
          <div class="section-header">
              <h2>ADDRESSES</h2>
              <span class="material-symbols-outlined">add</span>
          </div>
          <div class="address-item">
              <div style="flex-grow: 1;">
                <div class="info-label">STREET ADDRESS</div>
                <div class="info-value" id="address-street-display">7513 N Brad Road</div>
                <input type="text" class="form-input" id="address-street-input" value="7513 N Brad Road">
              </div>
          </div>
          <div class="address-item">
              <div style="flex-grow: 1;">
                <div class="info-label">POSTAL CODE</div>
                <div class="info-value" id="address-postal-display">48458</div>
                <input type="text" class="form-input" id="address-postal-input" value="48458">
              </div>
          </div>
          <div class="address-item">
              <div style="flex-grow: 1;">
                <div class="info-label">STATE/REGION</div>
                <div class="info-value" id="address-state-display">Michigan</div>
                <select class="form-input" id="address-state-input">
                  <option value="">Select State</option>
                  <option value="ncr" selected>NCR (Metro Manila)</option>
                  <option value="luzon">Luzon</option>
                  <option value="visayas">Visayas</option>
                  <option value="mindanao">Mindanao</option>
                </select>
              </div>
          </div>
          <div class="address-item">
              <div style="flex-grow: 1;">
                <div class="info-label">CITY</div>
                <div class="info-value" id="address-city-display">Mount Morris</div>
                <input type="text" class="form-input" id="address-city-input" value="Mount Morris">
              </div>
          </div>
          <div class="address-item">
              <div style="flex-grow: 1;">
                <div class="info-label">BARANGAY</div>
                <div class="info-value" id="address-barangay-display">-</div>
                <input type="text" class="form-input" id="address-barangay-input" value="">
              </div>
              <div style="display: flex; gap: 10px; flex-shrink: 0;">
                <span class="material-symbols-outlined" id="addressEditBtn" onclick="toggleAddressEdit()">edit</span>
                <div class="edit-btn-group" id="addressBtnGroup">
                  <button class="save-btn" onclick="saveAddressChanges()">Save</button>
                  <button class="cancel-btn" onclick="cancelAddressEdit()">Cancel</button>
                </div>
              </div>
          </div>
      </div>
    </div>
  </main>

  <script>
    let profileEditMode = false;
    let addressEditMode = false;

    function toggleProfileEdit() {
      profileEditMode = !profileEditMode;
      const fields = ['username', 'firstName', 'lastName', 'email', 'phone', 'password'];
      const btnGroup = document.getElementById('profileBtnGroup');
      const editBtn = document.getElementById('profileEditBtn');

      fields.forEach(field => {
        const display = document.getElementById('profile-' + field);
        const input = document.getElementById('profile' + field.charAt(0).toUpperCase() + field.slice(1) + 'Input');
        if (display && input) {
          if (profileEditMode) {
            display.classList.add('hidden');
            input.classList.add('show');
          } else {
            display.classList.remove('hidden');
            input.classList.remove('show');
          }
        }
      });

      if (profileEditMode) {
        btnGroup.classList.add('show');
        editBtn.style.display = 'none';
        document.getElementById('profileUsernameInput').focus();
      } else {
        cancelProfileEdit();
      }
    }


    function saveProfileChanges() {
      const username = document.getElementById('profileUsernameInput').value;
      const firstName = document.getElementById('profileFirstNameInput').value;
      const lastName = document.getElementById('profileLastNameInput').value;
      const email = document.getElementById('profileEmailInput').value;
      const phone = document.getElementById('profilePhoneInput').value;
      const password = document.getElementById('profilePasswordInput').value;

      if (!username || !firstName || !lastName || !email || !phone) {
        alert('Please fill in all required fields');
        return;
      }

      const formData = new FormData();
      formData.append('action', 'update');
      formData.append('username', username);
      formData.append('firstName', firstName);
      formData.append('lastName', lastName);
      formData.append('email', email);
      formData.append('phone', phone);
      if (password) {
        formData.append('password', password);
      }

      fetch('db/action/profile.php', {
        method: 'POST',
        body: formData
      }).then(response => response.text())
      .then(text => {

        try {
          const data = JSON.parse(text);

          if (data.success) {
            document.getElementById('profile-username').textContent = username;
            document.getElementById('profileUsernameInput').value = username;
            document.getElementById('profile-firstName').textContent = firstName;
            document.getElementById('profile-lastName').textContent = lastName;
            document.getElementById('profile-email').textContent = email;
            document.getElementById('profile-phone').textContent = phone;
            localStorage.setItem('userFirstName', firstName);
            localStorage.setItem('userLastName', lastName);
            localStorage.setItem('userEmail', email);
            localStorage.setItem('userPhone', phone);
            if (data.newUsername) {
              localStorage.setItem('userUsername', data.newUsername);
            }
            alert('Profile updated successfully');
            if (data.reload) {
              location.reload();
            } else {
              cancelProfileEdit();
            }
          } else {
            alert('Error updating profile: ' + (data.error || 'Unknown error'));
          }


        } catch (e) {

          console.error('JSON Parse Error:', e, text);
          alert('Server error - invalid response');
          return;
        }

        

      }).catch(error => {
        alert('Error: ' + error);
      });
    }


    function cancelProfileEdit() {
      profileEditMode = false;
      const fields = ['firstName', 'lastName', 'email', 'phone', 'password'];
      const btnGroup = document.getElementById('profileBtnGroup');
      const editBtn = document.getElementById('profileEditBtn');

      fields.forEach(field => {
        const display = document.getElementById('profile-' + field);
        const input = document.getElementById('profile' + field.charAt(0).toUpperCase() + field.slice(1) + 'Input');
        if (display && input) {
          display.classList.remove('hidden');
          input.classList.remove('show');
          if (field !== 'password') {
            input.value = display.textContent;
          } else {
            input.value = '';
          }
        }
      });

      btnGroup.classList.remove('show');
      editBtn.style.display = 'block';
    }

    function toggleAddressEdit() {
      addressEditMode = !addressEditMode;
      const fields = ['street', 'postal', 'state', 'city', 'barangay'];
      const btnGroup = document.getElementById('addressBtnGroup');
      const editBtn = document.getElementById('addressEditBtn');

      fields.forEach(field => {
        const display = document.getElementById('address-' + field + '-display');
        const input = document.getElementById('address-' + field + '-input');
        if (display && input) {
          if (addressEditMode) {
            display.classList.add('hidden');
            input.classList.add('show');
          } else {
            display.classList.remove('hidden');
            input.classList.remove('show');
          }
        }
      });

      if (addressEditMode) {
        btnGroup.classList.add('show');
        editBtn.style.display = 'none';
        document.getElementById('address-street-input').focus();
      } else {
        cancelAddressEdit();
      }
    }

    function saveAddressChanges() {
      const street = document.getElementById('address-street-input').value;
      const postal = document.getElementById('address-postal-input').value;
      const state = document.getElementById('address-state-input').value;
      const city = document.getElementById('address-city-input').value;
      const barangay = document.getElementById('address-barangay-input').value;

      if (!street || !postal || !state || !city) {
        alert('Please fill in all required address fields');
        return;
      }

      const formData = new FormData();
      formData.append('action', 'edit');
      formData.append('street', street);
      formData.append('postal', postal);
      formData.append('state', state);
      formData.append('city', city);
      formData.append('barangay', barangay);

fetch('db/action/manageaddress.php', {
        method: 'POST',
        body: formData
      }).then(response => response.text())
      .then(text => {
        try {
          const data = JSON.parse(text);
          // rest unchanged
        } catch (e) {
          console.error('JSON Parse Error:', e, text);
          alert('Server error - invalid response');
        }
      })
        .then(data => {
          if (data.success) {
            document.getElementById('address-street-display').textContent = street;
            document.getElementById('address-postal-display').textContent = postal;
            document.getElementById('address-state-display').textContent = state;
            document.getElementById('address-city-display').textContent = city;
            document.getElementById('address-barangay-display').textContent = barangay || '-';
            localStorage.setItem('userStreet', street);
            localStorage.setItem('userPostal', postal);
            localStorage.setItem('userState', state);
            localStorage.setItem('userCity', city);
            localStorage.setItem('userBarangay', barangay);
            alert('Address updated successfully');
            cancelAddressEdit();
          } else {
            alert('Error updating address: ' + (data.error || 'Unknown error'));
          }
        }).catch(error => {
          alert('Error: ' + error);
        });
    }

    function cancelAddressEdit() {
      addressEditMode = false;
      const fields = ['street', 'postal', 'state', 'city', 'barangay'];
      const btnGroup = document.getElementById('addressBtnGroup');
      const editBtn = document.getElementById('addressEditBtn');

      fields.forEach(field => {
        const display = document.getElementById('address-' + field + '-display');
        const input = document.getElementById('address-' + field + '-input');
        if (display && input) {
          display.classList.remove('hidden');
          input.classList.remove('show');
          input.value = display.textContent === '-' ? '' : display.textContent;
        }
      });

      btnGroup.classList.remove('show');
      editBtn.style.display = 'block';
    }

    // Load user data from backend
    document.addEventListener('DOMContentLoaded', () => {
      fetch('db/action/profile.php')
        .then(response => response.json())
        .then(data => {
          if (data && data.username) {
            // Update profile section
            const firstName = data.first_name || '';
            const lastName = data.last_name || '';
            const email = data.email || '';
            const phone = data.phone || '';
            
            document.getElementById('profile-firstName').textContent = firstName;
            document.getElementById('profileFirstNameInput').value = firstName;
            document.getElementById('profile-lastName').textContent = lastName;
            document.getElementById('profileLastNameInput').value = lastName;
            document.getElementById('profile-email').textContent = email;
            document.getElementById('profileEmailInput').value = email;
            document.getElementById('profile-phone').textContent = phone;
            document.getElementById('profilePhoneInput').value = phone;

            // Update address section
            const street = data.street || '7513 N Brad Road';
            const postal = data.postal_code || '48458';
            const state = data.state || 'ncr';
            const city = data.city || 'Mount Morris';
            const barangay = data.barangay || '';

            document.getElementById('address-street-display').textContent = street;
            document.getElementById('address-street-input').value = street;
            document.getElementById('address-postal-display').textContent = postal;
            document.getElementById('address-postal-input').value = postal;
            document.getElementById('address-state-display').textContent = state;
            document.getElementById('address-state-input').value = state;
            document.getElementById('address-city-display').textContent = city;
            document.getElementById('address-city-input').value = city;
            document.getElementById('address-barangay-display').textContent = barangay || '-';
            document.getElementById('address-barangay-input').value = barangay;

            // Store in localStorage for checkout
            localStorage.setItem('userFirstName', firstName);
            localStorage.setItem('userLastName', lastName);
            localStorage.setItem('userEmail', email);
            localStorage.setItem('userPhone', phone);
            localStorage.setItem('userStreet', street);
            localStorage.setItem('userPostal', postal);
            localStorage.setItem('userState', state);
            localStorage.setItem('userCity', city);
            localStorage.setItem('userBarangay', barangay);
          }
        }).catch(error => console.log('Could not load profile data:', error));
    });

    // Search Modal Functions
    function openSearchModal() {
      document.getElementById('searchModal').style.display = 'flex';
      document.getElementById('searchInput').focus();
    }

    function closeSearchModal() {
      document.getElementById('searchModal').style.display = 'none';
      document.getElementById('searchInput').value = '';
    }

    function performSearch(event) {
      event.preventDefault();
      const query = document.getElementById('searchInput').value.trim();
      if (query.length >= 2) {
        window.location.href = 'search.php?q=' + encodeURIComponent(query);
      } else {
        alert('Please enter at least 2 characters to search');
      }
    }

    // Close modal when clicking outside
    document.addEventListener('click', function(event) {
      const modal = document.getElementById('searchModal');
      if (event.target === modal) {
        closeSearchModal();
      }
    });

    // Allow closing modal with Escape key
    document.addEventListener('keydown', function(event) {
      if (event.key === 'Escape') {
        closeSearchModal();
      }
    });
  </script>

  <script>
  $(document).ready(function() {
    $('#logout-trigger').click(function(e) {
      e.preventDefault();
      Swal.fire({
        title: 'Are you sure?',
        text: "You will be logged out!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#000000',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, logout!'
      }).then((result) => {
        if (result.isConfirmed) {
          window.location.href = 'logout.php';
        }
      });
    });
  });
  </script>
  <script>
    $(document).ready(function() {
      $('#logout-trigger').click(function(e) {
        e.preventDefault();
        
        Swal.fire({
          title: 'Are you sure?',
          text: "You will be logged out!", 
          icon: 'warning', 
          showCancelButton: true, 
          confirmButtonColor: '#000000',
          cancelButtonColor: '#6c757d', 
          confirmButtonText: 'Yes, logout!'})
         .then((result) => {
          if (result.isConfirmed) {
            window.location.href = 'logout.php'; }
          }); 
      }); });
  </script><!-- Footer - Very Bottom -->
  <footer class="footer-banner" style="background: black; padding: 60px 20px; color: white; font-family: 'Rubik', sans-serif;">
    <div style="max-width: 1200px; margin: 0 auto; display: grid; grid-template-columns: 2fr 1fr 1fr 1fr; gap: 60px; align-items: start;">
      <div>
        <h1 style="font-family: 'Rubik Mono One', sans-serif; font-size: 3rem; font-weight: bold; letter-spacing: 2px; margin: 0; color: white;">LYNX</h1>
      </div>
      <div>
        <h3 style="font-weight: bold; font-size: 1.2rem; margin-bottom: 20px;">SHOP</h3>
        <ul style="list-style: none; padding: 0;">
          <li style="margin-bottom: 10px;"><a href="#" style="color: white; text-decoration: none;">MEN</a></li>
          <li style="margin-bottom: 10px;"><a href="#" style="color: white; text-decoration: none;">WOMEN</a></li>
        </ul>
      </div>
      <div>
        <h3 style="font-weight: bold; font-size: 1.2rem; margin-bottom: 20px;">COMPANY</h3>
        <ul style="list-style: none; padding: 0;">
          <li style="margin-bottom: 10px;"><a href="about.php" style="color: white; text-decoration: none;">ABOUT US</a></li>
        </ul>
      </div>
      <div style="text-align: right;">
        <h3 style="font-weight: bold; font-size: 1.2rem; margin-bottom: 20px;">BECOME A MEMBER</h3>
        <ul style="list-style: none; padding: 0;">
          <li style="margin-bottom: 10px;"><a href="register.php" style="color: white; text-decoration: none;">JOIN US</a></li>
        </ul>
      </div>
    </div>
  </footer>
</body>
</html>
