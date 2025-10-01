let pathToNavbar = '';
if (window.location.pathname.includes('/login/') || window.location.pathname.includes('/about/')) {
    pathToNavbar = '../navbar.php';
} else {
    pathToNavbar = 'navbar.php';
}

fetch(pathToNavbar)
    .then(response => response.text())
    .then(data => {
        document.getElementById('navbar-placeholder').innerHTML = data;

        const loginLink = document.getElementById('login-link');
        if (loginLink) {
            loginLink.addEventListener('click', (e) => {
                e.preventDefault();
                // check to see if we are already on the login page
                if (!window.location.pathname.endsWith('/login/login.html')) {
                    window.location.href = 'login/login.html';
                } else {
                    console.log('already on login page');
                }
            });
        }
    })
    .catch(err => console.error('Failed to load navbar:', err));
