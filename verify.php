<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $input_code = $_POST['verification_code'];

    if (time() > $_SESSION['code_expiry']) {
        // 如果当前时间超过验证码的到期时间
        echo "<script>alert('Verification code has expired. Please try again.');</script>";
        header("Refresh:0; url=index.html"); // 返回前一页面
        exit();
    }

    if ($input_code == $_SESSION['verification_code']) {
        // 验证码正确，登录成功
        echo "Login successful!";
        
        // Store that the user has successfully verified
        $_SESSION['is_verified'] = true;

        // Redirect to the password management system
        header("Location: password_manager.php");
        exit();
    } else {
        echo "Incorrect verification code.";
    }
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verification</title>
    <style>
        /* 页面样式 */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f0f0f0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            width: 100%;
        }
        input[type="text"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }
        input[type="submit"] {
            width: 100%;
            background-color: #007bff;
            color: #fff;
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #0056b3;
        }
    </style>
    <script>
        // 计算剩余时间并显示倒计时
        function startCountdown(expiryTime) {
            var countdownElement = document.getElementById('countdown');
            var interval = setInterval(function() {
                var currentTime = Math.floor(Date.now() / 1000);
                var remainingTime = expiryTime - currentTime;

                if (remainingTime <= 0) {
                    clearInterval(interval);
                    countdownElement.innerHTML = "Code expired.";
                } else {
                    countdownElement.innerHTML = "Time remaining: " + remainingTime + " seconds";
                }
            }, 1000);
        }
    </script>
</head>
<body onload="startCountdown(<?php echo $_SESSION['code_expiry']; ?>)">

    <div class="container">
        <h2>Two-Factor Authentication</h2>
        <p>Please enter the verification code sent to your email.</p>
        <!--<p>Your verification code is: <strong><?php echo $_SESSION['verification_code'] ?? 'N/A'; ?></strong></p>-->
        <p id="countdown"></p> <!-- 剩余时间显示在这里 -->
        <form action="verify.php" method="post">
            <input type="text" name="verification_code" placeholder="Verification Code" required>
            <input type="submit" value="Verify">
        </form>
    </div>
</body>
</html>
