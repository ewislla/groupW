<?php
include_once("../db/Connection.php");
include_once("../trait/BasicOperation.php");
include_once("../trait/Validate.php");
include_once("../trait/Mailer.php");

class User extends Connection
{

    use BasicOperation, Validation, Mailer;

    public function __construct()
    {
        parent::__construct();
    }

    public function SeedUser()
    {
        //seed user
        $name = 'john';
        $password = 'john123';

        //first check to know if user exist 
        $check =  $this->recordExists('users', 'first_name', $name);
        if ($check) {
            return ["status" => "error", "message" => "A user aleady exist try to login or cantact support"];
        }

        //hash password and get token 
        $hash_password = password_hash($password, PASSWORD_DEFAULT);
        $token = bin2hex(random_bytes(32));
        //add user into db
        $seed_user = $this->InsertUser($name, 'doe', '', 'johndoe@gmail.com', $hash_password, 'Moderator', $token, 'true');

        if ($seed_user) {
            return ["status" => "success", "message" => "User seeded Successfully"];
        } else {
            return ["status" => "error", "message" => "Failed to Seed User due to a database error"];
        }
    }

    // to add users
    public function AddUser(
        string $first_name,
        string $last_name,
        string $middle_name,
        string $email,
        string $password,
        string $role,
        string $active
    ) {
        //validating users details
        $validationResult = $this->ValidateAddingUser($first_name, $last_name, $email, $password, $role, $active);

        if ($validationResult !== "valid") {
            return ["status" => "error", "message" => $validationResult];
        }

        //check if email already exist
        $emailExists = $this->recordExists('users', 'email', $email);
        if ($emailExists) {
            return ["status" => "error", "message" => "A student with this email already exists"];
        }

        //hashpassword
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // tokenise
        $token = bin2hex(random_bytes(32));


        $insertStatus =  $this->InsertUser($first_name, $last_name, $middle_name, $email, $hashed_password, $role, $token, $active);

        if ($insertStatus) {
            return ["status" => "success", "message" => "User Added  successfully"];
        } else {
            return ["status" => "error", "message" => "Failed to Add Student due to a database error"];
        }
    }


    public function loginUser(
        string $email,
        string $password,
        string $device_id
    ) {
        // Validate first
        $isValid = $this->ValidateLogin($email, $password);

        if ($isValid !== "valid") {
            return ["status" => "error", "message" => $isValid];
        }

        $user_data = $this->getSingleRecord('users', 'email', $email);

        // Check if the user DOES NOT exist
        if (!$user_data) {
            return ["status" => "error", "message" => "User does not exist. Try registering first."];
        }

        if (!empty($user_data['current_device_id']) && $user_data['current_device_id'] !== $device_id) {
            return [
                'status' => 'error',
                'message' => 'You are already logged in on another device. Please log out of that device first.'
            ];
        }

        // Verify password
        if (password_verify($password, $user_data['password'])) {

            // 1. Generate a random 6-digit number
            $otp_code = strtoupper(bin2hex(random_bytes(3)));

            // 2. Calculate the expiration time (10 minutes from exactly right now)
            $otp_expiry = date("Y-m-d H:i:s", strtotime("+10 minutes"));

            // 3. Save the OTP to the database
            $otpSaved = $this->setOTP($email, $otp_code, $otp_expiry);

            if ($otpSaved) {

                $emailSent = $this->sendOTPEmail($email, $user_data['first_name'], $otp_code, $otp_expiry);

                if ($emailSent) {
                    return [
                        "status" => "otp_sent",
                        "message" => "An authentication code has been sent to your email.",
                        "email" => $email,
                        "time_expire" => $user_data['otp_expiry']
                    ];
                } else {
                    // Wipes the OTP from DB if email failed to send to prevent ghost codes
                    $this->setOTP($email, NULL, NULL);
                    return ["status" => "error", "message" => "Failed to send OTP email. Please try again later."];
                }
            } else {
                return ["status" => "error", "message" => "Failed to initiate login process. Please try again."];
            }
        } else {
            return ["status" => "error", "message" => "Incorrect password"];
        }
    }

    // to get all users
    public function GetUser()
    {
        return  $this->getAllRecords('users');
    }

