fetch('navbar.html')
    .then(response => response.text())
    .then(data => {
        document.getElementById('navbar-placeholder').innerHTML = data;

        const loginLink = document.getElementById('login-link');
        if(loginLink) {
            console.log('Navbar loaded, login link found:', loginLink);
        }
    })
    .catch(err => console.error('Failed to load navbar:', err));
