import { getData, postData } from "../../js/api.js";

const form = document.querySelector(".form");
const courseSelect = document.getElementById("course_id");
const batchSelect = document.getElementById("batch_id");


async function loadDropdowns() {
    try {
     
        const courses = await getData("/courses/course_get.php");
        courses.forEach((course) => {
            courseSelect.innerHTML += `
                <option value="${course.id}">${course.course_name}</option>
            `;
        });

       
        const batches = await getData("/courses/batch_get.php");
        batches.forEach((batch) => {
            batchSelect.innerHTML += `
                <option value="${batch.id}">${batch.batch_name}</option>
            `;
        });

    } catch (err) {
        console.error(err.response?.data || err.message);
    }
}

loadDropdowns();

// Register
form.addEventListener("submit", async (e) => {
    e.preventDefault();

    const studentData = {
        name: document.getElementById("name").value,
        phone: document.getElementById("phone").value,
        email: document.getElementById("email").value,
        password: document.getElementById("password").value,
        course_id: courseSelect.value,
        batch_id: batchSelect.value,
        admission_date: document.getElementById("admission_date").value
    };

    try {
        const result = await postData("/students/php/student_register.php", studentData);
        console.log(result);
        alert("Student registered successfully");
    } catch (err) {
        console.error(err.response?.data || err.message);
    }
});
