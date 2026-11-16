<?php

class Prerequisite {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getCourses() {
        $sql = "SELECT id, code, title FROM courses ORDER BY code";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function create($data) {
        $courseId = filter_var($data["course_id"] ?? null, FILTER_VALIDATE_INT);
        $prereqId = filter_var($data["prereq_id"] ?? null, FILTER_VALIDATE_INT);

        if ($courseId === false || $courseId === null ||
            $prereqId === false || $prereqId === null ||
            $courseId < 1 || $prereqId < 1) {
            throw new InvalidArgumentException("Select valid courses.");
        }

        if ($courseId == $prereqId) {
            throw new InvalidArgumentException(
                "A course cannot be its own prerequisite."
            );
        }

        // Verify both courses exist.
        $sql = "SELECT id FROM courses WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);

        foreach ([$courseId, $prereqId] as $id) {
            $stmt->execute([$id]);

            if (!$stmt->fetch()) {
                throw new InvalidArgumentException("Course does not exist.");
            }
        }

        // Get all existing prerequisite relationships.
        $sql = "SELECT course_id, prereq_id FROM course_prerequisites";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $links = $stmt->fetchAll();

        // Check for a duplicate.
        foreach ($links as $link) {
            if ($link["course_id"] == $courseId &&
                $link["prereq_id"] == $prereqId) {
                throw new DomainException("Prerequisite already exists.");
            }
        }

        // Follow the prerequisite chain, starting at the proposed prerequisite.
        $toCheck = [$prereqId];
        $visited = [];

        while (count($toCheck) > 0) {
            $current = array_pop($toCheck);

            if ($current == $courseId) {
                throw new DomainException(
                    "This prerequisite creates a circular dependency."
                );
            }

            if (in_array($current, $visited)) {
                continue;
            }

            $visited[] = $current;

            foreach ($links as $link) {
                if ($link["course_id"] == $current) {
                    $toCheck[] = $link["prereq_id"];
                }
            }
        }

        // Safe to insert the new relationship.
        $sql = "INSERT INTO course_prerequisites (course_id, prereq_id)
                VALUES (?, ?)";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$courseId, $prereqId]);
    }

    public function update($id, $data) {
        $id = filter_var($id, FILTER_VALIDATE_INT);
        $courseId = filter_var($data["course_id"] ?? null, FILTER_VALIDATE_INT);
        $prereqId = filter_var($data["prereq_id"] ?? null, FILTER_VALIDATE_INT);

        if (!$id || !$courseId || !$prereqId ||
            $id < 1 || $courseId < 1 || $prereqId < 1) {
            throw new InvalidArgumentException("Invalid prerequisite information.");
        }

        if ($courseId == $prereqId) {
            throw new InvalidArgumentException(
                "A course cannot be its own prerequisite."
            );
        }

        // Check that the prerequisite record exists.
        $sql = "SELECT id FROM course_prerequisites WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);

        if (!$stmt->fetch()) {
            throw new OutOfBoundsException("Prerequisite not found.");
        }

        // Check that both courses exist.
        $sql = "SELECT id FROM courses WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);

        foreach ([$courseId, $prereqId] as $course) {
            $stmt->execute([$course]);

            if (!$stmt->fetch()) {
                throw new InvalidArgumentException("Course does not exist.");
            }
        }

        // Get all other prerequisite relationships.
        $sql = "SELECT course_id, prereq_id
                FROM course_prerequisites
                WHERE id <> ?";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        $links = $stmt->fetchAll();

        // Check for duplicate relationships.
        foreach ($links as $link) {
            if ($link["course_id"] == $courseId &&
                $link["prereq_id"] == $prereqId) {
                throw new DomainException("Prerequisite already exists.");
            }
        }

        // Check for circular prerequisites.
        $toCheck = [$prereqId];
        $visited = [];

        while (count($toCheck) > 0) {
            $current = array_pop($toCheck);

            if ($current == $courseId) {
                throw new DomainException(
                    "This prerequisite creates a circular dependency."
                );
            }

            if (in_array($current, $visited)) {
                continue;
            }

            $visited[] = $current;

            foreach ($links as $link) {
                if ($link["course_id"] == $current) {
                    $toCheck[] = $link["prereq_id"];
                }
            }
        }

        // Update the existing prerequisite.
        $sql = "UPDATE course_prerequisites
                SET course_id = ?, prereq_id = ?
                WHERE id = ?";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$courseId, $prereqId, $id]);
    }

}
