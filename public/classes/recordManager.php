<?php

class RecordManager {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getRecords($table) {

        if ($table == "students") {
            $sql = "SELECT * FROM students ORDER BY id";

        } else if ($table == "courses") {
            $sql = "SELECT * FROM courses ORDER BY code";

        } else if ($table == "prerequisites") {
            $sql = "SELECT p.id, p.course_id, p.prereq_id,
                           c.code AS course,
                           r.code AS prerequisite
                    FROM course_prerequisites p
                    JOIN courses c ON p.course_id = c.id
                    JOIN courses r ON p.prereq_id = r.id
                    ORDER BY c.code";

        } else if ($table == "enrollments") {
            $sql = "SELECT e.id, e.student_id, e.course_id,
                           s.first, s.last,
                           c.code AS course, e.grade
                    FROM enrollments e
                    JOIN students s ON e.student_id = s.id
                    JOIN courses c ON e.course_id = c.id
                    ORDER BY s.last";

        } else {
            throw new InvalidArgumentException("Invalid table.");
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll();
    }


    public function delete($table, $id) {
        $id = filter_var($id, FILTER_VALIDATE_INT);

        if (!$id || $id < 1) {
            throw new InvalidArgumentException("Invalid record ID.");
        }

        // Allow only our four tables.
        $tables = [
            "students" => "students",
            "courses" => "courses",
            "prerequisites" => "course_prerequisites",
            "enrollments" => "enrollments"
        ];

        if (!isset($tables[$table])) {
            throw new InvalidArgumentException("Invalid table.");
        }

        // Students with enrollments cannot be deleted.
        if ($table == "students") {
            $sql = "SELECT id FROM enrollments WHERE student_id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$id]);

            if ($stmt->fetch()) {
                throw new DomainException(
                    "Cannot delete student because they have enrollments."
                );
            }
        }

        // Courses used by other records cannot be deleted.
        if ($table == "courses") {
            $sql = "SELECT id FROM enrollments WHERE course_id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$id]);

            if ($stmt->fetch()) {
                throw new DomainException(
                    "Cannot delete course because it has enrollments."
                );
            }

            $sql = "SELECT id FROM course_prerequisites
                    WHERE course_id = ? OR prereq_id = ?";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$id, $id]);

            if ($stmt->fetch()) {
                throw new DomainException(
                    "Cannot delete course because it is used in prerequisites."
                );
            }
        }

        // Delete the requested record.
        $sql = "DELETE FROM " . $tables[$table] . " WHERE id = ?";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);

        if ($stmt->rowCount() == 0) {
            throw new OutOfBoundsException("Record not found.");
        }
    }

}
