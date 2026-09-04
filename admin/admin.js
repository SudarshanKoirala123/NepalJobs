// ========================================
// NEPALJOBS - MAIN ADMIN DASHBOARD
// ========================================

// Get registered users
const users =
    JSON.parse(localStorage.getItem("nepalJobsUsers")) || [];

// Get posted jobs
const jobs =
    JSON.parse(localStorage.getItem("nepalJobs")) || [];

// Get job applications
const applications =
    JSON.parse(localStorage.getItem("jobApplications")) || [];


// ========================================
// COUNT TOTAL USERS
// ========================================

const totalUsers = users.length;


// ========================================
// COUNT EMPLOYERS
// ========================================

const totalEmployers = users.filter(function (user) {
    return user.role === "employer";
}).length;


// ========================================
// COUNT JOB SEEKERS
// ========================================

const totalJobSeekers = users.filter(function (user) {
    return user.role === "jobseeker";
}).length;


// ========================================
// COUNT TOTAL JOBS
// ========================================

const totalJobs = jobs.length;


// ========================================
// COUNT TOTAL APPLICATIONS
// ========================================

const totalApplications = applications.length;


// ========================================
// DISPLAY TOTAL USERS
// ========================================

const totalUsersElement =
    document.getElementById("totalUsers");

if (totalUsersElement) {
    totalUsersElement.textContent = totalUsers;
}


// ========================================
// DISPLAY EMPLOYERS
// ========================================

const totalEmployersElement =
    document.getElementById("totalEmployers");

if (totalEmployersElement) {
    totalEmployersElement.textContent = totalEmployers;
}


// ========================================
// DISPLAY JOB SEEKERS
// ========================================

const totalJobSeekersElement =
    document.getElementById("totalJobSeekers");

if (totalJobSeekersElement) {
    totalJobSeekersElement.textContent = totalJobSeekers;
}


// ========================================
// DISPLAY TOTAL JOBS
// ========================================

const totalJobsElement =
    document.getElementById("totalJobs");

if (totalJobsElement) {
    totalJobsElement.textContent = totalJobs;
}


// ========================================
// DISPLAY TOTAL APPLICATIONS
// ========================================

const totalApplicationsElement =
    document.getElementById("totalApplications");

if (totalApplicationsElement) {
    totalApplicationsElement.textContent = totalApplications;
}