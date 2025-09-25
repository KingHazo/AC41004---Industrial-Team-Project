const email = document.querySelector('.email');
const password = document.querySelector('.password');
const login = document.getElementById('login');

login.addEventListener('click', (e) => {
    e.preventDefault();

    const loginDetails = {
        name: email.value.trim(),
        password: password.value.trim()
    };

    if (loginDetails.name !== "" && loginDetails.password !== "") {
        alert("Login Successfully (test mode)");
        console.log("Login details:", loginDetails);

        // check the current page URL for now 
        //Chnage this to some sort of ID later
        const path = window.location.pathname.toLowerCase();

        if (path.includes("invest")) {
            
            window.location.href = '../investor%20portal/investor-portal-home.html';
        } else if (path.includes("business")) {
          
            window.location.href = '../business%20portal/business_dashboard.html';
        } else {
        
            window.location.href = '../index.html';
        }

    } else {
        alert("Fields cannot be empty");
    }
});
