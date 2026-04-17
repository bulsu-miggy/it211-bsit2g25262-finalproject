"use strict";

$(document).ready(function () {
    const urlParams = new URLSearchParams(window.location.search);
    const themeColor = '#2A2A2A';

    // 1. Server Notifications (Checks URL for ?signup=success or ?error=...)
    if (urlParams.get('error') === 'invalid_login') {
        Swal.fire({ 
            icon: 'error', 
            title: 'Login Failed', 
            text: 'Incorrect email or password.', 
            confirmButtonColor: themeColor 
        });
    } else if (urlParams.get('error') === 'empty_fields') {
        Swal.fire({ 
            icon: 'warning', 
            title: 'Incomplete', 
            text: 'Please ensure all fields are filled out.', 
            confirmButtonColor: themeColor 
        });
    } else if (urlParams.get('signup') === 'success') {
        Swal.fire({ 
            icon: 'success', 
            title: 'Welcome!', 
            text: 'Account created successfully. You can now log in.', 
            confirmButtonColor: themeColor,
            confirmButtonText: 'Go to Login' 
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'login.php'; 
            }
        });
    } else if (urlParams.get('reset') === 'success') {
        Swal.fire({
            icon: 'success',
            title: 'Password Updated',
            text: 'Your password has been successfully changed. Please log in now.',
            confirmButtonColor: themeColor,
            confirmButtonText: 'Login Now'
        });
    } else if (urlParams.get('error') === 'email_taken') {
        Swal.fire({ 
            icon: 'error', 
            title: 'Email Taken', 
            text: 'This email is already registered with Solis.', 
            confirmButtonColor: themeColor 
        });
    } else if (urlParams.get('error') === 'invalid_email') {
        Swal.fire({ 
            icon: 'warning', 
            title: 'Invalid Email', 
            text: 'Please enter a valid email address.', 
            confirmButtonColor: themeColor 
        });
    } else if (urlParams.get('error') === 'password_too_short') {
        Swal.fire({ 
            icon: 'warning', 
            title: 'Weak Password', 
            text: 'Password must be at least 8 characters long.', 
            confirmButtonColor: themeColor 
        });
    } else if (urlParams.get('error') === 'password_mismatch') {
        Swal.fire({ 
            icon: 'error', 
            title: 'Password Mismatch', 
            text: 'Password and confirmation do not match.', 
            confirmButtonColor: themeColor 
        });
    } else if (urlParams.get('error') === 'system_error') {
        Swal.fire({ 
            icon: 'error', 
            title: 'System Error', 
            text: 'We encountered a problem. Please try again later.', 
            confirmButtonColor: themeColor 
        });
    } else if (urlParams.get('error')) {
        Swal.fire({
            icon: 'warning',
            title: 'Attention',
            text: decodeURIComponent(urlParams.get('error')),
            confirmButtonColor: themeColor
        });
    }

    // 2. Login Validation
    $('#loginForm').on('submit', function (e) {
        const email = $('#email').val().trim();
        const password = $('#password').val();
        
        if (!email || !password) {
            e.preventDefault();
            Swal.fire({ 
                icon: 'warning', 
                title: 'Required', 
                text: 'Please enter both email and password.', 
                confirmButtonColor: themeColor 
            });
        }
    });

    // 3. Signup Validation
    $('#signupForm').on('submit', function (e) {
        // Grab values using updated IDs from our HTML
        const fullName = $('#full_name').val().trim();
        const email = $('#email').val().trim();
        const pass = $('#password').val();
        const confirm = $('#confirm_password').val();
        
        if (!fullName || !email) {
            e.preventDefault();
            Swal.fire({ icon: 'warning', title: 'Missing Info', text: 'Please fill in your name and email.', confirmButtonColor: themeColor });
        } else if (pass.length < 8) {
            e.preventDefault();
            Swal.fire({ 
                icon: 'warning', 
                title: 'Security', 
                text: 'Password must be at least 8 characters.', 
                confirmButtonColor: themeColor 
            });
        } else if (pass !== confirm) {
            e.preventDefault();
            Swal.fire({ 
                icon: 'error', 
                title: 'Mismatch', 
                text: 'Passwords do not match.', 
                confirmButtonColor: themeColor 
            });
        }
        // If all good, the form will proceed to db/action/process_signup.php
    });

    function openForgotPasswordModal() {
        Swal.fire({
            title: 'Reset Password',
            html:
                '<p style="margin-bottom: 18px; color: #555; font-size: 0.95rem;">Enter your email address and we will send a link to reset your password.</p>' +
                '<input type="email" id="swal-reset-email" class="swal2-input" placeholder="email@example.com">',
            showCancelButton: true,
            confirmButtonText: 'Reset',
            cancelButtonText: 'Cancel',
            confirmButtonColor: themeColor,
            focusConfirm: false,
            preConfirm: () => {
                const email = $('#swal-reset-email').val().trim();
                const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

                if (!email) {
                    Swal.showValidationMessage('Please enter your email address.');
                    return false;
                }

                if (!emailPattern.test(email)) {
                    Swal.showValidationMessage('Please enter a valid email address.');
                    return false;
                }

                return email;
            },
            didOpen: () => {
                $('#swal-reset-email').focus();
            }
        }).then((result) => {
            if (!result.isConfirmed || !result.value) {
                return;
            }

            const email = result.value;
            $.ajax({
                url: 'db/action/process_forgot_password.php',
                method: 'POST',
                data: { email: email, ajax: '1' },
                dataType: 'json'
            }).done(function (response) {
                if (response.status === 'redirect' && response.url) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Email Verified',
                        text: 'Email found. Redirecting to reset your password now.',
                        confirmButtonColor: themeColor,
                        timer: 1800,
                        timerProgressBar: true,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = response.url;
                    });
                } else if (response.status === 'error') {
                    let message = 'Unable to process the request. Please try again.';

                    if (response.error === 'invalid_email') {
                        message = 'Please enter a valid email address.';
                    } else if (response.error === 'email_not_found') {
                        message = 'No account was found using this email.';
                    } else if (response.error === 'system_error') {
                        message = 'A system error occurred. Please try again later.';
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Reset Failed',
                        text: message,
                        confirmButtonColor: themeColor
                    });
                } else {
                    Swal.fire({
                        icon: 'success',
                        title: 'Reset Sent',
                        text: 'If the email exists, a reset link will arrive momentarily.',
                        confirmButtonColor: themeColor
                    });
                }
            }).fail(function () {
                Swal.fire({
                    icon: 'error',
                    title: 'Network Error',
                    text: 'Unable to send the reset request. Please try again later.',
                    confirmButtonColor: themeColor
                });
            });
        });
    }

    $(document).on('click', '.forgot-password-link', function (e) {
        e.preventDefault();
        openForgotPasswordModal();
    });

    if (urlParams.get('showForgot') === '1') {
        openForgotPasswordModal();
    }

    // 5. Forgot Password Validation
    $('#forgotPasswordForm').on('submit', function (e) {
        const email = $('#email').val().trim();
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        if (!email) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Email Required',
                text: 'Please enter the email address for your account.',
                confirmButtonColor: themeColor
            });
        } else if (!emailPattern.test(email)) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Invalid Email',
                text: 'Please enter a valid email address before sending the reset link.',
                confirmButtonColor: themeColor
            });
        }
    });

    // 5. Reset Password Validation
    $('#resetPasswordForm').on('submit', function (e) {
        const password = $('#password').val();
        const confirmPassword = $('#confirm_password').val();

        if (!password || !confirmPassword) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Missing Password',
                text: 'Please fill in both password fields.',
                confirmButtonColor: themeColor
            });
            return;
        }

        if (password.length < 8) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Weak Password',
                text: 'Password must be at least 8 characters long.',
                confirmButtonColor: themeColor
            });
            return;
        }

        if (password !== confirmPassword) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Password Mismatch',
                text: 'Both password fields must match.',
                confirmButtonColor: themeColor
            });
        }
    });

    // 6. Guest View Details Redirect
    // Using event delegation $(document).on to ensure it works even if content is reloaded
    $(document).on('click', '.guest-view', function (e) {
        e.preventDefault();
        Swal.fire({
            title: 'Login Required',
            text: 'Please login or sign up to view product details.',
            icon: 'info',
            showCancelButton: true,
            confirmButtonColor: themeColor,
            cancelButtonColor: '#888',
            confirmButtonText: 'Login',
            cancelButtonText: 'Sign Up'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'login.php';
            } else if (result.dismiss === Swal.DismissReason.cancel) {
                window.location.href = 'signup.php';
            }
        });
    });

    // 5. ADDED: Guest Purchase Restriction
    // Triggers when a guest clicks "LOGIN TO PURCHASE" on the shop grid
    $(document).on('click', 'a[href="login.php"].btn-add', function (e) {
        e.preventDefault();
        Swal.fire({
            title: 'Start Your Collection',
            text: 'Create an account or login to add items to your basket.',
            icon: 'info',
            showCancelButton: true,
            confirmButtonColor: themeColor,
            cancelButtonColor: '#888',
            confirmButtonText: 'Login',
            cancelButtonText: 'Sign Up'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'login.php';
            } else if (result.dismiss === Swal.DismissReason.cancel) {
                window.location.href = 'signup.php';
            }
        });
    });
});