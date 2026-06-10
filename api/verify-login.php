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
$otp = $input['otp'] ?? '';

if (empty($api_key) || empty($mobile) || empty($otp)) {
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
$result = $otpService->verifyOtp($website['id'], $mobile, $otp);

if ($result['status'] === true) {
    ApiHelper::sendResponse(true, $result['message'], ['website' => $website['name']]);
} else {
    ApiHelper::sendResponse(false, $result['message']);
}
?>