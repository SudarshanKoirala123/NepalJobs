console.log("NepalJobs is running!");


// =========================
// REGISTER SYSTEM (MULTIPLE USERS)
// =========================

const registerForm = document.querySelector(".register-form");


if(registerForm){

    registerForm.addEventListener("submit", function(e){

        e.preventDefault();


        let name = document.querySelector(".name").value;
        let email = document.querySelector(".email").value;
        let password = document.querySelector(".password").value;



        let users = JSON.parse(localStorage.getItem("nepalJobsUsers")) || [];



        let existingUser = users.find(function(user){

            return user.email.toLowerCase() === email.toLowerCase();

        });



        if(existingUser){

            alert("This email is already registered!");

            return;

        }



        let newUser = {

            name:name,

            email:email,

            password:password

        };



        users.push(newUser);



        localStorage.setItem(
            "nepalJobsUsers",
            JSON.stringify(users)
        );



        alert("Registration successful!");

        window.location.href="login.html";


    });

}







// =========================
// LOGIN SYSTEM (MULTIPLE USERS)
// =========================


const loginForm = document.querySelector(".login-form");


if(loginForm){

    loginForm.addEventListener("submit", function(e){

        e.preventDefault();


        let email = document.querySelector(".email").value;

        let password = document.querySelector(".password").value;



        let users = JSON.parse(localStorage.getItem("nepalJobsUsers")) || [];



        let user = users.find(function(user){


            return (

                user.email.toLowerCase() === email.toLowerCase()

                &&

                user.password === password

            );


        });



        if(user){


            localStorage.setItem(
                "loggedInUser",
                JSON.stringify(user)
            );



            alert("Login successful!");

            window.location.href="index.html";


        }
        else{


            alert("Invalid email or password");


        }



    });


}









// =========================
// POST JOB SYSTEM
// =========================


const jobForm = document.querySelector(".job-form");


if(jobForm){


    jobForm.addEventListener("submit", function(e){


        e.preventDefault();



        let job = {


            company:
            document.querySelector(".company").value,


            title:
            document.querySelector(".job-title").value,


            location:
            document.querySelector(".location").value,


            salary:
            document.querySelector(".salary").value,


            description:
            document.querySelector(".description").value


        };




        let jobs =
        JSON.parse(localStorage.getItem("nepalJobs")) || [];






        // =========================
        // DUPLICATE JOB CHECK
        // =========================


        let duplicateJob = jobs.find(function(existingJob){


            return (

                existingJob.company.toLowerCase()
                ===
                job.company.toLowerCase()


                &&


                existingJob.title.toLowerCase()
                ===
                job.title.toLowerCase()


                &&


                existingJob.location.toLowerCase()
                ===
                job.location.toLowerCase()

            );


        });





        if(duplicateJob){


            alert("This job has already been posted!");

            return;


        }





        jobs.push(job);



        localStorage.setItem(

            "nepalJobs",

            JSON.stringify(jobs)

        );





        alert("Job posted successfully!");



        window.location.href="index.html";



    });


}









// =========================
// DISPLAY POSTED JOBS
// =========================

const jobContainer =
document.querySelector(".job-container");

if(jobContainer){

    let jobs =
    JSON.parse(localStorage.getItem("nepalJobs")) || [];

    jobs.forEach(function(job, index){

        createJobCard(job, jobContainer, index);

    });

}







// =========================
// CREATE JOB CARD FUNCTION
// =========================

function createJobCard(job, container, index){

    // Create a new job card
    let jobCard = document.createElement("div");

    // Add class
    jobCard.classList.add("job-card");

    // Job card HTML
    jobCard.innerHTML = `

        <h3>${job.title}</h3>

        <p><strong>Company:</strong> ${job.company}</p>

        <p><strong>Location:</strong> ${job.location}</p>

        <p><strong>Salary:</strong> ${job.salary}</p>

        <p>${job.description}</p>

        <button class="apply-btn">
            Apply Now
        </button>

        <button class="edit-btn">
            Edit
        </button>

        <button class="delete-btn">
            Delete
        </button>

    `;

    // Add card to page
    container.appendChild(jobCard);

}









// =========================
// APPLY FORM SYSTEM
// =========================

const applyForm = document.querySelector(".apply-form");


if(applyForm){

    applyForm.addEventListener("submit", function(e){

        e.preventDefault();


        let selectedJob = localStorage.getItem("selectedJob");


        let application = {

            jobTitle: selectedJob,

            name:
            document.querySelector(".applicant-name").value,


            email:
            document.querySelector(".applicant-email").value,


            phone:
            document.querySelector(".applicant-phone").value,


            message:
            document.querySelector(".applicant-message").value


        };



        let applications =
        JSON.parse(localStorage.getItem("jobApplications")) || [];





        // =========================
        // DUPLICATE APPLICATION CHECK
        // =========================


        let duplicateApplication =
        applications.find(function(existingApplication){


            return (

                existingApplication.email.toLowerCase()
                ===
                application.email.toLowerCase()


                &&


                existingApplication.jobTitle
                ===
                application.jobTitle

            );


        });





        if(duplicateApplication){


            alert("You have already applied for this job!");


            return;


        }





        applications.push(application);



        localStorage.setItem(

            "jobApplications",

            JSON.stringify(applications)

        );



        alert("Application submitted successfully!");



        window.location.href="index.html";


    });

}

          

      







   

