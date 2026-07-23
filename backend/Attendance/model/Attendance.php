<?php
include_once('../db/Connection.php');
include_once('../trait/BasicOperation.php');
include_once('../trait/Mailer.php');

class Attendance extends Connection
{
    use BasicOperation, Mailer;

    public function __construct()
    {
        parent::__construct();
        // Set timezone to match your local region (West Africa Time)
        date_default_timezone_set('Africa/Lagos');
    }

    public function StartSession(string $course_code, $latitude, $longitude)
    {
        // Auto-generate server-side timestamps for accuracy
        $class_date = date('Y-m-d');
        $start_time = date('H:i:s');

        // Basic validation
        if (empty(trim($course_code))) {
            return ["status" => "error", "message" => "Course code is required."];
        }

        if (empty($latitude) || empty($longitude)) {
            return ["status" => "error", "message" => "Admin location coordinates are required to anchor the classroom."];
        }

        // Execute the trait operation
        $sessionId = $this->InsertClassSession($course_code, $class_date, $start_time, $latitude, $longitude);

        if ($sessionId) {
            return [
                "status" => "success",
                "message" => "Class session started.",
                "session_id" => $sessionId
            ];
        } else {
            return ["status" => "error", "message" => "Failed to start class session due to a database error."];
        }
    }


    public function RequestAttendanceOTP(string $matric_no)
    {
        // 1. Check if the student exists
        if (!$this->recordExists('students', 'matric_no', $matric_no)) {
            return ["status" => "error", "message" => "Student not found. Please check your matriculation number."];
        }

        // 2. Generate OTP and strict database expiry timestamp
        $otp_code = sprintf("%06d", mt_rand(1, 999999));
        $db_otp_expiry = date("Y-m-d H:i:s", strtotime('+10 minutes'));

        // 3. Generate a human-readable time for the email template (e.g., 10:30 AM)
        $email_display_time = date("h:i A", strtotime($db_otp_expiry));

        // 4. Update the database using your BasicOperation trait
        $updateStatus = $this->UpdateStudentOTP($matric_no, $otp_code, $db_otp_expiry);

        if ($updateStatus) {
            // 5. Fetch the student's details
            // Assuming getRecord fetches a single associative array row
            $student = $this->getSingleRecord('students', 'matric_no', $matric_no);

            $email = $student['email'];
            $recipientName = $student['first_name'] . ' ' . $student['last_name'];

            // 6. Trigger your exact Mailer trait
            $mailSent = $this->sendOTPEmail($email, $recipientName, $otp_code, $email_display_time);

            if (!$mailSent) {
                return ["status" => "error", "message" => "OTP generated, but failed to send the email due to a server error."];
            }

            return [
                "status" => "success",
                "message" => "An OTP has been sent to your registered email.",
                "email_hint" => substr($email, 0, 3) . "***" . strstr($email, '@')
            ];
        } else {
            return ["status" => "error", "message" => "Failed to generate OTP due to a system error."];
        }
    }

    /**
     * Calculates the distance between two GPS coordinates in meters using the Haversine formula.
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // Earth's radius in meters

        $latDelta = deg2rad($lat2 - $lat1);
        $lonDelta = deg2rad($lon2 - $lon1);

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($lonDelta / 2) * sin($lonDelta / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c; // Returns distance in meters
    }

    public function MarkAttendance(int $session_id, string $matric_no, string $otp_code, string $device_id, $student_lat, $student_long)
    {
        // 1. Fetch Student Data
        $student = $this->getSingleRecord('students', 'matric_no', $matric_no);
        if (!$student) {
            return ["status" => "error", "message" => "Student not found."];
        }

        // 2. The Hardware Check
        if ($student['current_device_id'] !== $device_id) {
            return ["status" => "error", "message" => "Security Alert: This phone is not registered to this matriculation number."];
        }

        // 3. The OTP Check (Match and Expiry)
        if ($student['otp_code'] !== $otp_code) {
            return ["status" => "error", "message" => "Invalid OTP code."];
        }

        $current_time = date("Y-m-d H:i:s");
        if ($current_time > $student['otp_expiry']) {
            return ["status" => "error", "message" => "OTP has expired. Please request a new one."];
        }

        // 4. Fetch Session Data
        $session = $this->getSingleRecord('attendance_sessions', 'session_id', $session_id);
        if (!$session || $session['is_active'] === 'False') {
            return ["status" => "error", "message" => "This class session is closed or does not exist."];
        }

        // 5. The Location Check (Geofencing)
        $distance = $this->calculateDistance($session['latitude'], $session['longitude'], $student_lat, $student_long);
        $allowed_radius_in_meters = 50; // Set strictly to 50 meters

        if ($distance > $allowed_radius_in_meters) {
            return [
                "status" => "error",
                "message" => "You are too far from the classroom. (Distance: " . round($distance) . "m)"
            ];
        }

        // 6. The Final Commit
        $insertStatus = $this->InsertAttendanceRecord($session_id, $student['student_id'], $student_lat, $student_long);

        if ($insertStatus) {
            // 7. Destroy the OTP so it cannot be reused
            $this->ClearStudentOTP($student['student_id']);

            return ["status" => "success", "message" => "Attendance marked successfully!"];
        } else {
            // Because of the UNIQUE constraint we set on the database earlier, 
            // a duplicate entry will fail the insert and drop down to this error message.
            return ["status" => "error", "message" => "You have already been marked present for this class."];
        }
    }
}
