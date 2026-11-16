CREATE TABLE IF NOT EXISTS students (
  id                 SERIAL PRIMARY KEY,
  first              VARCHAR(100) NOT NULL,
  last               VARCHAR(100) NOT NULL,
  email              VARCHAR(255) NOT NULL UNIQUE,
  birthdate          DATE NOT NULL,
  date_of_enrollment DATE NOT NULL
);

CREATE TABLE IF NOT EXISTS courses (
  id SERIAL PRIMARY KEY, 
  code VARCHAR(20) NOT NULL UNIQUE, 
  title VARCHAR(255) NOT NULL, 
  credits INTEGER      NOT NULL CHECK (credits BETWEEN 1 AND 6)
);

CREATE TABLE IF NOT EXISTS course_prerequisites (
  id SERIAL PRIMARY KEY, 
  course_id INTEGER NOT NULL, 
  prereq_id INTEGER NOT NULL,

  FOREIGN KEY (course_id) REFERENCES courses(id),
  FOREIGN KEY (prereq_id) REFERENCES courses(id),

  UNIQUE (course_id, prereq_id), 
  CHECK (course_id <> prereq_id)
);

CREATE TABLE IF NOT EXISTS enrollments (
  id SERIAL PRIMARY KEY, student_id INTEGER NOT NULL, 
  student_id INTEGER NOT NULL, 
  course_id INTEGER NOT NULL, 
  grade VARCHAR(2) NOT NULL,

  FOREIGN KEY (student_id) REFERENCES students(id),
  FOREIGN KEY (course_id) REFERENCES courses(id), 

  UNIQUE (student_id, course_id),

  CHECK (grade IN (
    'A', 'A-', 'B+', 'B', 'B-',
    'C+', 'C', 'C-', 'D', 'F'
  ))
);
