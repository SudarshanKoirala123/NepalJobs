// ========================================
// NEPALJOBS - REGISTRATION SYSTEM
// ========================================

const registerForm = document.getElementById("registerForm");

if (registerForm) {

    registerForm.addEventListener("submit", function (e) {

        e.preventDefault();

        // Get form values
        const name = document.getElementById("registerName").value.trim();
        const email = document.getElementById("registerEmail").value.trim();
        const password = document.getElementById("registerPassword").value;
        const role = document.getElementById("registerRole").value;

        // Check account type
        if (role === "") {
            alert("Please select an account type.");
            return;
        }

        // Get existing users
        let users = JSON.parse(
            localStorage.getItem("nepalJobsUsers")
        ) || [];

        // Check duplicate email
        const existingUser = users.find(function (user) {
            return user.email.toLowerCase() === email.toLowerCase();
        });

        if (existingUser) {
            alert("This email is already registered!");
            return;
        }

        // Create new user
        const newUser = {
            name: name,
            email: email,
            password: password,
            role: role
        };

        // Save user
        users.push(newUser);

        localStorage.setItem(
            "nepalJobsUsers",
            JSON.stringify(users)
        );

        // Show success message
        alert("Registration successful!");

        // Go to login
        window.location.href = "login.html";
    });
}