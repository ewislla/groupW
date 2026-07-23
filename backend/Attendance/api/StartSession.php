<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../model/Attendance.php';

$attendanceModel = new Attendance();

$course_code = isset($_POST['course_code']) ? trim($_POST['course_code']) : '';
$latitude = isset($_POST['latitude']) ? $_POST['latitude'] : '';
$longitude = isset($_POST['longitude']) ? $_POST['longitude'] : '';

$result = $attendanceModel->StartSession(
    $course_code,
    $latitude,
    $longitude
);

if ($result['status'] === 'success') {
    http_response_code(201); // 201 Created
} else {
    http_response_code(400); // 400 Bad Request
}

echo json_encode($result);