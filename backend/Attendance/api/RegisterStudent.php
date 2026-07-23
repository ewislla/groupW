<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../model/Student.php';

$studentModel = new Student();

// Grabbing variables safely from the frontend POST request
$first_name = isset($_POST['first_name']) ? trim($_POST['first_name']) : '';
$last_name = isset($_POST['last_name']) ? trim($_POST['last_name']) : '';
$middle_name = isset($_POST['middle_name']) ? trim($_POST['middle_name']) : '';
$gender = isset($_POST['gender']) ? trim($_POST['gender']) : '';
$matric_no = isset($_POST['matric_no']) ? trim($_POST['matric_no']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$level = isset($_POST['level']) ? trim($_POST['level']) : '';
$current_device_id = isset($_POST['current_device_id']) ? trim($_POST['current_device_id']) : '';
$dept_id = isset($_POST['department_id']) ? (int)$_POST['department_id'] : 0;
$fac_id = isset($_POST['faculty_id']) ? (int)$_POST['faculty_id'] : 0;

$result = $studentModel->RegisterStudent(
    $first_name,
    $last_name,
    $middle_name,
    $gender,
    $matric_no,
    $email,
    $level,
    $current_device_id,
    $dept_id,
    $fac_id
);

if ($result['status'] === 'success') {
    http_response_code(201);
} else {
    http_response_code(400);
}

echo json_encode($result);