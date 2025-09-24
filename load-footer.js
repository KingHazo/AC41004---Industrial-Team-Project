let pathToFooter = '';
if (window.location.pathname.includes('/login/') || window.location.pathname.includes('/about/')) {
    pathToFooter = '../footer.html';
} else {
    pathToFooter = 'footer.html';
}

fetch(pathToFooter)
    .then(response => response.text())
    .then(data => {
        document.getElementById('footer-placeholder').innerHTML = data;
    })
    .catch(err => console.error('Failed to load footer:', err));
