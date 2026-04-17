/**
 * js/profile.js
 * Handle tab switching, SweetAlert2 pop-ups, and jQuery AJAX updates
 */

/**
 * Manages the tabbed interface on the profile page.
 * @param {Event} evt - The trigger event
 * @param {string} tabName - The ID of the tab section to display
 */
function openTab(evt, tabName) {
    var i, tabcontent, navbtns;

    // 1. HIDE: Loop through all sections with class="tab-content"
    tabcontent = document.getElementsByClassName("tab-content");
    for (i = 0; i < tabcontent.length; i++) {
        tabcontent[i].style.display = "none";
        tabcontent[i].classList.remove("active");
    }

    // 2. RESET BUTTONS: Remove the "active" highlight
    navbtns = document.getElementsByClassName("nav-btn");
    for (i = 0; i < navbtns.length; i++) {
        navbtns[i].classList.remove("active");
    }

    // 3. SHOW TAB: Display the clicked section
    var targetTab = document.getElementById(tabName);
    if (targetTab) {
        targetTab.style.display = "block";
        targetTab.classList.add("active");
    }

    // 4. HIGHLIGHT BUTTON: Make the clicked button look "selected"
    // We always search the sidebar for the button matching tabName to ensure the 
    // sidebar stays synced, even if the tab was opened via a "View All" link.
    navbtns = document.querySelectorAll('.nav-btn');
    navbtns.forEach(btn => {
        btn.classList.remove("active");
        if (btn.getAttribute('data-tab') === tabName) btn.classList.add('active');
    });
}

