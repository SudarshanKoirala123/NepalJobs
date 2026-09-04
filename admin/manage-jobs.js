// ========================================
// NEPALJOBS - MANAGE JOBS
// ========================================

const jobs =
    JSON.parse(localStorage.getItem("nepalJobs")) || [];

const jobsTableBody =
    document.getElementById("jobsTableBody");

// ========================================
// DISPLAY JOBS
// ========================================

function displayJobs() {

    jobsTableBody.innerHTML = "";

    if (jobs.length === 0) {
        jobsTableBody.innerHTML = `
            <tr>
                <td colspan="6">
                    No jobs posted yet.
                </td>
            </tr>
        `;
        return;
    }

    jobs.forEach((job, index) => {

        const row = document.createElement("tr");

        row.innerHTML = `
            <td>${index + 1}</td>
            <td>${job.company || "N/A"}</td>
            <td>${job.title || job.jobTitle || "N/A"}</td>
            <td>${job.location || "N/A"}</td>
            <td>${job.salary || "Not specified"}</td>
            <td>
                <button
                    class="delete-btn"
                    onclick="deleteJob(${index})">
                    Delete
                </button>
            </td>
        `;

        jobsTableBody.appendChild(row);
    });
}

// ========================================
// DELETE JOB
// ========================================

function deleteJob(index) {

    if (!confirm("Are you sure you want to delete this job?")) {
        return;
    }

    jobs.splice(index, 1);

    localStorage.setItem(
        "nepalJobs",
        JSON.stringify(jobs)
    );

    displayJobs();
}

// ========================================
// LOAD
// ========================================

displayJobs();