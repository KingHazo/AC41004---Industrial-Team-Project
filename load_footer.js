let pathToFooter = '../footer.php'; // default we are in a sub folder

// if we are in the root folder use footer.html directly
if (window.location.pathname === '/' || window.location.pathname.endsWith('index.html')) {
    pathToFooter = 'footer.php';
}

fetch(pathToFooter)
    .then(response => response.text())
    .then(data => {
        document.getElementById('footer-placeholder').innerHTML = data;
    })
    .catch(err => console.error('Failed to load footer:', err));
