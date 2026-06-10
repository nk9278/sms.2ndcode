<?php
require_once '../config/database.php';
require_once '../classes/ApiHelper.php';
require_once '../classes/Website.php';
require_once '../classes/OtpService.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ApiHelper::sendResponse(false, 'Invalid request method');
}

$input = ApiHelper::getJsonInput();

$api_key = $input['api_key'] ?? '';
$mobile = $input['mobile'] ?? '';

if (empty($api_key) || empty($mobile)) {
    ApiHelper::sendResponse(false, 'Missing required parameters');
}

$websiteModel = new Website($pdo);
$website = $websiteModel->getWebsiteByApiKey($api_key);

if (!$website) {
    ApiHelper::sendResponse(false, 'Invalid API Key');
}

if ($website['status'] !== 'active') {
    ApiHelper::sendResponse(false, 'Website is not active');
}

$otpService = new OtpService($pdo);

if ($otpService->isDailyLimitExceeded($website['id'], $website['daily_limit'])) {
    ApiHelper::sendResponse(false, 'Daily OTP limit exceeded');
}

$otp = $otpService->generateOtp($website['id'], $mobile);

if ($otp) {
    // For this temporary phase, we're not actually sending an SMS.
    // In a real system, the SMS provider would be called here.

    // As per the prompt, we return the successful response.
    ApiHelper::sendResponse(true, 'OTP sent successfully');
} else {
    ApiHelper::sendResponse(false, 'Failed to generate OTP');
}
?>