document.addEventListener('DOMContentLoaded', () => {

    //Check the URL parameter
    const urlParams = new URLSearchParams(window.location.search);
    const typeParam = urlParams.get('type');
    //console.log('URL type parameter:', typeParam); // for debugging

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

    // function to select user type visually
    function selectUserType(type) {
        options.forEach(o => o.classList.remove('active'));
        if (type === 'investor') {
            options[1].classList.add('active');
            slider.style.left = '50%';
            userTypeInput.value = 'investor';
        } else {
            options[0].classList.add('active');
            slider.style.left = '0%';
            userTypeInput.value = 'business';
        }
    }

    //toggle based on URL param
    if (typeParam === 'business' || typeParam === 'investor') {
        selectUserType(typeParam);
    }

    // user clicks on toggle
    options.forEach(option => {
        option.addEventListener('click', () => {
            options.forEach(o => o.classList.remove('active'));
            option.classList.add('active');

            if (option.dataset.role === 'business') {
                slider.style.left = '0%';
                userTypeInput.value = 'business';
            } else {
                slider.style.left = '50%';
                userTypeInput.value = 'investor';
            }
        });
    });

    // login/signup toggle
    const toggleMode = document.getElementById('toggleMode');
    const mainForm = document.getElementById('mainForm');

    toggleMode.addEventListener('click', (e) => {
        e.preventDefault();

        if (mainForm.action.includes('login.php')) {
            mainForm.action = 'signup.php';
            formTitle.textContent = 'Sign Up';
            submitButton.textContent = 'Sign Up';
            toggleMode.textContent = 'Already have an account? Login';
            if (nameField) nameField.style.display = 'block';
            if (confirmLabel) confirmLabel.style.display = 'block';
            if (confirmPassword) confirmPassword.style.display = 'block';
        } else {
            mainForm.action = 'login.php';
            formTitle.textContent = 'Login';
            submitButton.textContent = 'Login';
            toggleMode.textContent = 'Don’t have an account? Sign up';
            if (nameField) nameField.style.display = 'none';
            if (confirmLabel) confirmLabel.style.display = 'none';
            if (confirmPassword) confirmPassword.style.display = 'none';
        }
    });

});

const toggleContainer = document.getElementById('loginToggle');
const toggleOptions = document.querySelectorAll('.toggle-option');
const slider = document.querySelector('.slider');
const userTypeInput = document.getElementById('userType');

toggleOptions.forEach(option => {
    option.addEventListener('click', () => {
        toggleOptions.forEach(opt => opt.classList.remove('active'));
        option.classList.add('active');
        slider.style.left = option.dataset.role === 'business' ? '0%' : '50%';
        userTypeInput.value = option.dataset.role;
    });
});
