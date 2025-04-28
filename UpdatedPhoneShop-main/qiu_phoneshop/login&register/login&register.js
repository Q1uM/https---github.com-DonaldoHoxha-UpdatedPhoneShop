document.addEventListener('DOMContentLoaded', (event) => {
    const forgot_psw = document.querySelector('.forgot_psw');

    const registerBefore = document.querySelector('.register_before');
    const signUpBox = document.querySelector('.signupBox');
    const loginAfter = document.querySelector('.login_after');
    const loginBox = document.querySelector('.loginBox');
    signUpBox.style.transition = 'background-color 2s ease';
    loginBox.style.transition = 'background-color 2s ease';
    // Mouseover event for the register_before element
    registerBefore.addEventListener('mouseenter', () => {
        registerBefore.classList.add('hidden');
        signUpBox.classList.remove('hidden');
        signUpBox.style.backgroundColor = '#f1f1f1';
        loginBox.classList.add('hidden');
        loginAfter.classList.remove('hidden');
        loginAfter.style.backgroundColor = "#ddd";
    });

    // Mouseover event for the login_after element
    loginAfter.addEventListener('mouseenter', () => {
        loginAfter.classList.add('hidden');
        loginBox.classList.remove('hidden');
        signUpBox.classList.add('hidden');
        registerBefore.classList.remove('hidden');
    });

    // Check for error parameters on page load
    const urlParams = new URLSearchParams(window.location.search);
    const newP = document.createElement('p');
    newP.className = 'error';

    if (urlParams.has('error')) {
        const error = urlParams.get('error');
        console.log(error);
        switch (error) {
            case 'email_exists':
                signupEmail = document.querySelector('.signupEmail');
                newP.textContent = 'Email already exists';
                signupEmail.appendChild(newP);
                break;
            case 'username_exists':
                signupUsername = document.querySelector('.signupUsername');
                newP.textContent = 'Username already exists';
                signupUsername.appendChild(newP);
                break;
        }
    }
});