<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login/Signup</title>
    <link rel="stylesheet" href="login.css?v=<?php echo time(); ?>"> <!--handles cache issues-->
    <link rel="stylesheet" href="../navbar.css?v=<?php echo time(); ?>"> <!--handles cache issues-->
    <link rel="stylesheet" href="../footer.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">

    <style>
        /* toggle slider container */
        .toggle-container {
            width: 220px;
            margin: 20px auto;
            background: #f0f0f0;
            border-radius: 25px;
            display: flex;
            position: relative;
            cursor: pointer;
            border: 5px solid #f0f0f0;
        }

        .toggle-option {
            flex: 1;
            text-align: center;
            padding: 10px 0;
            font-weight: 600;
            z-index: 1;
        }

        .slider {
            position: absolute;
            top: 0;
            left: 0;
            width: 50%;
            height: 100%;
            background: #0b3d91;
            border-radius: 25px;
            transition: left 0.3s;
        }

        .toggle-option.active {
            color: white;
        }
    </style>

    <!-- Google Fonts -->
    <link
        href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap"
        rel="stylesheet">
</head>

<body>

    <?php include '../navbar.php'; ?>

    <main class="form-container">
        <form method="POST" action="login.php" id="mainForm" class="login-form">

            <!-- Business / Investor Toggle -->
            <div class="toggle-container" id="loginToggle">
                <div class="slider"></div>
                <button type="button" class="toggle-option active" data-role="business">Business</button>
                <button type="button" class="toggle-option" data-role="investor">Investor</button>
            </div>

            <!-- Hidden input to track user type -->
            <input type="hidden" name="user_type" id="userType" value="business">

            <!-- Form Title -->
            <h2 id="formTitle">Login</h2>

            <!-- Login / Signup Fields -->
            <div id="nameField" style="display:none;">
                <label for="name">Name</label>
                <input id="name" name="name" type="text" placeholder="Enter your name">
            </div>

            <label for="email">Email</label>
            <input id="email" name="email" type="text" placeholder="Enter email / Username" required>

            <label for="password">Password</label>
            <input id="password" name="password" type="password" placeholder="Enter password" required>

            <label for="confirmPassword" id="confirmLabel" style="display:none;">Confirm Password</label>
            <input id="confirmPassword" name="confirm_password" type="password"
                   placeholder="Confirm password" style="display:none;">

            <!-- Submit Button -->
            <button type="submit" id="submitButton">Login</button>

            <!-- Alternate Link -->
            <p class="alt-link">
                <a href="#" id="toggleMode">
                    Don’t have an account?
                    <span tabindex="0">Sign up</span>
                </a>
            </p>
        </form>
    </main>

    <?php include '../footer.php'; ?>
    <script src="login_signup.js?v=<?php echo time(); ?>"></script>
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const mainForm = document.getElementById("mainForm");

    mainForm.addEventListener("submit", async (e) => {
        e.preventDefault(); // prevent default form submission

        const isSignup = mainForm.action.includes('signup.php');
        const password = document.getElementById("password").value.trim();
        const confirmPassword = document.getElementById("confirmPassword").value.trim();
        const nameField = document.getElementById("nameField");
        const email = document.getElementById("email").value.trim();

        // ===== SIGNUP VALIDATION =====
        if (isSignup) {
            // Check if name is filled
            if (nameField && nameField.style.display !== 'none' && !document.getElementById("name").value.trim()) {
                Toastify({
                    text: "Please enter your name",
                    duration: 5000,
                    gravity: "top",
                    position: "right",
                    backgroundColor: "#e74c3c",
                    close: true
                }).showToast();
                return;
            }

            // Check email format
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailPattern.test(email)) {
                Toastify({
                    text: "Please enter a valid email address",
                    duration: 5000,
                    gravity: "top",
                    position: "right",
                    backgroundColor: "#e74c3c",
                    close: true
                }).showToast();
                return;
            }

            // Check if passwords match
            if (password !== confirmPassword) {
                Toastify({
                    text: "Passwords do not match",
                    duration: 5000,
                    gravity: "top",
                    position: "right",
                    backgroundColor: "#e74c3c",
                    close: true
                }).showToast();
                return;
            }

            // Submit signup via fetch to handle duplicate email and other errors
            const formData = new FormData(mainForm);

            try {
                const response = await fetch(mainForm.action, {
                    method: "POST",
                    body: formData
                });

                const result = await response.json();

                if (result.error) {
                    // Show Toastify popup for duplicate email or other errors
                    Toastify({
                        text: result.error,
                        duration: 5000,
                        gravity: "top",
                        position: "right",
                        backgroundColor: "#e74c3c",
                        close: true
                    }).showToast();
                } else if (result.success) {
                    // Redirect based on user type
                    if (result.userType === 'investor') {
                        window.location.href = "../investor_portal/investor_portal_home.php";
                    } else {
                        window.location.href = "../business_portal/business_dashboard.php";
                    }
                }
            } catch (err) {
                console.error("Fetch error:", err);
                Toastify({
                    text: "Unexpected error, try again.",
                    duration: 5000,
                    gravity: "top",
                    position: "right",
                    backgroundColor: "#e74c3c",
                    close: true
                }).showToast();
            }

            return;
        }

        // ===== LOGIN VALIDATION =====
        if (mainForm.action.includes('login.php')) {
            const formData = new FormData(mainForm);

            try {
                const response = await fetch(mainForm.action, {
                    method: "POST",
                    body: formData
                });

                const result = await response.json();

                if (result.error) {
                    Toastify({
                        text: result.error,
                        duration: 5000,
                        gravity: "top",
                        position: "right",
                        backgroundColor: "#e74c3c",
                        close: true
                    }).showToast();
                } else if (result.success) {
                    if (result.userType === 'investor') {
                        window.location.href = "../investor_portal/investor_portal_home.php";
                    } else {
                        window.location.href = "../business_portal/business_dashboard.php";
                    }
                }
            } catch (err) {
                console.error("Fetch error:", err);
                Toastify({
                    text: "Unexpected error, try again.",
                    duration: 5000,
                    gravity: "top",
                    position: "right",
                    backgroundColor: "#e74c3c",
                    close: true
                }).showToast();
            }
        }
    });
});
</script>


</body>

</html>
