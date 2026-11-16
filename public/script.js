const content = document.getElementById("content");

document.getElementById("createBtn").addEventListener("click", showCreate);

function showCreate() {
    content.innerHTML = `
        <h2>Create Record</h2>

        <label for="table">Choose Table:</label>

        <select id="table">
            <option value="students">Students</option>
            <option value="courses">Courses</option>
            <option value="prerequisites">Prerequisites</option>
            <option value="enrollments">Enrollments</option>
        </select>

        <div id="formArea"></div>
    `;

    document.getElementById("table").addEventListener("change", showForm);

    showForm();
}

async function showForm() {
    const table = document.getElementById("table").value;
    const formArea = document.getElementById("formArea");

    formArea.replaceChildren();

    if (table === "students") {
        const form = document.createElement("form");

        form.innerHTML = `
            <h3>Add Student</h3>

            <label>First Name:</label>
            <input type="text" name="first" required>

            <label>Last Name:</label>
            <input type="text" name="last" required>

            <label>Email:</label>
            <input type="email" name="email" required>

            <label>Birthdate:</label>
            <input type="date" name="birthdate" required>

            <label>Enrollment Date:</label>
            <input type="date" name="date_of_enrollment" required>

            <button type="submit">Add Student</button>
        `;

        
        form.addEventListener("submit", async function(event) {
            event.preventDefault();

            const button = form.querySelector("button");
            const data = Object.fromEntries(new FormData(form));

            button.disabled = true;
            button.textContent = "Saving...";

            try {
                const response = await fetch("api.php?action=createStudent", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (!response.ok) {
                    throw new Error(result.error || "Unable to save student.");
                }

                alert(result.message);
                form.reset();

            } catch (error) {
                alert(error.message);

            } finally {
                button.disabled = false;
                button.textContent = "Add Student";
            }
        });

        formArea.appendChild(form);
    } else if (table === "courses") {
        const form = document.createElement("form");
    
        form.innerHTML = `
            <h3>Add Course</h3>
    
            <label>Course Code:</label>
            <input type="text" name="code" required>
    
            <label>Course Title:</label>
            <input type="text" name="title" required>
    
            <label>Credits:</label>
            <input type="number" name="credits" min="1" max="6" step="1" required>
    
            <button type="submit">Add Course</button>`;
    
        form.addEventListener("submit", async function(event) {
            event.preventDefault();
    
            const button = form.querySelector("button");
            const data = Object.fromEntries(new FormData(form));
    
            button.disabled = true;
            button.textContent = "Saving...";
    
            try {
                const response = await fetch("api.php?action=createCourse", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify(data)
                });
    
                const result = await response.json();
    
                if (!response.ok) {
                    throw new Error(result.error || "Unable to save course.");
                }
    
                alert(result.message);
                form.reset();
    
            } catch (error) {
                alert(error.message);
    
            } finally {
                button.disabled = false;
                button.textContent = "Add Course";
            }
        });
    
        formArea.appendChild(form);
    } else if (table === "prerequisites") {
        const form = document.createElement("form");
    
        form.innerHTML = `
            <h3>Add Prerequisite</h3>
    
            <label for="courseId">Course:</label>
            <select id="courseId" name="course_id" required></select>
    
            <label for="prereqId">Required Course:</label>
            <select id="prereqId" name="prereq_id" required></select>
    
            <button type="submit">Add Prerequisite</button>
    
            <p id="message"></p>
        `;
    
        formArea.appendChild(form);
    
        const courseSelect = form.querySelector("#courseId");
        const prereqSelect = form.querySelector("#prereqId");
        const message = form.querySelector("#message");
        const button = form.querySelector("button");
    
        button.disabled = true;
        message.textContent = "Loading courses...";
    
        // Load courses from PHP.
        try {
            const response = await fetch("api.php?action=getCourses");
            const courses = await response.json();
    
            if (!response.ok) {
                throw new Error(courses.error || "Could not load courses.");
            }
    
            for (const course of courses) {
                const label = course.code + " - " + course.title;
    
                courseSelect.add(new Option(label, course.id));
                prereqSelect.add(new Option(label, course.id));
            }
    
            if (courses.length < 2) {
                message.textContent = "Create at least two courses first.";
            } else {
                button.disabled = false;
                message.textContent = "";
            }
    
        } catch (error) {
            message.textContent = error.message;
        }
    
        form.addEventListener("submit", async function(event) {
            event.preventDefault();
    
            const data = Object.fromEntries(new FormData(form));
    
            button.disabled = true;
            message.textContent = "Saving...";
    
            try {
                const response = await fetch("api.php?action=createPrerequisite", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify(data)
                });
    
                const result = await response.json();
    
                if (!response.ok) {
                    throw new Error(result.error || "Unable to save prerequisite.");
                }
    
                message.textContent = result.message;
    
            } catch (error) {
                message.textContent = error.message;
    
            } finally {
                button.disabled = false;
            }
        });
    } 
    else if (table === "enrollments") {
        const form = document.createElement("form");
    
        form.innerHTML = `
            <h3>Add Enrollment</h3>
    
            <label for="studentId">Student:</label>
            <select id="studentId" name="student_id" required></select>
    
            <label for="enrollmentCourse">Course:</label>
            <select id="enrollmentCourse" name="course_id" required></select>
    
            <label for="grade">Grade:</label>
            <select id="grade" name="grade" required>
                <option value="A">A</option>
                <option value="A-">A-</option>
                <option value="B+">B+</option>
                <option value="B">B</option>
                <option value="B-">B-</option>
                <option value="C+">C+</option>
                <option value="C">C</option>
                <option value="C-">C-</option>
                <option value="D">D</option>
                <option value="F">F</option>
            </select>
    
            <button type="submit">Add Enrollment</button>
            <p id="message"></p>
        `;
    
        formArea.appendChild(form);
    
        const studentSelect = form.querySelector("#studentId");
        const courseSelect = form.querySelector("#enrollmentCourse");
        const button = form.querySelector("button");
        const message = form.querySelector("#message");
    
        button.disabled = true;
        message.textContent = "Loading...";
    
        try {
            const studentResponse = await fetch("api.php?action=getStudents");
            const courseResponse = await fetch("api.php?action=getCourses");
    
            const students = await studentResponse.json();
            const courses = await courseResponse.json();
    
            if (!studentResponse.ok || !courseResponse.ok) {
                throw new Error("Could not load students or courses.");
            }
    
            for (const student of students) {
                const name = student.first + " " + student.last;
                studentSelect.add(new Option(name, student.id));
            }
    
            for (const course of courses) {
                const name = course.code + " - " + course.title;
                courseSelect.add(new Option(name, course.id));
            }
    
            if (students.length === 0 || courses.length === 0) {
                message.textContent = "Add students and courses first.";
            } else {
                button.disabled = false;
                message.textContent = "";
            }
    
        } catch (error) {
            message.textContent = error.message;
        }
    
        form.addEventListener("submit", async function(event) {
            event.preventDefault();
    
            const data = Object.fromEntries(new FormData(form));
    
            button.disabled = true;
            message.textContent = "Saving...";
    
            try {
                const response = await fetch("api.php?action=createEnrollment", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify(data)
                });
    
                const result = await response.json();
    
                if (!response.ok) {
                    throw new Error(result.error || "Unable to add enrollment.");
                }
    
                message.textContent = result.message;
    
            } catch (error) {
                message.textContent = error.message;
    
            } finally {
                button.disabled = false;
            }
        });
    }


