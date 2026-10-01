
const API_URL = "/examm/ERP-SYSTEM-FOR-COACHING/teacher/attendance_api.php";
const batchSelect = document.getElementById("batch-select");
const dateInput = document.getElementById("attendance-date");
const loadButton = document.querySelector(".filter-button button");

const form = document.getElementById("attendance-form");
const studentList = document.querySelector(".student-list");

const presentCount = document.getElementById("present-count");
const absentCount = document.getElementById("absent-count");


const today = new Date();

const year = today.getFullYear();
const month = String(today.getMonth() + 1).padStart(2, "0");
const day = String(today.getDate()).padStart(2, "0");

dateInput.value = `${year}-${month}-${day}`;


function updateSummary() {

    let present = 0;
    let absent = 0;

    const students = studentList.querySelectorAll(
        ".student-row"
    );

    students.forEach(student => {

        const selected = student.querySelector(
            'input[type="radio"]:checked'
        );

        if (!selected) {
            return;
        }

        if (selected.value === "true") {
            present++;
        }

        if (selected.value === "false") {
            absent++;
        }

    });

    presentCount.textContent = present;
    absentCount.textContent = absent;
}


function createStudent(student) {

    const presentChecked =
        student.present === true
            ? "checked"
            : "";

    const absentChecked =
        student.present === false
            ? "checked"
            : "";

    return `

    <div
        class="student-row"
        data-student-id="${student.id}"
    >

        <div class="student-roll">

            <span class="mobile-label">
                Roll No
            </span>

            <strong>
                #${student.id}
            </strong>

        </div>


        <div class="student-info">

            <span class="mobile-label">
                Student
            </span>

            <strong>
                ${student.name}
            </strong>

        </div>


        <div class="status-options">

            <span class="mobile-label">
                Attendance
            </span>


            <label class="status-label present">

                <input
                    type="radio"
                    name="status[${student.id}]"
                    value="true"
                    ${presentChecked}
                >

                    <span>

                        <i class="fa-solid fa-check"></i>

                        Present

                    </span>

            </label>


            <label class="status-label absent">

                <input
                    type="radio"
                    name="status[${student.id}]"
                    value="false"
                    ${absentChecked}
                >

                    <span>

                        <i class="fa-solid fa-xmark"></i>

                        Absent

                    </span>

            </label>

        </div>

    </div>

    `;
}


studentList.addEventListener(
    "change",
    function (event) {

        if (
            event.target.matches(
                'input[type="radio"]'
            )
        ) {

            updateSummary();

        }

    }
);


async function loadStudents() {

    const batchId = batchSelect.value;
    const date = dateInput.value;

    if (!batchId) {

        alert("Please select a batch");

        return;
    }

    if (!date) {

        alert("Please select a date");

        return;
    }


    studentList.innerHTML = `

    <div style="
            padding:30px;
            text-align:center;
        ">

        Loading students...

    </div>

    `;


    try {

        const response = await fetch(
            `${API_URL}?batch_id=${batchId}&date=${date}`
        );

        const data = await response.json();

        console.log(data);





        if (!response.ok || !data.success) {

            throw new Error(
                data.message ||
                "Unable to load students"
            );

        }


        if (
            !data.students ||
            data.students.length === 0
        ) {

            studentList.innerHTML = `

                <div style="
                    padding:30px;
                    text-align:center;
                ">

                    No students found in this batch.

                </div>

            `;

            updateSummary();

            return;
        }


        studentList.innerHTML =
            data.students
                .map(createStudent)
                .join("");


        updateSummary();

    } catch (error) {

        console.error(error);


        studentList.innerHTML = `

    <div style="
                padding:30px;
                text-align:center;
                color:red;
            ">

        ${error.message}

    </div>

    `;


        presentCount.textContent = "0";
        absentCount.textContent = "0";

    }

}


async function saveAttendance() {

    const batchId = batchSelect.value;
    const date = dateInput.value;


    if (!batchId) {

        alert("Please select a batch");

        return;
    }


    if (!date) {

        alert("Please select a date");

        return;
    }


    const attendance = [];


    const students =
        studentList.querySelectorAll(
            ".student-row"
        );


    if (students.length === 0) {

        alert("No students available");

        return;
    }


    students.forEach(student => {

        const studentId =
            Number(
                student.dataset.studentId
            );


        const selected =
            student.querySelector(
                'input[type="radio"]:checked'
            );


        if (!selected) {
            return;
        }


        attendance.push({

            student_id: studentId,

            present:
                selected.value === "true"

        });

    });


    try {

        const response = await fetch(
            "attendance_api.php",
            {

                method: "POST",

                headers: {
                    "Content-Type": "application/json"
                },

                body: JSON.stringify({

                    batch_id:
                        Number(batchId),

                    date: date,

                    attendance:
                        attendance

                })

            }
        );


        const data =
            await response.json();


        if (
            !response.ok ||
            !data.success
        ) {

            throw new Error(
                data.message ||
                "Unable to save attendance"
            );

        }


        alert(data.message);


    } catch (error) {

        console.error(error);

        alert(error.message);

    }

}


loadButton.addEventListener(
    "click",
    loadStudents
);


form.addEventListener(
    "submit",
    function (event) {

        event.preventDefault();

        saveAttendance();

    }
);


updateSummary();

