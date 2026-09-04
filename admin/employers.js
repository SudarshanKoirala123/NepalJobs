// ========================================
// NEPALJOBS - MANAGE EMPLOYERS
// ========================================

const users =
    JSON.parse(localStorage.getItem("nepalJobsUsers")) || [];

const employersTableBody =
    document.getElementById("employersTableBody");


// ========================================
// DISPLAY EMPLOYERS
// ========================================

function displayEmployers() {

    if (!employersTableBody) {
        return;
    }

    employersTableBody.innerHTML = "";

    const employers = users.filter(function (user) {
        return user.role === "employer";
    });


    if (employers.length === 0) {

        employersTableBody.innerHTML = `
            <tr>
                <td colspan="4">
                    No registered employers found.
                </td>
            </tr>
        `;

        return;
    }


    employers.forEach(function (employer, index) {

        const row = document.createElement("tr");

        row.innerHTML = `
            <td>${index + 1}</td>
            <td>${employer.name || "N/A"}</td>
            <td>${employer.email || "N/A"}</td>
            <td>Employer</td>
        `;

        employersTableBody.appendChild(row);

    });
}


// ========================================
// LOAD EMPLOYERS
// ========================================

displayEmployers();