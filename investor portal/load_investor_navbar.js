let pathToNavbar = '';

if (window.location.pathname.includes('/investor/')) {
    pathToNavbar = '../investor_navbar.html';
} else {
    pathToNavbar = 'investor_navbar.html';
}

fetch(pathToNavbar)
    .then(response => response.text())
    .then(data => {
        document.getElementById('investor-navbar-placeholder').innerHTML = data;

        // handles logout click
        const logoutLink = document.getElementById('logout');
        if (logoutLink) {
            logoutLink.addEventListener('click', (e) => {
                e.preventDefault();
                //we have to clear the login state 
                window.location.href = '/index.html';
            });
        }
    })
    .catch(err => console.error('Failed to load investor navbar:', err));
