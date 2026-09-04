// ========================================
// NEPALJOBS - ADMIN LOGIN
// ========================================

const adminLoginForm = document.getElementById("adminLoginForm");

adminLoginForm.addEventListener("submit", function(event) {

    event.preventDefault();

    const adminId = document.getElementById("adminId").value.trim();
    const adminPassword = document.getElementById("adminPassword").value;

    // Temporary admin credentials
    const correctAdminId = "admin";
    const correctAdminPassword = "admin123";

    if (
        adminId === correctAdminId &&
        adminPassword === correctAdminPassword
    ) {

        // Save admin login status
        localStorage.setItem("adminLoggedIn", "true");

        // Open admin dashboard
        window.location.href = "admin.html";

    } else {

        const error = document.getElementById("adminLoginError");

        error.textContent = "Invalid Admin ID or Password.";
        error.style.color = "red";
    }

});