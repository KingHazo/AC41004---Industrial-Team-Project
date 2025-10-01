// business/investor toggle
const toggle = document.getElementById('loginToggle');
const slider = toggle.querySelector('.slider');
const options = toggle.querySelectorAll('.toggle-option');
const userTypeInput = document.getElementById('userType');
const formTitle = document.getElementById('formTitle');
const submitButton = document.getElementById('submitButton');

// signup extra fields
const nameField = document.getElementById('nameField');
const confirmLabel = document.getElementById('confirmLabel');
const confirmPassword = document.getElementById('confirmPassword');

options.forEach(option => {
    option.addEventListener('click', () => {
        options.forEach(o => o.classList.remove('active'));
        option.classList.add('active');

        if(option.dataset.role === 'business'){
            slider.style.left = '0%';
            userTypeInput.value = 'business';
        } else {
            slider.style.left = '50%';
            userTypeInput.value = 'investor';
        }
           //debug
          //console.log('Selected user type:', userTypeInput.value);
    });
});

// login/signup toggle
const toggleMode = document.getElementById('toggleMode');
const mainForm = document.getElementById('mainForm');

toggleMode.addEventListener('click', (e) => {
    e.preventDefault();

    if(mainForm.action.includes('login.php')){
        // Switch to signup
        mainForm.action = 'signup.php';
        formTitle.textContent = 'Sign Up';
        submitButton.textContent = 'Sign Up';
        toggleMode.textContent = 'Already have an account? Login';

        // show extra signup fields
        if(nameField) nameField.style.display = 'block';
        if(confirmLabel) confirmLabel.style.display = 'block';
        if(confirmPassword) confirmPassword.style.display = 'block';
    } else {
        // switch back to login
        mainForm.action = 'login.php';
        formTitle.textContent = 'Login';
        submitButton.textContent = 'Login';
        toggleMode.textContent = 'Don’t have an account? Sign up';

        // hide extra signup fields
        if(nameField) nameField.style.display = 'none';
        if(confirmLabel) confirmLabel.style.display = 'none';
        if(confirmPassword) confirmPassword.style.display = 'none';
    }
});
