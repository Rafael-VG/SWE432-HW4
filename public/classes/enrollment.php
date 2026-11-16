<?php

class Enrollment {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getStudents() {
        $sql = "SELECT id, first, last FROM students ORDER BY last, first";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function create($data) {
        $studentId = filter_var($data["student_id"] ?? null, FILTER_VALIDATE_INT);
        $courseId = filter_var($data["course_id"] ?? null, FILTER_VALIDATE_INT);
        $grade = $data["grade"] ?? "";

        if (!$studentId || !$courseId || $studentId < 1 || $courseId < 1) {
            throw new InvalidArgumentException("Select a valid student and course.");
        }

        $grades = ["A", "A-", "B+", "B", "B-",
                   "C+", "C", "C-", "D", "F"];

        if (!in_array($grade, $grades, true)) {
            throw new InvalidArgumentException("Invalid grade.");
        }

        // Check that the student exists.
        $sql = "SELECT id FROM students WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$studentId]);

        if (!$stmt->fetch()) {
            throw new InvalidArgumentException("Student does not exist.");
        }

        // Check that the course exists.
        $sql = "SELECT id FROM courses WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$courseId]);

        if (!$stmt->fetch()) {
            throw new InvalidArgumentException("Course does not exist.");
        }

        // Check for duplicate enrollment.
        $sql = "SELECT id FROM enrollments
                WHERE student_id = ? AND course_id = ?";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$studentId, $courseId]);

        if ($stmt->fetch()) {
            throw new DomainException("Student is already enrolled in this course.");
        }

        // Add the enrollment.
        $sql = "INSERT INTO enrollments (student_id, course_id, grade)
                VALUES (?, ?, ?)";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$studentId, $courseId, $grade]);
    }

    public function update($id, $data) {
        $id = filter_var($id, FILTER_VALIDATE_INT);
        $studentId = filter_var($data["student_id"] ?? null, FILTER_VALIDATE_INT);
        $courseId = filter_var($data["course_id"] ?? null, FILTER_VALIDATE_INT);
        $grade = $data["grade"] ?? "";

        if (!$id || !$studentId || !$courseId ||
            $id < 1 || $studentId < 1 || $courseId < 1) {
            throw new InvalidArgumentException("Invalid enrollment information.");
        }

        $grades = ["A", "A-", "B+", "B", "B-",
                "C+", "C", "C-", "D", "F"];

        if (!in_array($grade, $grades, true)) {
            throw new InvalidArgumentException("Invalid grade.");
        }

        // Check that the enrollment exists.
        $sql = "SELECT id FROM enrollments WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);

        if (!$stmt->fetch()) {
            throw new OutOfBoundsException("Enrollment not found.");
        }

        // Check that the student exists.
        $sql = "SELECT id FROM students WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$studentId]);

        if (!$stmt->fetch()) {
            throw new InvalidArgumentException("Student does not exist.");
        }

        // Check that the course exists.
        $sql = "SELECT id FROM courses WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$courseId]);

        if (!$stmt->fetch()) {
            throw new InvalidArgumentException("Course does not exist.");
        }

        // Check for duplicate enrollments.
        $sql = "SELECT id FROM enrollments
                WHERE student_id = ?
                AND course_id = ?
                AND id <> ?";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$studentId, $courseId, $id]);

        if ($stmt->fetch()) {
            throw new DomainException(
                "Student is already enrolled in this course."
            );
        }

        // Update the enrollment.
        $sql = "UPDATE enrollments
                SET student_id = ?, course_id = ?, grade = ?
                WHERE id = ?";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$studentId, $courseId, $grade, $id]);
    }

}
