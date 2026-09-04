// ========================================
// NEPALJOBS - LOGIN & ROLE SYSTEM
// ========================================

// Get login form
const loginForm = document.querySelector(".login-form");

// Check if login form exists
if (loginForm) {

    loginForm.addEventListener("submit", function(e) {

        // Prevent page refresh
        e.preventDefault();


        // Get login details
        const email =
            document.querySelector(".email").value.trim();

        const password =
            document.querySelector(".password").value;


        // ========================================
        // MAIN ADMIN LOGIN
        // ========================================
        // This account is NOT created through registration.

        const adminEmail = "admin@nepaljobs.com";
        const adminPassword = "admin123";


        if (
            email.toLowerCase() === adminEmail.toLowerCase() &&
            password === adminPassword
        ) {

            // Save Admin login
            const adminUser = {
                name: "NepalJobs Admin",
                email: adminEmail,
                role: "admin"
            };

            localStorage.setItem(
                "loggedInUser",
                JSON.stringify(adminUser)
            );

            alert("Admin login successful!");

            // Send Admin to Main Admin Dashboard
            window.location.href = "admin/admin.html";

            return;
        }


        // ========================================
        // GET REGISTERED USERS
        // ========================================

        const users =
            JSON.parse(
                localStorage.getItem("nepalJobsUsers")
            ) || [];


        // Find matching user
        const user = users.find(function(user) {

            return (
                user.email.toLowerCase() ===
                email.toLowerCase()
                &&
                user.password === password
            );

        });


        // ========================================
        // CHECK USER LOGIN
        // ========================================

        if (user) {

            // Save logged-in user
            localStorage.setItem(
                "loggedInUser",
                JSON.stringify(user)
            );


            // ========================================
            // EMPLOYER
            // ========================================

            if (user.role === "employer") {

                alert("Employer login successful!");

                // Your existing Employer Dashboard
                window.location.href =
                    "admin/dashboard.html";

                return;
            }


            // ========================================
            // JOB SEEKER
            // ========================================

            if (user.role === "jobseeker") {

                alert("Job Seeker login successful!");

                // Existing Job Seeker Dashboard
                window.location.href =
                    "seeker/dashboard.html";

                return;
            }


            // ========================================
            // UNKNOWN ROLE
            // ========================================

            alert("User role not recognized.");

        }

        else {

            // Login failed
            alert("Invalid email or password");

        }

    });

}