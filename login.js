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

    } else {
        alert("Fields cannot be empty");
    }
});
