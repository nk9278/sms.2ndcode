<?php

class OtpService {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function isDailyLimitExceeded($website_id, $daily_limit) {
        if ($daily_limit <= 0) {
            return false; // Unlimited
        }

        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM otp_logs WHERE website_id = ? AND DATE(created_at) = CURDATE()");
        $stmt->execute([$website_id]);
        $count = $stmt->fetchColumn();

        return $count >= $daily_limit;
    }

    public function generateOtp($website_id, $mobile) {
        // Firebase handles actual OTP generation. We just log the intent.
        $otp = 'FIREBASE';
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? null;

        // 5 minutes expiry for the quota log
        $expires_at = date('Y-m-d H:i:s', strtotime('+5 minutes'));

        $stmt = $this->pdo->prepare('INSERT INTO otp_logs (website_id, mobile, otp, status, ip_address, expires_at) VALUES (?, ?, ?, ?, ?, ?)');

        if ($stmt->execute([$website_id, $mobile, $otp, 'sent', $ip_address, $expires_at])) {
            return true;
        }

        return false;
    }

    private function verifyFirebaseToken($idToken) {
        $firebaseConfig = require __DIR__ . '/../config/firebase.php';
        $apiKey = $firebaseConfig['apiKey'] ?? '';

        $url = "https://identitytoolkit.googleapis.com/v1/accounts:lookup?key=" . $apiKey;
        $data = json_encode(['idToken' => $idToken]);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            $responseData = json_decode($response, true);
            if (isset($responseData['users'][0]['phoneNumber'])) {
                return $responseData['users'][0]['phoneNumber'];
            }
        }

        return false;
    }

    public function verifyOtp($website_id, $mobile, $otp) {
        // $otp here is actually the Firebase ID Token
        $stmt = $this->pdo->prepare("SELECT id, expires_at FROM otp_logs WHERE website_id = ? AND mobile = ? AND status = 'sent' ORDER BY id DESC LIMIT 1");
        $stmt->execute([$website_id, $mobile]);
        $log = $stmt->fetch();

        if (!$log) {
            return ['status' => false, 'message' => 'No pending OTP found'];
        }

        if (strtotime($log['expires_at']) < time()) {
            $update = $this->pdo->prepare("UPDATE otp_logs SET status = 'failed' WHERE id = ?");
            $update->execute([$log['id']]);
            return ['status' => false, 'message' => 'OTP has expired'];
        }

        $verifiedMobile = $this->verifyFirebaseToken($otp);

        if ($verifiedMobile === false) {
            $update = $this->pdo->prepare("UPDATE otp_logs SET status = 'failed' WHERE id = ?");
            $update->execute([$log['id']]);
            return ['status' => false, 'message' => 'Invalid Firebase Token'];
        }

        // Firebase returns phone number in E.164 format (+1234567890).
        // We should ensure the mobile numbers match, stripping out non-digits.
        $cleanRequestedMobile = preg_replace('/\D/', '', $mobile);
        $cleanVerifiedMobile = preg_replace('/\D/', '', $verifiedMobile);

        // Simple match strategy
        if (substr($cleanVerifiedMobile, -strlen($cleanRequestedMobile)) !== $cleanRequestedMobile && $cleanVerifiedMobile !== $cleanRequestedMobile) {
             $update = $this->pdo->prepare("UPDATE otp_logs SET status = 'failed' WHERE id = ?");
             $update->execute([$log['id']]);
             return ['status' => false, 'message' => 'Token phone number mismatch'];
        }

        // Token is valid
        $update = $this->pdo->prepare("UPDATE otp_logs SET status = 'verified' WHERE id = ?");
        $update->execute([$log['id']]);

        return ['status' => true, 'message' => 'OTP verified successfully'];
    }
}
?>