$(document).ready(function() {
    // The endpoint for profile-related AJAX actions
    const actionUrl = 'db/action/update_profile.php';

    // Bind tab buttons and initialize the default tab
    $('.nav-btn').click(function(evt) {
        const tabName = $(this).data('tab');
        if (tabName) {
            openTab(evt, tabName);
        }
    });

    $('.view-all-link').click(function(evt) {
        evt.preventDefault();
        openTab(evt, $(this).data('tab'));
    });

    // Filters the order history table based on the selected status (Paid, Cancelled, etc.)
    function filterOrderHistory(statusFilter) {
        $('#orders .order-row').each(function() {
            const row = $(this);
            const rowStatus = (row.data('status') || '').toLowerCase();
            const matchesStatus = !statusFilter || rowStatus === statusFilter;
            row.toggle(matchesStatus);
        });
    }

    $(document).on('click', '.order-status-tab', function() {
        const tab = $(this);
        $('.order-status-tab').removeClass('active');
        tab.addClass('active');
        const statusFilter = tab.data('status') || '';
        filterOrderHistory(statusFilter);
    });

    filterOrderHistory('');

    var defaultTab = $(".nav-btn.active");
    if (defaultTab.length) {
        defaultTab.trigger('click');
    }

    const originalUsername = $('#settings-username').val().trim();

    // --- 1. PERSONAL INFORMATION ---
    $('#personal-info-form').submit(function(e) {
        e.preventDefault();

        const username = $('#settings-username').val().trim();
        const fullName = $('#settings-full-name').val().trim();
        const email = $('#settings-email').val().trim();
        const phone = $('#settings-phone').val().trim();
        const gender = $('#settings-gender').val();

        if (!fullName || !email) {
            Swal.fire('Error', 'Please enter your full name and email address.', 'error');
            return;
        }

        $.post(actionUrl, {
            update_personal_info: 1,
            username: username,
            full_name: fullName,
            email: email,
            phone: phone,
            gender: gender
        }, function(response) {
            if (response.status === 'success') {
                $('.user-name').text(fullName);
                if (username !== originalUsername) {
                    Swal.fire('Username Changed', 'Your username has been updated successfully.', 'success');
                } else {
                    Swal.fire('Saved!', 'Your profile information has been updated.', 'success');
                }
            } else {
                Swal.fire('Error', response.message || 'Unable to save changes.', 'error');
            }
        }, 'json');
    });

    // --- 2. CHANGE PASSWORD ---
    $(document).on('click', '.btn-dark.js-change-pass', function() {
        Swal.fire({
            title: 'Change Password',
            html:
                '<input type="password" id="old-pass" class="swal2-input" placeholder="Current Password">' +
                '<input type="password" id="new-pass" class="swal2-input" placeholder="New Password">' +
                '<input type="password" id="confirm-pass" class="swal2-input" placeholder="Confirm New Password">',
            showCancelButton: true,
            confirmButtonColor: '#2A2A2A',
            cancelButtonColor: '#888',
            confirmButtonText: 'Update Password',
            preConfirm: () => {
                const oldP = $('#old-pass').val();
                const newP = $('#new-pass').val();
                const confP = $('#confirm-pass').val();

                // Validation: Stop if fields are empty
                if (!oldP || !newP || !confP) {
                    Swal.showValidationMessage('All fields are required');
                } 
                // Password Length Validation: Check if at least 8 characters
                else if (newP.length < 8) {
                    Swal.showValidationMessage('New password must be at least 8 characters long');
                }
                // Match Validation
                else if (newP !== confP) {
                    Swal.showValidationMessage('New passwords do not match');
                }
                return { old_pass: oldP, new_pass: newP };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Send data to PHP via POST
                $.post(actionUrl, result.value, function(response) {
                    if(response.status === 'success') {
                        Swal.fire('Updated!', 'Password changed successfully.', 'success');
                    } else {
                        Swal.fire('Error', response.message || 'Failed to update password', 'error');
                    }
                }, 'json');
            }
        });
    });

    // --- 2. EDIT ADDRESS ---
    $(document).on('click', '.js-edit-address', function(e) {
        e.preventDefault();
        
        // 'card' finds the specific address box you clicked
        const card = $(this).closest('.addr-card');
        
        // 'addrId' pulls the ID from the hidden input we added to the HTML
        const addrId = card.find('input[name="address_id"]').val();
        
        // Pull current text from the screen (using specific classes we'll add to profile.php)
        const currentName = card.find('.addr-name-text').text();
        const currentLabel = card.find('.addr-label-text').text();
        const currentStreet = card.find('.addr-street-text').text();
        const currentCity = card.find('.addr-city-text').text();
        const currentZip = card.find('.addr-zip-text').text();
        const currentPhone = card.find('.addr-phone-text').text();

        Swal.fire({
            title: 'Edit Address',
            html:
                `<input id="edit-label" class="swal2-input" value="${currentLabel}" placeholder="Label">` +
                `<input id="edit-name" class="swal2-input" value="${currentName}" placeholder="Full Name">` +
                `<input id="edit-street" class="swal2-input" value="${currentStreet}" placeholder="Street Address">` +
                `<input id="edit-city" class="swal2-input" value="${currentCity}" placeholder="City">` +
                `<input id="edit-zip" class="swal2-input" value="${currentZip}" placeholder="Zip Code">` +
                `<input id="edit-phone" class="swal2-input" value="${currentPhone}" placeholder="Phone Number">`,
            showCancelButton: true,
            confirmButtonColor: '#2A2A2A',
            confirmButtonText: 'Save Changes',
            preConfirm: () => {
                return { 
                    id: addrId, 
                    name: $('#edit-name').val(), 
                    label: $('#edit-label').val(),
                    street: $('#edit-street').val(),
                    city: $('#edit-city').val(),
                    zip: $('#edit-zip').val(),
                    phone: $('#edit-phone').val()
                };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Send the new info to the database
                $.post(actionUrl, result.value, function(response) {
                    if(response.status === 'success') {
                        Swal.fire('Saved!', 'Address updated.', 'success').then(() => {
                            location.reload(); // Refresh to see the new text on screen
                        });
                    }
                }, 'json');
            }
        });
    });

    // --- 3. ADDRESS REMOVAL (THE "DOUBLE DELETE") ---
    $(document).on('click', '.addr-card a.danger', function(e) {
        e.preventDefault(); // Prevents the page from jumping to the top
        
        // Step A: Find the specific address card in the HTML
        const card = $(this).closest('.addr-card');
        
        // Step B: Get the ID from the hidden input field inside that card
        const addrId = card.find('input[name="address_id"]').val();

        Swal.fire({
            title: 'Remove Address?',
            text: "Are you sure you want to delete this saved address?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#2A2A2A',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it'
        }).then((result) => {
            if (result.isConfirmed) {
                // Step C: Tell PHP to delete this ID from the MySQL database
                $.post(actionUrl, { delete_address_id: addrId }, function(response) {
                    if(response.status === 'success') {
                        // Step D: Only if DB delete worked, hide the card from the UI
                        card.fadeOut(500, function() { 
                            $(this).remove(); // Removes the HTML element completely
                        });
                        Swal.fire('Deleted!', 'Your address has been removed.', 'success');
                    } else {
                        Swal.fire('Error', 'Could not delete from database.', 'error');
                    }
                }, 'json');
            }
        });
    });

    // --- 3. VIEW ORDER DETAILS ---
    $(document).on('click', '.js-view-order, .view-order-btn', function(e) {
        e.preventDefault();
        console.log('View Order button clicked!'); // Debugging line
        
        const row = $(this).closest('.order-row');
        const orderId = $(this).data('order-id');
        const orderNo = row.find('.order-no-text').text() || 'Order #' + orderId;
        const date = row.find('.order-date-text').text() || 'N/A';
        const total = row.find('.order-total-text').text() || 'N/A';
        const status = (row.data('status') || 'Pending').toUpperCase();

        Swal.fire({
            title: 'Order Summary',
            html: `
                <div class="order-modal-details" style="text-align: left; font-size: 0.95rem; line-height: 1.6;">
                    <p><strong>Order Number:</strong> ${orderNo}</p>
                    <p><strong>Date Placed:</strong> ${date}</p>
                    <p><strong>Total Amount:</strong> ${total}</p>
                    <p><strong>Current Status:</strong> <span class="status-badge status-${status.toLowerCase()}">${status}</span></p>
                    <hr style="margin: 15px 0; border: 0; border-top: 1px solid #eee;">
                    <p style="font-style: italic; color: #666;">Note: For security reasons, itemized receipts are only visible via the confirmation email sent at purchase.</p>
                </div>
            `,
            confirmButtonColor: '#2A2A2A',
            confirmButtonText: 'Close'
        });
    });

    // --- 4. CANCEL ORDER ---
    $(document).on('click', '.cancel-order-btn', function(e) {
        e.preventDefault();
        const button = $(this);
        const orderId = button.data('order-id');

        Swal.fire({
            title: 'Cancel this order?',
            text: 'Pending orders can be cancelled before they are processed.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#888',
            confirmButtonText: 'Yes, cancel it'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post(actionUrl, { cancel_order_id: orderId }, function(response) {
                    if (response.status === 'success') {
                        const orderRow = button.closest('.order-row');
                        const statusBadge = orderRow.find('.status-badge');
                        statusBadge.text('Cancelled');
                        statusBadge.removeClass('status-pending status-completed status-processing').addClass('status-cancelled');
                        orderRow.attr('data-status', 'cancelled');
                        button.fadeOut(200, function() { $(this).remove(); });
                        // Re-filter the history while maintaining the current status tab view
                        filterOrderHistory($('.order-status-tab.active').data('status') || '');
                        Swal.fire('Cancelled', 'Your order has been cancelled.', 'success');
                    } else {
                        Swal.fire('Error', response.message || 'Unable to cancel this order.', 'error');
                    }
                }, 'json');
            }
        });
    });

    // --- 5. ADD NEW ADDRESS ---
    $('.btn-dark:contains("+ Add New")').click(function() {
        Swal.fire({
            title: 'Add New Address',
            html:
                '<input id="swal-label" class="swal2-input" placeholder="Home, Office, etc.">' +
                '<input id="swal-name" class="swal2-input" placeholder="Full Name">' +
                '<input id="swal-street" class="swal2-input" placeholder="Street Address">' +
                '<input id="swal-city" class="swal2-input" placeholder="City">' +
                '<input id="swal-zip" class="swal2-input" placeholder="Zip Code">' +
                '<input id="swal-phone" class="swal2-input" placeholder="Phone Number">',
            confirmButtonColor: '#2A2A2A',
            preConfirm: () => {
                const label = $('#swal-label').val();
                const name = $('#swal-name').val();
                if (!label || !name) {
                    Swal.showValidationMessage('Please fill in Label and Full Name');
                }
                return { 
                    label: label, 
                    name: name,
                    street: $('#swal-street').val(),
                    city: $('#swal-city').val(),
                    zip: $('#swal-zip').val(),
                    phone: $('#swal-phone').val()
                };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                $.post(actionUrl, result.value, function(response) {
                    if(response.status === 'success') {
                        Swal.fire('Saved!', 'Address added successfully.', 'success').then(() => {
                            location.reload(); // Refresh to show the new card in the grid
                        });
                    }
                }, 'json');
            }
        });
    });

    // --- 5. DELETE ACCOUNT ---
    $('.btn-outline:contains("Delete Account")').click(function(e) {
        e.preventDefault();
        Swal.fire({
            title: 'Are you absolutely sure?',
            text: "This will permanently delete your account!",
            icon: 'error',
            showCancelButton: true,
            confirmButtonColor: '#C00',
            confirmButtonText: 'Yes, delete my account'
        }).then((result) => {
            if (result.isConfirmed) {
                // Sends a special trigger to PHP to run the account deletion logic
                $.post(actionUrl, { confirm_delete_account: true }, function(response) {
                    if(response.status === 'success') {
                        Swal.fire('Deleted', 'Your account is gone.', 'success').then(() => {
                            window.location.href = "index.php"; // Redirect to home page
                        });
                    }
                }, 'json');
            }
        });
    });
});