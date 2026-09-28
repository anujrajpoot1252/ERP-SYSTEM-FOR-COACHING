import { getData } from "../../js/api.js";

const tbody = document.getElementById("tbody");

async function loadStudents() {
    try {
        const students = await getData("/students/php/student_get.php");

        students.forEach((student) => {
            tbody.innerHTML += `
                <tr>
                    <td>${student.id}</td>
                    <td>${student.name}</td>
                    <td>${student.Phone_number}</td>
                    <td>${student.Email}</td>
                    <td>${student.course_id}</td>
                     <td>${student.batch_id}</td>
                </tr>
            `;
        });

    } catch (err) {
        console.error(err);
    }
}

loadStudents();