// =========================
// EMPLOYER DASHBOARD SYSTEM
// =========================


const dashboardJobs =
document.querySelector(".dashboard-jobs");


const dashboardApplications =
document.querySelector(".dashboard-applications");









// =========================
// SHOW POSTED JOBS
// =========================


if(dashboardJobs){


    let jobs =
    JSON.parse(localStorage.getItem("nepalJobs")) || [];



    jobs.forEach(function(job){


        let card = document.createElement("div");


        card.classList.add("dashboard-card");



        card.innerHTML = `


        <h3>${job.title}</h3>


        <p>
        <strong>Company:</strong> ${job.company}
        </p>


        <p>
        <strong>Location:</strong> ${job.location}
        </p>


        <p>
        <strong>Salary:</strong> ${job.salary}
        </p>


        <p>
        ${job.description}
        </p>


        `;



        dashboardJobs.appendChild(card);


    });


}






// =========================
// SHOW APPLICATIONS
// =========================


if(dashboardApplications){


    let applications =
    JSON.parse(localStorage.getItem("jobApplications")) || [];



    applications.forEach(function(app){



        let card = document.createElement("div");


        card.classList.add("dashboard-card");



        card.innerHTML = `



        <h3>${app.name}</h3>



        <p>
        <strong>Applied For:</strong> ${app.jobTitle}
        </p>



        <p>
        <strong>Email:</strong> ${app.email}
        </p>



        <p>
        <strong>Phone:</strong> ${app.phone}
        </p>



        <p>
        ${app.message}
        </p>



        `;



        dashboardApplications.appendChild(card);



    });



}




   





// =========================
// USER PROFILE NAVBAR SYSTEM
// =========================


const buttonsArea =
document.querySelector(".buttons");



let loggedInUser =
JSON.parse(localStorage.getItem("loggedInUser"));




if(buttonsArea && loggedInUser){



    buttonsArea.innerHTML = `



    <div class="profile-menu">



        <button class="profile-btn">

            👤 ${loggedInUser.name} ▾

        </button>



        <div class="dropdown-menu">



            <a href="dashboard.html">
            Dashboard
            </a>



            <a href="my-applications.html">
My Applications
</a>



            <button class="logout-btn">
            Logout
            </button>



        </div>



    </div>



    `;





    const profileBtn =
    document.querySelector(".profile-btn");



    const dropdown =
    document.querySelector(".dropdown-menu");




    profileBtn.addEventListener("click", function(){


        dropdown.classList.toggle("show");


    });







    const logoutBtn =
    document.querySelector(".logout-btn");



    logoutBtn.addEventListener("click", function(){



        localStorage.removeItem("loggedInUser");



        alert("Logged out successfully");



        window.location.href="login.html";



    });



}






// =========================
// APPLY JOB
// =========================

const applyBtn = document.getElementById("applyBtn");

if (applyBtn) {

    applyBtn.addEventListener("click", function () {

        let loggedInUser =
        JSON.parse(localStorage.getItem("loggedInUser"));

        if(!loggedInUser){

            alert("Please login first.");

            window.location.href = "login.html";

            return;

        }

        let applications =
        JSON.parse(localStorage.getItem("jobApplications")) || [];

        let selectedJob =
        localStorage.getItem("selectedJob");

        let alreadyApplied =
        applications.find(function(app){

            return (

                app.email.toLowerCase() === loggedInUser.email.toLowerCase()

                &&

                app.jobTitle === selectedJob

            );

        });

        if(alreadyApplied){

            alert("You have already applied for this job!");

            return;

        }

        window.location.href = "apply.html";

    });

}







// =========================
// MY APPLICATIONS
// =========================

const myApplicationList =
document.querySelector(".my-application-list");

if(myApplicationList){

    let loggedInUser =
    JSON.parse(localStorage.getItem("loggedInUser"));

    let applications =
    JSON.parse(localStorage.getItem("jobApplications")) || [];

    let myApplications =
    applications.filter(function(app){

        return app.email === loggedInUser.email;

    });

    myApplications.forEach(function(app){

        let card = document.createElement("div");

        card.classList.add("application-card");

        card.innerHTML = `

        <h3>${app.jobTitle}</h3>

        <p><strong>Name:</strong> ${app.name}</p>

        <p><strong>Email:</strong> ${app.email}</p>

        <p><strong>Phone:</strong> ${app.phone}</p>

        <p>${app.message}</p>

        `;

        myApplicationList.appendChild(card);

    });

}