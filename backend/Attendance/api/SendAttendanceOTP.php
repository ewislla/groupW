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

$matric_no = isset($_POST['matric_no']) ? trim($_POST['matric_no']) : '';

if (empty($matric_no)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Matriculation number is required."]);
    exit();
}

$result = $attendanceModel->RequestAttendanceOTP($matric_no);

if ($result['status'] === 'success') {
    http_response_code(200);
} else {
    http_response_code(400);
}

echo json_encode($result);