<?php

class Student {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function create($data) {
        $first = trim($data["first"] ?? "");
        $last = trim($data["last"] ?? "");
        $email = trim($data["email"] ?? "");
        $birthdate = $data["birthdate"] ?? "";
        $enrollment = $data["date_of_enrollment"] ?? "";

        if ($first == "" || $last == "" || $email == "" ||
            $birthdate == "" || $enrollment == "") {
            throw new InvalidArgumentException("All fields are required.");
        }

        if (strlen($first) > 100 || strlen($last) > 100 ||
            strlen($email) > 255) {
            throw new InvalidArgumentException("A field is too long.");
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("Invalid email.");
        }

        foreach ([$birthdate, $enrollment] as $date) {
            $check = DateTime::createFromFormat("!Y-m-d", $date);

            if (!$check || $check->format("Y-m-d") != $date) {
                throw new InvalidArgumentException("Invalid date.");
            }
        }

        $sql = "INSERT INTO students
                (first, last, email, birthdate, date_of_enrollment)
                VALUES (?, ?, ?, ?, ?)";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            $first,
            $last,
            $email,
            $birthdate,
            $enrollment
        ]);
    }


    public function update($id, $data) {
        $id = filter_var($id, FILTER_VALIDATE_INT);

        if (!$id || $id < 1) {
            throw new InvalidArgumentException("Invalid student ID.");
        }

        $first = trim($data["first"] ?? "");
        $last = trim($data["last"] ?? "");
        $email = trim($data["email"] ?? "");
        $birthdate = $data["birthdate"] ?? "";
        $enrollment = $data["date_of_enrollment"] ?? "";

        if ($first == "" || $last == "" || $email == "" ||
            $birthdate == "" || $enrollment == "") {
            throw new InvalidArgumentException("All fields are required.");
        }

        if (strlen($first) > 100 || strlen($last) > 100 ||
            strlen($email) > 255) {
            throw new InvalidArgumentException("A field is too long.");
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("Invalid email.");
        }

        foreach ([$birthdate, $enrollment] as $date) {
            $check = DateTime::createFromFormat("!Y-m-d", $date);

            if (!$check || $check->format("Y-m-d") != $date) {
                throw new InvalidArgumentException("Invalid date.");
            }
        }

        $sql = "UPDATE students
                SET first = ?, last = ?, email = ?,
                    birthdate = ?, date_of_enrollment = ?
                WHERE id = ?";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $first,
            $last,
            $email,
            $birthdate,
            $enrollment,
            $id
        ]);

        if ($stmt->rowCount() == 0) {
            $check = $this->pdo->prepare("SELECT id FROM students WHERE id = ?");
            $check->execute([$id]);

            if (!$check->fetch()) {
                throw new OutOfBoundsException("Student not found.");
            }
        }
    }
}
