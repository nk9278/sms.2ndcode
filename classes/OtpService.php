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
        // Temporary local OTP generation
        $otp = rand(1111, 9999);
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? null;

        // 5 minutes expiry
        $expires_at = date('Y-m-d H:i:s', strtotime('+5 minutes'));

        $stmt = $this->pdo->prepare('INSERT INTO otp_logs (website_id, mobile, otp, status, ip_address, expires_at) VALUES (?, ?, ?, ?, ?, ?)');

        if ($stmt->execute([$website_id, $mobile, $otp, 'sent', $ip_address, $expires_at])) {
            return $otp;
        }

        return false;
    }

    public function verifyOtp($website_id, $mobile, $otp) {
        // Find the latest OTP sent to this mobile for this website
        $stmt = $this->pdo->prepare("SELECT id, otp, expires_at FROM otp_logs WHERE website_id = ? AND mobile = ? AND status = 'sent' ORDER BY id DESC LIMIT 1");
        $stmt->execute([$website_id, $mobile]);
        $log = $stmt->fetch();

        if (!$log) {
            return ['status' => false, 'message' => 'No pending OTP found'];
        }

        if ($log['otp'] !== $otp) {
            // Update status to failed
            $update = $this->pdo->prepare("UPDATE otp_logs SET status = 'failed' WHERE id = ?");
            $update->execute([$log['id']]);
            return ['status' => false, 'message' => 'Invalid OTP'];
        }

        if (strtotime($log['expires_at']) < time()) {
            // Update status to failed (expired)
            $update = $this->pdo->prepare("UPDATE otp_logs SET status = 'failed' WHERE id = ?");
            $update->execute([$log['id']]);
            return ['status' => false, 'message' => 'OTP has expired'];
        }

        // OTP is valid
        $update = $this->pdo->prepare("UPDATE otp_logs SET status = 'verified' WHERE id = ?");
        $update->execute([$log['id']]);

        return ['status' => true, 'message' => 'OTP verified successfully'];
    }
}
?>