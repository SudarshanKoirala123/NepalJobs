// ========================================
// NEPALJOBS - MANAGE APPLICATIONS
// ========================================

const applications =
    JSON.parse(localStorage.getItem("jobApplications")) || [];

const applicationsTableBody =
    document.getElementById("applicationsTableBody");


// ========================================
// DISPLAY APPLICATIONS
// ========================================

function displayApplications() {

    if (!applicationsTableBody) {
        console.log("applicationsTableBody not found");
        return;
    }

    applicationsTableBody.innerHTML = "";


    // No applications
    if (applications.length === 0) {

        applicationsTableBody.innerHTML = `
            <tr>
                <td colspan="6">
                    No applications found.
                </td>
            </tr>
        `;

        return;
    }


    // Display applications
    applications.forEach((application, index) => {

        const row = document.createElement("tr");

        row.innerHTML = `
            <td>${index + 1}</td>

            <td>${application.name || application.applicantName || "N/A"}</td>

            <td>${application.email || application.applicantEmail || "N/A"}</td>

            <td>${application.jobTitle || application.job || "N/A"}</td>

            <td>${application.company || "N/A"}</td>

            <td>${application.status || "Pending"}</td>
        `;

        applicationsTableBody.appendChild(row);

    });
}


// ========================================
// RUN
// ========================================

displayApplications();