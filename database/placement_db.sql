CREATE DATABASE placement_db;
USE placement_db;

CREATE TABLE students(
id INT AUTO_INCREMENT PRIMARY KEY,
name VARCHAR(100),
email VARCHAR(100),
password VARCHAR(50),
cgpa FLOAT,
department VARCHAR(50),
skills TEXT,
resume VARCHAR(255)
);

CREATE TABLE admin(
id INT AUTO_INCREMENT PRIMARY KEY,
username VARCHAR(50),
password VARCHAR(50)
);

INSERT INTO admin(username,password)
VALUES('admin','admin');

CREATE TABLE jobs(
id INT AUTO_INCREMENT PRIMARY KEY,
company VARCHAR(100),
role VARCHAR(100),
cgpa_required FLOAT,
skills TEXT
);

CREATE TABLE applications(
id INT AUTO_INCREMENT PRIMARY KEY,
student_id INT,
job_id INT,
status VARCHAR(50) DEFAULT 'Applied'
);
