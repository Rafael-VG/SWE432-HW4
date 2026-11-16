<?php

class Transcript {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getTranscript($studentId) {
        $studentId = filter_var($studentId, FILTER_VALIDATE_INT);

        if (!$studentId || $studentId < 1) {
            throw new InvalidArgumentException("Invalid student ID.");
        }

        // Get the student's information.
        $sql = "SELECT id, first, last, email
                FROM students
                WHERE id = ?";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$studentId]);
        $student = $stmt->fetch();

        if (!$student) {
            throw new OutOfBoundsException("Student not found.");
        }

        // Get courses and grades for the student.
        $sql = "SELECT c.code, c.title, c.credits, e.grade
                FROM enrollments e
                JOIN courses c ON e.course_id = c.id
                WHERE e.student_id = ?
                ORDER BY c.code";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$studentId]);
        $courses = $stmt->fetchAll();

        // Calculate total enrolled credits.
        $sql = "SELECT COALESCE(SUM(c.credits), 0) AS total_credits
                FROM enrollments e
                JOIN courses c ON e.course_id = c.id
                WHERE e.student_id = ?";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$studentId]);
        $total = $stmt->fetch();

        return [
            "student" => $student,
            "courses" => $courses,
            "total_credits" => (int) $total["total_credits"]
        ];
    }
}
