<?php
session_start();
include '../config.php';
if(!isset($_SESSION['student'])){ header("Location: login.php"); exit(); }

$email = $_SESSION['student'];
$student = mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM students WHERE email='$email'"));
$sid = $student['id'];

if(isset($_GET['job_id'])){
    $jid = (int)$_GET['job_id'];
    $exists = mysqli_fetch_assoc(mysqli_query($conn,"SELECT id FROM applications WHERE student_id='$sid' AND job_id='$jid'"));
    if(!$exists){
        mysqli_query($conn,"INSERT INTO applications(student_id,job_id,status) VALUES('$sid','$jid','Applied')");
    }
    header("Location: my_applications.php?msg=applied");
    exit();
}
header("Location: dashboard.php");
