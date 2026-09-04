// ========================================
// NEPALJOBS - MANAGE JOB SEEKERS
// ========================================

const users =
    JSON.parse(localStorage.getItem("nepalJobsUsers")) || [];

const jobseekersTableBody =
    document.getElementById("jobseekersTableBody");


// ========================================
// DISPLAY JOB SEEKERS
// ========================================

function displayJobSeekers() {

    if (!jobseekersTableBody) {
        return;
    }

    jobseekersTableBody.innerHTML = "";

    const jobseekers = users.filter(function (user) {
        return user.role === "jobseeker";
    });


    if (jobseekers.length === 0) {

        jobseekersTableBody.innerHTML = `
            <tr>
                <td colspan="4">
                    No registered job seekers found.
                </td>
            </tr>
        `;

        return;
    }


    jobseekers.forEach(function (jobseeker, index) {

        const row = document.createElement("tr");

        row.innerHTML = `
            <td>${index + 1}</td>
            <td>${jobseeker.name || "N/A"}</td>
            <td>${jobseeker.email || "N/A"}</td>
            <td>Job Seeker</td>
        `;

        jobseekersTableBody.appendChild(row);

    });
}


// ========================================
// LOAD JOB SEEKERS
// ========================================

displayJobSeekers();