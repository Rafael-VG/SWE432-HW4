<?php

header("Content-Type: application/json");

function fail($message, $status = 400) {
    http_response_code($status);
    echo json_encode(["error" => $message]);
    exit;
}

require_once "db.php";
require_once "classes/student.php";
require_once "classes/course.php";
require_once "classes/prerequisite.php";
require_once "classes/enrollment.php";
require_once "classes/recordmanager.php";
require_once "classes/transcript.php";

try {
    $action = $_GET["action"] ?? "";
    $method = $_SERVER["REQUEST_METHOD"];

    // Get courses for the dropdowns.
    if ($action == "getCourses" && $method == "GET") {
        $prerequisite = new Prerequisite(db());
        $courses = $prerequisite->getCourses();

        echo json_encode($courses);
        exit;
    }

    // Get students for the dropdowns.
    if ($action == "getStudents" && $method == "GET") {
        $enrollment = new Enrollment(db());
        $students = $enrollment->getStudents();

        echo json_encode($students);
        exit;
    }

    if ($action == "getRecords" && $method == "GET") {
        $table = $_GET["table"] ?? "";

        $manager = new RecordManager(db());
        $records = $manager->getRecords($table);

        echo json_encode($records);
        exit;
    }

    if ($action == "getTranscript" && $method == "GET") {
        $studentId = $_GET["student_id"] ?? null;

        $transcript = new Transcript(db());
        $result = $transcript->getTranscript($studentId);

        echo json_encode($result);
        exit;
    }

    
    if ($method != "POST") {
        fail("Method not allowed.", 405);
    }

    $data = json_decode(file_get_contents("php://input"), true);

    if (!is_array($data)) {
        fail("Invalid JSON data.");
    }

    if ($action == "createStudent") {
        $student = new Student(db());
        $student->create($data);

        $message = "Student added successfully!";

    } else if ($action == "updateStudent") {
    $student = new Student(db());
    $student->update($data["id"] ?? null, $data);

    $message = "Student updated successfully!";

    } else if ($action == "createCourse") {
        $course = new Course(db());
        $course->create($data);

        $message = "Course added successfully!";


    } else if ($action == "updateCourse") {
        $course = new Course(db());
        $course->update($data["id"] ?? null, $data);

        $message = "Course updated successfully!";

    } else if ($action == "createPrerequisite") {
        $prerequisite = new Prerequisite(db());
        $prerequisite->create($data);

        $message = "Prerequisite added successfully!";

    } else if ($action == "updatePrerequisite") {
        $prerequisite = new Prerequisite(db());
        $prerequisite->update($data["id"] ?? null, $data);

        $message = "Prerequisite updated successfully!";

    } else if ($action == "createEnrollment") {
        $enrollment = new Enrollment(db());
        $enrollment->create($data);

        $message = "Enrollment added successfully!";

    } else if ($action == "updateEnrollment") {
        $enrollment = new Enrollment(db());
        $enrollment->update($data["id"] ?? null, $data);

        $message = "Enrollment updated successfully!";
    
    } else if ($action == "deleteRecord") {
        $manager = new RecordManager(db());
        $manager->delete($data["table"] ?? "", $data["id"] ?? null);

        $message = "Record deleted successfully!";

    } else {
        fail("Unknown action.", 404);
    }

    http_response_code(
        in_array($action, [
            "updateStudent",
            "updateCourse",
            "updatePrerequisite",
            "updateEnrollment",
            "deleteRecord"
        ]) ? 200 : 201
    );

    echo json_encode(["message" => $message]);

} catch (InvalidArgumentException $e) {
    fail($e->getMessage(), 400);

} catch (DomainException $e) {
    fail($e->getMessage(), 409);

} catch (OutOfBoundsException $e) {
    fail($e->getMessage(), 404);

} catch (PDOException $e) {
    error_log($e->getMessage());

    if ($e->getCode() == "23505") {
        fail("This record already exists.", 409);
    }

    if ($e->getCode() == "23503") {
        fail("A referenced record does not exist.", 409);
    }

    if ($e->getCode() == "23514") {
        fail("The data violates a database constraint.", 400);
    }

    fail("Database operation failed.", 500);

} catch (Throwable $e) {
    error_log($e->getMessage());
    fail("Server error.", 500);
}