document.getElementById("manageBtn").addEventListener("click", showManage);

    function showManage() {
        content.innerHTML = `
            <h2>Manage Records</h2>

            <label for="manageTable">Choose Table:</label>

            <select id="manageTable">
                <option value="students">Students</option>
                <option value="courses">Courses</option>
                <option value="prerequisites">Prerequisites</option>
                <option value="enrollments">Enrollments</option>
            </select>

            <p id="manageMessage"></p>
            <div id="recordsArea"></div>
        `;

        document.getElementById("manageTable").addEventListener("change", loadRecords);

        loadRecords();
    }

    async function loadRecords() {
        const table = document.getElementById("manageTable").value;
        const area = document.getElementById("recordsArea");
        const message = document.getElementById("manageMessage");

        area.replaceChildren();
        message.textContent = "Loading records...";

        try {
            const response = await fetch(
                "api.php?action=getRecords&table=" + encodeURIComponent(table)
            );

            const records = await response.json();

            if (!response.ok) {
                throw new Error(records.error || "Unable to load records.");
            }

            message.textContent = "";

            if (records.length === 0) {
                message.textContent = "No records found.";
                return;
            }

            const columns = {
                students: ["first", "last", "email", "birthdate", "date_of_enrollment"],
                courses: ["code", "title", "credits"],
                prerequisites: ["course", "prerequisite"],
                enrollments: ["first", "last", "course", "grade"]
            };

            const tableElement = document.createElement("table");
            const header = document.createElement("tr");

            for (const column of columns[table]) {
                const th = document.createElement("th");
                th.textContent = column.replaceAll("_", " ");
                header.appendChild(th);
            }

            const actionHeader = document.createElement("th");
            actionHeader.textContent = "Actions";
            header.appendChild(actionHeader);

            tableElement.appendChild(header);

            for (const record of records) {
                const row = document.createElement("tr");

                for (const column of columns[table]) {
                    const td = document.createElement("td");
                    td.textContent = record[column] ?? "";
                    row.appendChild(td);
                }

                const actions = document.createElement("td");

                const edit = document.createElement("button");
                edit.textContent = "Edit";

                const remove = document.createElement("button");
                remove.textContent = "Delete";

                edit.addEventListener("click", function() {
                    if (table === "students") {
                        editStudent(record);
                    } else if (table === "courses") {
                        editCourse(record);
                    } else if (table === "prerequisites") {
                        editPrerequisite(record);
                    } else if (table === "enrollments") {
                        editEnrollment(record);
                    }
                });
                
                remove.addEventListener("click", function() {
                    deleteRecord(table, record);
                });

                actions.appendChild(edit);
                actions.appendChild(remove);

                row.appendChild(actions);
                tableElement.appendChild(row);
            }

            area.appendChild(tableElement);

        } catch (error) {
            message.textContent = error.message;
        }
    }


    function editStudent(student) {
        const area = document.getElementById("recordsArea");

        area.replaceChildren();

        const form = document.createElement("form");

        form.innerHTML = `
            <h3>Edit Student</h3>

            <label>First Name:</label>
            <input type="text" name="first" required>

            <label>Last Name:</label>
            <input type="text" name="last" required>

            <label>Email:</label>
            <input type="email" name="email" required>

            <label>Birthdate:</label>
            <input type="date" name="birthdate" required>

            <label>Enrollment Date:</label>
            <input type="date" name="date_of_enrollment" required>

            <button type="submit">Save Changes</button>
            <button type="button" id="cancelEdit">Cancel</button>

            <p id="editMessage"></p>
        `;

        form.elements["first"].value = student.first;
        form.elements["last"].value = student.last;
        form.elements["email"].value = student.email;
        form.elements["birthdate"].value = student.birthdate;
        form.elements["date_of_enrollment"].value = student.date_of_enrollment;

        area.appendChild(form);

        form.querySelector("#cancelEdit").addEventListener("click", loadRecords);

        form.addEventListener("submit", async function(event) {
            event.preventDefault();

            const data = Object.fromEntries(new FormData(form));
            data.id = student.id;

            const message = form.querySelector("#editMessage");
            const button = form.querySelector('button[type="submit"]');

            button.disabled = true;
            message.textContent = "Saving changes...";

            try {
                const response = await fetch("api.php?action=updateStudent", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (!response.ok) {
                    throw new Error(result.error || "Unable to update student.");
                }

                message.textContent = result.message;
                await loadRecords();

            } catch (error) {
                message.textContent = error.message;

            } finally {
                button.disabled = false;
            }
        });
    }


    function editCourse(course) {
        const area = document.getElementById("recordsArea");

        area.replaceChildren();

        const form = document.createElement("form");

        form.innerHTML = `
            <h3>Edit Course</h3>

            <label>Course Code:</label>
            <input type="text" name="code" required>

            <label>Course Title:</label>
            <input type="text" name="title" required>

            <label>Credits:</label>
            <input type="number" name="credits" min="1" max="6" step="1" required>

            <button type="submit">Save Changes</button>
            <button type="button" id="cancelEdit">Cancel</button>

            <p id="editMessage"></p>
        `;

        form.elements["code"].value = course.code;
        form.elements["title"].value = course.title;
        form.elements["credits"].value = course.credits;

        area.appendChild(form);

        form.querySelector("#cancelEdit").addEventListener("click", loadRecords);

        form.addEventListener("submit", async function(event) {
            event.preventDefault();

            const data = Object.fromEntries(new FormData(form));
            data.id = course.id;

            const message = form.querySelector("#editMessage");
            const button = form.querySelector('button[type="submit"]');

            button.disabled = true;
            message.textContent = "Saving changes...";

            try {
                const response = await fetch("api.php?action=updateCourse", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (!response.ok) {
                    throw new Error(result.error || "Unable to update course.");
                }

                message.textContent = result.message;
                await loadRecords();

            } catch (error) {
                message.textContent = error.message;

            } finally {
                button.disabled = false;
            }
        });
    }

    async function editPrerequisite(record) {
        const area = document.getElementById("recordsArea");

        area.replaceChildren();

        const form = document.createElement("form");

        form.innerHTML = `
            <h3>Edit Prerequisite</h3>

            <label for="editCourseId">Course:</label>
            <select id="editCourseId" name="course_id" required></select>

            <label for="editPrereqId">Required Course:</label>
            <select id="editPrereqId" name="prereq_id" required></select>

            <button type="submit">Save Changes</button>
            <button type="button" id="cancelEdit">Cancel</button>

            <p id="editMessage"></p>
        `;

        area.appendChild(form);

        const courseSelect = form.querySelector("#editCourseId");
        const prereqSelect = form.querySelector("#editPrereqId");
        const message = form.querySelector("#editMessage");
        const button = form.querySelector('button[type="submit"]');

        button.disabled = true;
        message.textContent = "Loading courses...";

        try {
            const response = await fetch("api.php?action=getCourses");
            const courses = await response.json();

            if (!response.ok) {
                throw new Error(courses.error || "Unable to load courses.");
            }

            for (const course of courses) {
                const label = course.code + " - " + course.title;

                courseSelect.add(new Option(label, course.id));
                prereqSelect.add(new Option(label, course.id));
            }

            // Select the current values.
            courseSelect.value = record.course_id;
            prereqSelect.value = record.prereq_id;

            button.disabled = false;
            message.textContent = "";

        } catch (error) {
            message.textContent = error.message;
        }

        form.querySelector("#cancelEdit").addEventListener("click", loadRecords);

        form.addEventListener("submit", async function(event) {
            event.preventDefault();

            const data = Object.fromEntries(new FormData(form));
            data.id = record.id;

            button.disabled = true;
            message.textContent = "Saving changes...";

            try {
                const response = await fetch("api.php?action=updatePrerequisite", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (!response.ok) {
                    throw new Error(result.error || "Unable to update prerequisite.");
                }

                message.textContent = result.message;
                await loadRecords();

            } catch (error) {
                message.textContent = error.message;

            } finally {
                button.disabled = false;
            }
        });
    }


    async function editEnrollment(record) {
        const area = document.getElementById("recordsArea");

        area.replaceChildren();

        const form = document.createElement("form");

        form.innerHTML = `
            <h3>Edit Enrollment</h3>

            <label for="editStudentId">Student:</label>
            <select id="editStudentId" name="student_id" required></select>

            <label for="editEnrollmentCourse">Course:</label>
            <select id="editEnrollmentCourse" name="course_id" required></select>

            <label for="editGrade">Grade:</label>
            <select id="editGrade" name="grade" required>
                <option value="A">A</option>
                <option value="A-">A-</option>
                <option value="B+">B+</option>
                <option value="B">B</option>
                <option value="B-">B-</option>
                <option value="C+">C+</option>
                <option value="C">C</option>
                <option value="C-">C-</option>
                <option value="D">D</option>
                <option value="F">F</option>
            </select>

            <button type="submit">Save Changes</button>
            <button type="button" id="cancelEdit">Cancel</button>

            <p id="editMessage"></p>
        `;

        area.appendChild(form);

        const studentSelect = form.querySelector("#editStudentId");
        const courseSelect = form.querySelector("#editEnrollmentCourse");
        const gradeSelect = form.querySelector("#editGrade");
        const message = form.querySelector("#editMessage");
        const button = form.querySelector('button[type="submit"]');

        button.disabled = true;
        message.textContent = "Loading students and courses...";

        try {
            const studentResponse = await fetch("api.php?action=getStudents");
            const courseResponse = await fetch("api.php?action=getCourses");

            const students = await studentResponse.json();
            const courses = await courseResponse.json();

            if (!studentResponse.ok || !courseResponse.ok) {
                throw new Error("Unable to load students or courses.");
            }

            for (const student of students) {
                const name = student.first + " " + student.last;
                studentSelect.add(new Option(name, student.id));
            }

            for (const course of courses) {
                const name = course.code + " - " + course.title;
                courseSelect.add(new Option(name, course.id));
            }

            // Select the current enrollment values.
            studentSelect.value = record.student_id;
            courseSelect.value = record.course_id;
            gradeSelect.value = record.grade;

            button.disabled = false;
            message.textContent = "";

        } catch (error) {
            message.textContent = error.message;
        }

        form.querySelector("#cancelEdit").addEventListener("click", loadRecords);

        form.addEventListener("submit", async function(event) {
            event.preventDefault();

            const data = Object.fromEntries(new FormData(form));
            data.id = record.id;

            button.disabled = true;
            message.textContent = "Saving changes...";

            try {
                const response = await fetch("api.php?action=updateEnrollment", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (!response.ok) {
                    throw new Error(
                        result.error || "Unable to update enrollment."
                    );
                }

                await loadRecords();

            } catch (error) {
                message.textContent = error.message;

            } finally {
                button.disabled = false;
            }
        });
    }


    async function deleteRecord(table, record) {

        const confirmed = confirm(
            "Are you sure you want to delete this record?"
        );

        if (!confirmed) {
            return;
        }

        const data = {
            table: table,
            id: record.id
        };

        const message = document.getElementById("manageMessage");

        message.textContent = "Deleting record...";

        try {
            const response = await fetch("api.php?action=deleteRecord", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (!response.ok) {
                throw new Error(result.error || "Unable to delete record.");
            }

            await loadRecords();

        } catch (error) {
            message.textContent = error.message;
        }
    }


document.getElementById("transcriptBtn").addEventListener("click", showTranscript);

    async function showTranscript() {
        content.innerHTML = `
            <h2>Student Transcript</h2>

            <label for="transcriptStudent">Choose Student:</label>
            <select id="transcriptStudent"></select>

            <button id="viewTranscript">View Transcript</button>

            <p id="transcriptMessage"></p>
            <div id="transcriptArea"></div>
        `;

        const studentSelect = document.getElementById("transcriptStudent");
        const button = document.getElementById("viewTranscript");
        const message = document.getElementById("transcriptMessage");

        button.disabled = true;
        message.textContent = "Loading students...";

        try {
            const response = await fetch("api.php?action=getStudents");
            const students = await response.json();

            if (!response.ok) {
                throw new Error(students.error || "Unable to load students.");
            }

            for (const student of students) {
                const name = student.first + " " + student.last;
                studentSelect.add(new Option(name, student.id));
            }

            if (students.length === 0) {
                message.textContent = "No students found. Add a student first.";
                return;
            }

            button.disabled = false;
            message.textContent = "";

        } catch (error) {
            message.textContent = error.message;
        }

        button.addEventListener("click", loadTranscript);
    }

    async function loadTranscript() {
        const studentId = document.getElementById("transcriptStudent").value;
        const area = document.getElementById("transcriptArea");
        const message = document.getElementById("transcriptMessage");

        area.replaceChildren();
        message.textContent = "Loading transcript...";

        try {
            const response = await fetch(
                "api.php?action=getTranscript&student_id=" +
                encodeURIComponent(studentId)
            );

            const result = await response.json();

            if (!response.ok) {
                throw new Error(result.error || "Unable to load transcript.");
            }

            message.textContent = "";

            const heading = document.createElement("h3");
            heading.textContent =
                result.student.first + " " + result.student.last;

            area.appendChild(heading);

            const email = document.createElement("p");
            email.textContent = "Email: " + result.student.email;
            area.appendChild(email);

            if (result.courses.length === 0) {
                const empty = document.createElement("p");
                empty.textContent = "This student has no enrollments.";
                area.appendChild(empty);
            } else {
                const table = document.createElement("table");

                const header = document.createElement("tr");

                for (const label of ["Course", "Title", "Credits", "Grade"]) {
                    const th = document.createElement("th");
                    th.textContent = label;
                    header.appendChild(th);
                }

                table.appendChild(header);

                for (const course of result.courses) {
                    const row = document.createElement("tr");

                    for (const column of ["code", "title", "credits", "grade"]) {
                        const td = document.createElement("td");
                        td.textContent = course[column];
                        row.appendChild(td);
                    }

                    table.appendChild(row);
                }

                area.appendChild(table);
            }

            const total = document.createElement("p");
            total.textContent = "Total Credits: " + result.total_credits;
            area.appendChild(total);

        } catch (error) {
            message.textContent = error.message;
        }
    }


}