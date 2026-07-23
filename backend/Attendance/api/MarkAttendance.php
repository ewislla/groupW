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

// Grabbing variables safely from the frontend POST request
$session_id = isset($_POST['session_id']) ? (int)$_POST['session_id'] : 0;
$matric_no = isset($_POST['matric_no']) ? trim($_POST['matric_no']) : '';
$otp_code = isset($_POST['otp_code']) ? trim($_POST['otp_code']) : '';
$device_id = isset($_POST['device_id']) ? trim($_POST['device_id']) : '';
$student_lat = isset($_POST['student_lat']) ? (float)$_POST['student_lat'] : null;
$student_long = isset($_POST['student_long']) ? (float)$_POST['student_long'] : null;

// Basic frontend-level validation
if (!$session_id || empty($matric_no) || empty($otp_code) || empty($device_id) || $student_lat === null || $student_long === null) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Missing required attendance data. Please ensure location services are enabled."]);
    exit();
}

// Pass to the engine
$result = $attendanceModel->MarkAttendance(
    $session_id,
    $matric_no,
    $otp_code,
    $device_id,
    $student_lat,
    $student_long
);

// HTTP Status Codes for standard REST flow
if ($result['status'] === 'success') {
    http_response_code(201); // 201 Created
} else {
    http_response_code(400); // 400 Bad Request
}

echo json_encode($result);