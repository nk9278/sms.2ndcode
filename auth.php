<?php
$firebaseConfig = require __DIR__ . '/config/firebase.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>2nd Code - OTP Verification</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css">
    <style>
        .auth-container { max-width: 400px; margin: 50px auto; }
        #otp-section, #success-section { display: none; }
        #recaptcha-container { margin-bottom: 15px; }
    </style>
</head>
<body class="bg-light">

<div class="container auth-container">
    <div class="card shadow-sm border-0">
        <div class="card-body p-4 text-center">
            <img src="/assets/img/logo.png" alt="2nd Code" class="img-fluid mb-4" style="max-height: 60px;">
            <h4 class="mb-3">Phone Verification</h4>

            <div id="alert-msg" class="alert d-none"></div>

            <div id="phone-section">
                <div class="mb-3">
                    <input type="text" id="api-key" class="form-control" placeholder="API Key" value="test_api_key_123">
                </div>
                <div class="mb-3">
                    <input type="tel" id="mobile" class="form-control" placeholder="Mobile Number (e.g. +1234567890)">
                </div>
                <div id="recaptcha-container"></div>
                <button id="send-otp-btn" class="btn btn-primary w-100">Send OTP</button>
            </div>

            <div id="otp-section">
                <div class="mb-3">
                    <input type="text" id="otp" class="form-control" placeholder="Enter 6-digit OTP">
                </div>
                <button id="verify-otp-btn" class="btn btn-primary w-100">Verify OTP</button>
            </div>

            <div id="success-section">
                <div class="alert alert-success">Authentication Successful!</div>
                <p>Welcome to <span id="website-name" class="fw-bold"></span></p>
                <button class="btn btn-outline-secondary w-100 mt-2" onclick="location.reload()">Start Over</button>
            </div>
        </div>
    </div>
</div>

<!-- Firebase SDK -->
<script src="https://www.gstatic.com/firebasejs/8.10.1/firebase-app.js"></script>
<script src="https://www.gstatic.com/firebasejs/8.10.1/firebase-auth.js"></script>

<script>
    const firebaseConfig = {
        apiKey: "<?php echo htmlspecialchars($firebaseConfig['apiKey']); ?>",
        authDomain: "<?php echo htmlspecialchars($firebaseConfig['authDomain']); ?>",
        projectId: "<?php echo htmlspecialchars($firebaseConfig['projectId']); ?>",
        appId: "<?php echo htmlspecialchars($firebaseConfig['appId']); ?>"
    };

    // Check if configuration exists before initializing
    if (firebaseConfig.apiKey !== 'YOUR_FIREBASE_API_KEY') {
        firebase.initializeApp(firebaseConfig);
    } else {
        showAlert("Firebase is not configured.", "danger");
    }

    let confirmationResultObj = null;

    function showAlert(msg, type) {
        const alertEl = document.getElementById('alert-msg');
        alertEl.textContent = msg;
        alertEl.className = 'alert alert-' + type;
    }

    function setupRecaptcha() {
        if (!window.recaptchaVerifier && firebase.auth) {
            window.recaptchaVerifier = new firebase.auth.RecaptchaVerifier('recaptcha-container', {
                'size': 'normal'
            });
            recaptchaVerifier.render();
        }
    }

    document.getElementById('send-otp-btn').addEventListener('click', async () => {
        const apiKey = document.getElementById('api-key').value.trim();
        const mobile = document.getElementById('mobile').value.trim();

        if (!apiKey || !mobile) {
            showAlert("API Key and Mobile number are required.", "danger");
            return;
        }

        document.getElementById('send-otp-btn').disabled = true;
        document.getElementById('send-otp-btn').textContent = 'Sending...';

        try {
            // Call internal API to check limits
            const res = await fetch('/api/request-login.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ api_key: apiKey, mobile: mobile })
            });
            const data = await res.json();

            if (!data.status) {
                showAlert(data.message, "danger");
                document.getElementById('send-otp-btn').disabled = false;
                document.getElementById('send-otp-btn').textContent = 'Send OTP';
                return;
            }

            // Proceed with Firebase Auth
            setupRecaptcha();
            const appVerifier = window.recaptchaVerifier;

            confirmationResultObj = await firebase.auth().signInWithPhoneNumber(mobile, appVerifier);

            document.getElementById('phone-section').style.display = 'none';
            document.getElementById('otp-section').style.display = 'block';
            showAlert(data.message, "success");

        } catch (error) {
            console.error(error);
            showAlert(error.message || "Failed to send OTP", "danger");
            document.getElementById('send-otp-btn').disabled = false;
            document.getElementById('send-otp-btn').textContent = 'Send OTP';
            if (window.recaptchaVerifier) window.recaptchaVerifier.clear();
        }
    });

    document.getElementById('verify-otp-btn').addEventListener('click', async () => {
        const otp = document.getElementById('otp').value.trim();
        const apiKey = document.getElementById('api-key').value.trim();
        const mobile = document.getElementById('mobile').value.trim();

        if (!otp) {
            showAlert("OTP is required.", "danger");
            return;
        }

        document.getElementById('verify-otp-btn').disabled = true;
        document.getElementById('verify-otp-btn').textContent = 'Verifying...';

        try {
            // Verify with Firebase first
            const result = await confirmationResultObj.confirm(otp);
            const idToken = await result.user.getIdToken();

            // Then verify with internal API using the ID Token
            const res = await fetch('/api/verify-login.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ api_key: apiKey, mobile: mobile, otp: idToken })
            });
            const data = await res.json();

            if (data.status) {
                document.getElementById('otp-section').style.display = 'none';
                document.getElementById('success-section').style.display = 'block';
                document.getElementById('website-name').textContent = data.website || 'Our Platform';
                showAlert(data.message, "success");
            } else {
                showAlert(data.message, "danger");
                document.getElementById('verify-otp-btn').disabled = false;
                document.getElementById('verify-otp-btn').textContent = 'Verify OTP';
            }

        } catch (error) {
            console.error(error);
            showAlert("Invalid or Expired OTP", "danger");
            document.getElementById('verify-otp-btn').disabled = false;
            document.getElementById('verify-otp-btn').textContent = 'Verify OTP';
        }
    });

    // Initialize recaptcha if firebase is ready
    window.onload = () => {
        if (firebaseConfig.apiKey !== 'YOUR_FIREBASE_API_KEY') {
            setupRecaptcha();
        }
    };
</script>

</body>
</html>