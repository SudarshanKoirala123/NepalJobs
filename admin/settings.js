// ========================================
// NEPALJOBS - ADMIN SETTINGS
// ========================================

const loggedInUser =
    JSON.parse(localStorage.getItem("loggedInUser")) || null;


// Get elements
const userName = document.getElementById("userName");

const profileForm = document.getElementById("profileForm");
const profileName = document.getElementById("profileName");
const profileEmail = document.getElementById("profileEmail");
const profilePhone = document.getElementById("profilePhone");

const passwordForm = document.getElementById("passwordForm");
const currentPassword = document.getElementById("currentPassword");
const newPassword = document.getElementById("newPassword");
const confirmPassword = document.getElementById("confirmPassword");


// ========================================
// LOAD ADMIN INFORMATION
// ========================================

if (loggedInUser) {

    userName.textContent = loggedInUser.name || "Admin";

    profileName.value = loggedInUser.name || "";
    profileEmail.value = loggedInUser.email || "";
    profilePhone.value = loggedInUser.phone || "";

} else {

    userName.textContent = "Admin";

}


// ========================================
// UPDATE PROFILE
// ========================================

profileForm.addEventListener("submit", function(event) {

    event.preventDefault();

    if (!loggedInUser) {
        alert("Please login first.");
        return;
    }

    loggedInUser.name = profileName.value.trim();
    loggedInUser.email = profileEmail.value.trim();
    loggedInUser.phone = profilePhone.value.trim();

    localStorage.setItem(
        "loggedInUser",
        JSON.stringify(loggedInUser)
    );

    userName.textContent = loggedInUser.name;

    alert("Profile updated successfully!");

});


// ========================================
// CHANGE PASSWORD
// ========================================

passwordForm.addEventListener("submit", function(event) {

    event.preventDefault();

    if (!loggedInUser) {
        alert("Please login first.");
        return;
    }

    const current = currentPassword.value;
    const newPass = newPassword.value;
    const confirm = confirmPassword.value;


    if (current !== loggedInUser.password) {
        alert("Current password is incorrect.");
        return;
    }


    if (newPass !== confirm) {
        alert("New passwords do not match.");
        return;
    }


    if (newPass.length < 6) {
        alert("Password must be at least 6 characters.");
        return;
    }


    loggedInUser.password = newPass;


    localStorage.setItem(
        "loggedInUser",
        JSON.stringify(loggedInUser)
    );


    const users =
        JSON.parse(localStorage.getItem("nepalJobsUsers")) || [];


    const userIndex = users.findIndex(
        user => user.email === loggedInUser.email
    );


    if (userIndex !== -1) {

        users[userIndex].password = newPass;

        localStorage.setItem(
            "nepalJobsUsers",
            JSON.stringify(users)
        );

    }


    alert("Password changed successfully!");

    passwordForm.reset();

});