    public function resendOTP(string $email)
    {
        // 1. Verify the user exists
        $user_data = $this->getSingleRecord('users', 'email', $email);

        if (!$user_data) {
            return ["status" => "error", "message" => "Session expired. Please start the login process again."];
        }

        // Generate a fresh alphanumeric code
        $otp_code = strtoupper(bin2hex(random_bytes(3)));

        //  Reset the clock for another 10 minutes
        $otp_expiry = date("Y-m-d H:i:s", strtotime("+10 minutes"));

        //  Overwrite the old OTP in the database
        $otpSaved = $this->setOTP($email, $otp_code, $otp_expiry);

        if ($otpSaved) {
            //  Send the new email
            $emailSent = $this->sendOTPEmail($email, $user_data['first_name'], $otp_code, $otp_expiry);

            if ($emailSent) {
                return [
                    "status" => "success",
                    "message" => "A new authentication code has been sent to your email.",
                    "time_expire" => $user_data['otp_expiry']
                ];
            } else {
                $this->setOTP($email, NULL, NULL);
                return ["status" => "error", "message" => "Failed to send new OTP. Please try again later."];
            }
        } else {
            return ["status" => "error", "message" => "System error while generating new code."];
        }
    }

    public function verifyOTP(string $email, string $otp_code, ?string $device_id = null)
    {
        $user_data = $this->getSingleRecord('users', 'email', $email);

        if (!$user_data) {
            return ["status" => "error", "message" => "Session invalid. Please log in again."];
        }

        if (empty($user_data['otp_code'])) {
            return ["status" => "error", "message" => "No OTP requested or code already used."];
        }

        if (strtoupper($user_data['otp_code']) !== strtoupper($otp_code)) {
            return ["status" => "error", "message" => "Invalid authentication code."];
        }

        if (strtotime($user_data['otp_expiry']) < time()) {
            return ["status" => "error", "message" => "Authentication code has expired."];
        }

        // Wipe the OTP to prevent reuse
        $this->setOTP($email, NULL, NULL);

        // If this is a Login, generate token and save device
        if ($device_id !== null) {
            $new_token = bin2hex(random_bytes(16));
            $this->setDeviceAndToken($email, $new_token, $device_id);

            return [
                "status" => "success",
                "message" => "Login successful",
                "token" => $new_token
            ];
        }

        // If this is a Password Reset, just return success without token stuff
        return [
            "status" => "success",
            "message" => "OTP Verified successfully"
        ];
    }

    //  Request the Reset
    public function requestPasswordReset(string $email)
    {
        $check_valid = $this->ValidateReset($email);
        if ($check_valid !== "valid") {
            return ['status' => 'error', 'message' => $check_valid];
        }

        $user_data = $this->getSingleRecord('users', 'email', $email);
        if ($user_data == false) {
            return ['status' => 'error', 'message' => 'User does not exist'];
        }

        $otp_code = strtoupper(bin2hex(random_bytes(3)));
        $otp_expiry = date("Y-m-d H:i:s", strtotime("+10 minutes"));
        $otp_saved = $this->setOTP($email, $otp_code, $otp_expiry);

        if ($otp_saved) {
            $email_sent = $this->sendOTPEmail($email, $user_data['first_name'], $otp_code, $otp_expiry);

            if ($email_sent) {
                return [
                    "status" => "otp_sent",
                    "message" => "A password reset code has been sent to your email.",
                    "email" => $email,
                    "time_expire" => $otp_expiry // Fixed: Now returns the fresh timestamp
                ];
            } else {
                $this->setOTP($email, NULL, NULL);
                return ["status" => "error", "message" => "Failed to send reset email. Please try again later."];
            }
        }
        return ["status" => "error", "message" => "Failed to initiate password reset."];
    }

    // Verify and Update
    public function completePasswordReset(string $email, string $user_provided_otp, string $new_password)
    {
        // 1. Verify the code using your existing robust method
        $verify_code = $this->verifyOTP($email, $user_provided_otp);

        // verifyOTP returns an array with "status" => "success" if it passes
        if (isset($verify_code['status']) && $verify_code['status'] === "success") {

            // 2. Hash the new password
            $hash_password = password_hash($new_password, PASSWORD_DEFAULT);

            // 3. Store the new password (assuming this method exists in your BasicOperation trait)
            $reset = $this->updatePassword($hash_password, $email);

            if ($reset) {
                return ["status" => "success", "message" => "Password reset successful. You can now log in."];
            } else {
                return ["status" => "error", "message" => "Failed to update password in the database."];
            }
        }

        // If OTP fails, return the exact error message from verifyOTP
        return $verify_code;
    }

    public function logoutUser(string $token)
    {

        $logout_user = $this->logoutDevice($token);

        return $logout_user;
    }
}
