<?php

class Course {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function create($data) {
        $code = trim($data["code"] ?? "");
        $title = trim($data["title"] ?? "");
        $credits = $data["credits"] ?? "";

        if ($code == "" || $title == "" || $credits === "") {
            throw new InvalidArgumentException("All fields are required.");
        }

        if (strlen($code) > 20 || strlen($title) > 255) {
            throw new InvalidArgumentException("A field is too long.");
        }

        if (filter_var($credits, FILTER_VALIDATE_INT) === false ||
            $credits < 1 || $credits > 6) {
            throw new InvalidArgumentException(
                "Credits must be a whole number between 1 and 6."
            );
        }

        $sql = "INSERT INTO courses (code, title, credits)
                VALUES (?, ?, ?)";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([$code, $title, $credits]);
    }


    public function update($id, $data) {
        $id = filter_var($id, FILTER_VALIDATE_INT);

        if (!$id || $id < 1) {
            throw new InvalidArgumentException("Invalid course ID.");
        }

        $code = trim($data["code"] ?? "");
        $title = trim($data["title"] ?? "");
        $credits = $data["credits"] ?? "";

        if ($code == "" || $title == "" || $credits === "") {
            throw new InvalidArgumentException("All fields are required.");
        }

        if (strlen($code) > 20 || strlen($title) > 255) {
            throw new InvalidArgumentException("A field is too long.");
        }

        if (filter_var($credits, FILTER_VALIDATE_INT) === false ||
            $credits < 1 || $credits > 6) {
            throw new InvalidArgumentException(
                "Credits must be a whole number between 1 and 6."
            );
        }

        $sql = "UPDATE courses
                SET code = ?, title = ?, credits = ?
                WHERE id = ?";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$code, $title, $credits, $id]);

        // Check whether the course exists.
        $sql = "SELECT id FROM courses WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);

        if (!$stmt->fetch()) {
            throw new OutOfBoundsException("Course not found.");
        }
    }

}
