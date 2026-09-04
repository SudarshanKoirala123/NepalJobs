// ========================================
// NEPALJOBS - MANAGE USERS
// ========================================

let users =
    JSON.parse(localStorage.getItem("nepalJobsUsers")) || [];

const usersTableBody =
    document.getElementById("usersTableBody");


// ========================================
// DISPLAY USERS
// ========================================

function displayUsers() {

    if (!usersTableBody) {
        return;
    }

    usersTableBody.innerHTML = "";


    // No users
    if (users.length === 0) {

        usersTableBody.innerHTML = `
            <tr>
                <td colspan="5">
                    No registered users found.
                </td>
            </tr>
        `;

        return;
    }


    // Display users
    users.forEach(function (user, index) {

        const row = document.createElement("tr");

        row.innerHTML = `
            <td>${index + 1}</td>

            <td>${user.name || "N/A"}</td>

            <td>${user.email || "N/A"}</td>

            <td>${user.role || "N/A"}</td>

            <td>
                <button
                    class="delete-btn"
                    onclick="deleteUser(${index})">
                    Delete
                </button>
            </td>
        `;

        usersTableBody.appendChild(row);

    });

}


// ========================================
// DELETE USER
// ========================================

function deleteUser(index) {

    const confirmDelete =
        confirm("Are you sure you want to delete this user?");

    if (!confirmDelete) {
        return;
    }

    users.splice(index, 1);

    localStorage.setItem(
        "nepalJobsUsers",
        JSON.stringify(users)
    );

    displayUsers();

}


// ========================================
// LOAD USERS
// ========================================

displayUsers();