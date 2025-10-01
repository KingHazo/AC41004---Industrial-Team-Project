document.addEventListener("DOMContentLoaded", () => {
    const form = document.querySelector(".signup-form");

    if (form) {
        const successMessage = form.querySelector(".success");

        form.addEventListener("submit", (e) => {
            e.preventDefault();

            // Get fields
            const email = form.querySelector('#email');
            const password = form.querySelector('#password');
            const confirmPassword = form.querySelector('#confirm-password');

            // Reset errors + success
            form.querySelectorAll(".error").forEach(err => err.textContent = "");
            successMessage.textContent = "";

            let valid = true;

            // Email check
            if (!email.value.trim()) {
                email.nextElementSibling.textContent = "Email is required";
                valid = false;
            }

            // Password check
            if (password.value.trim().length < 6) {
                password.nextElementSibling.textContent = "Password must be at least 6 characters";
                valid = false;
            }

            // Confirm password check
            if (confirmPassword.value.trim() !== password.value.trim()) {
                confirmPassword.nextElementSibling.textContent = "Passwords do not match";
                valid = false;
            }

            if (valid) {
                successMessage.textContent = "Signup successful ✅ (test mode)";
                form.reset();

                // Clear message after 3 seconds
                setTimeout(() => {
                    successMessage.textContent = "";
                }, 3000);
            }
        });
    }
});
