fetch('business_navbar.html')
  .then(response => response.text())
  .then(data => {
    document.getElementById('business-navbar-placeholder').innerHTML = data;
  })
  .catch(err => console.error('Failed to load business navbar:', err));
