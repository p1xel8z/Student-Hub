const form = document.querySelector("#registrationForm");

if (form) {

    form.addEventListener("submit", function (event) {


        const name = document.querySelector("#first_name").value.trim();
        const lastName = document.querySelector("#last_name").value.trim();
        const email = document.querySelector("#email").value.trim();
        const mobile = document.querySelector("#mobile").value.trim();
        const password = document.querySelector("#password").value;
        const confirmPassword = document.querySelector("#confirm_password").value;
        const program = document.querySelector("#program").value;
        const year = document.querySelector("#year").value;
        const terms = document.querySelector("#terms").checked;

        const namePattern = /^[A-Za-z ]+$/;
        const mobilePattern = /^[0-9]{10}$/;

        if (name === "" || !namePattern.test(name)) {
            alert("Enter a valid first name.");
            return;
        }

        if (lastName === "" || !namePattern.test(lastName)) {
            alert("Enter a valid last name.");
            return;
        }

        if (email === "" || !email.includes("@")) {
            alert("Enter a valid email.");
            return;
        }

        if (!mobilePattern.test(mobile)) {
            alert("Mobile number must contain 10 digits.");
            return;
        }

        if (password.length < 8) {
            alert("Password must be at least 8 characters.");
            return;
        }

        if (password !== confirmPassword) {
            alert("Passwords do not match.");
            return;
        }

        if (program === "") {
            alert("Please select a program.");
            return;
        }

        if (year === "") {
            alert("Please select your year.");
            return;
        }

        if (!terms) {
            alert("Please accept the Terms and Conditions.");
            return;
        }

    });
}

