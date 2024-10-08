<?php
// Database configuration
$servername = "DESKTOP-UB454E1";
$database = "SQLinject";
$username = "sa"; // MS SQL username
$password = "123456"; // MS SQL password

// Create connection
$connectionInfo = array("Database" => $database, "UID" => $username, "PWD" => $password);
$conn = sqlsrv_connect($servername, $connectionInfo);

// Check connection
if ($conn === false) {
    die(print_r(sqlsrv_errors(), true));
}

// Initialize variables
$registration_successful = false;

// Start session to handle CAPTCHA
session_start();

// Taiwan ID validation function
function validateTaiwanID($id) {
    if (!preg_match("/^[A-Z][12][0-9]{8}$/", $id)) {
        return false;
    }
    $alphabetMap = array(
        'A'=>10,'B'=>11,'C'=>12,'D'=>13,'E'=>14,'F'=>15,'G'=>16,'H'=>17,'I'=>34,'J'=>18,
        'K'=>19,'L'=>20,'M'=>21,'N'=>22,'O'=>35,'P'=>23,'Q'=>24,'R'=>25,'S'=>26,'T'=>27,
        'U'=>28,'V'=>29,'W'=>32,'X'=>30,'Y'=>31,'Z'=>33
    );

    $idArray = str_split($id);
    $firstLetterValue = $alphabetMap[$idArray[0]];
    $sum = intval($firstLetterValue / 10) + ($firstLetterValue % 10) * 9;

    for ($i = 1; $i <= 8; $i++) {
        $sum += intval($idArray[$i]) * (9 - $i);
    }
    
    $sum += intval($idArray[9]);

    return $sum % 10 === 0;
}

// Process form data
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $email = $_POST['email'];
    $ID_number = $_POST['ID_number'];
    $birthday = $_POST['birthday'];
    $captcha = $_POST['captcha'];

    if (empty($username) || empty($password) || empty($confirm_password) || empty($email) || empty($ID_number) || empty($birthday) || empty($captcha)) {
        header("Location: error.php?error=All+fields+are+required");
        exit();
    } elseif ($password !== $confirm_password) {
        header("Location: error.php?error=Confirm+password+not+correct");
        exit();
    } elseif (!preg_match('/^(?=.*[a-zA-Z])(?=.*\d).+$/', $username)) {
        header("Location: error.php?error=Username+must+contain+at+least+one+letter+and+one+number.");
        exit();
    } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{7,}$/', $password)) {
        header("Location: error.php?error=Password+must+be+at+least+7+characters+long%2C+contain+both+upper+and+lower+case+letters%2C+and+at+least+one+number.");
        exit();
    } elseif (!validateTaiwanID($ID_number)) {
        header("Location: error.php?error=Invalid+Taiwan+ID+number.");
        exit();
    } elseif ($captcha !== $_SESSION['captcha_code']) {
        header("Location: error.php?error=Incorrect+CAPTCHA.");
        exit();
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO users (username, password, email, ID_number, birthday) VALUES (?, ?, ?, ?, ?)";
        $params = array($username, $hashed_password, $email, $ID_number, $birthday);
        $stmt = sqlsrv_query($conn, $sql, $params);

        if ($stmt) {
            $registration_successful = true;
        } else {
            echo "Error: " . print_r(sqlsrv_errors(), true);
        }
    }
}

// Close connection
sqlsrv_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Page</title>
    <style>
        .invalid {
            color: red;
        }

        .valid {
            color: green;
        }

        .hint {
            display: block;
            margin-top: 5px;
        }

        .success {
            display: none;
            color: green;
            font-size: 18px;
        }

        .hint-box {
            border: 1px solid #ccc;
            padding: 15px;
            background-color: #f9f9f9;
            width: 800px;
            margin-bottom: 20px;
        }

        .hint-box p {
            margin: 0;
        }

        .return-link {
            position: absolute;
            top: 10px;
            right: 10px;
            display: inline-block;
            padding: 10px 20px;
            background-color: #28a745; /* 綠色背景 */
            color: white; /* 字體顏色 */
            text-decoration: none; /* 移除連結下劃線 */
            border-radius: 5px; /* 圓角 */
            border: 2px solid #28a745; /* 邊框為綠色 */
        }

        .return-link:hover {
            background-color: white; /* 滑鼠懸停時背景變為白色 */
            color: #28a745; /* 滑鼠懸停時字體變為綠色 */
        }

        .form-group {
            display: flex;
            align-items: flex-start;
        }
        .hints {
            margin-left: 10px;
            display: inline-block;
        }

        .hints span {
            display: block;
        }
    </style>
    <script>
        function validateUsername() {
            const username = document.getElementById("username").value;
            const letterHint = document.getElementById("letter-hint");
            const numberHint = document.getElementById("number-hint");

            const hasLetter = /[a-zA-Z]/.test(username);
            const hasNumber = /\d/.test(username);

            if (hasLetter) {
                letterHint.classList.remove("invalid");
                letterHint.classList.add("valid");
            } else {
                letterHint.classList.remove("valid");
                letterHint.classList.add("invalid");
            }

            if (hasNumber) {
                numberHint.classList.remove("invalid");
                numberHint.classList.add("valid");
            } else {
                numberHint.classList.remove("valid");
                numberHint.classList.add("invalid");
            }
        }
        function validatePassword() {
            const password = document.getElementById("password").value;
            const lengthHint = document.getElementById("length-hint");
            const upperCaseHint = document.getElementById("uppercase-hint");
            const lowerCaseHint = document.getElementById("lowercase-hint");
            const numberHint = document.getElementById("password-number-hint");

            const hasLength = password.length >= 7;
            const hasUpperCase = /[A-Z]/.test(password);
            const hasLowerCase = /[a-z]/.test(password);
            const hasNumber = /\d/.test(password);

            if (hasLength) {
                lengthHint.classList.remove("invalid");
                lengthHint.classList.add("valid");
            } else {
                lengthHint.classList.remove("valid");
                lengthHint.classList.add("invalid");
            }

            if (hasUpperCase) {
                upperCaseHint.classList.remove("invalid");
                upperCaseHint.classList.add("valid");
            } else {
                upperCaseHint.classList.remove("valid");
                upperCaseHint.classList.add("invalid");
            }

            if (hasLowerCase) {
                lowerCaseHint.classList.remove("invalid");
                lowerCaseHint.classList.add("valid");
            } else {
                lowerCaseHint.classList.remove("valid");
                lowerCaseHint.classList.add("invalid");
            }

            if (hasNumber) {
                numberHint.classList.remove("invalid");
                numberHint.classList.add("valid");
            } else {
                numberHint.classList.remove("valid");
                numberHint.classList.add("invalid");
            }
        }
        function validateConfirmPassword() {
            const password = document.getElementById("password").value;
            const confirmPassword = document.getElementById("confirm_password").value;
            const passwordMatchHint = document.getElementById("password-match-hint");

            if (password !== confirmPassword) {
                passwordMatchHint.style.display = "inline";
                passwordMatchHint.classList.remove("valid");
                passwordMatchHint.classList.add("invalid");
            } else {
                passwordMatchHint.style.display = "none";
            }
        }


        function showSuccessAlert() {
            const alertBox = document.getElementById("success-alert");
            alertBox.style.display = "block";
        }

        window.onload = function() {
            <?php if ($registration_successful): ?>
            showSuccessAlert();
            <?php endif; ?>
        };
    </script>
</head>
<body>

<div id="success-alert" class="success">
    <h2>Registration Successful!</h2>
    <p>Your account has been created successfully. <a href="index.html">Return to Login</a></p>
</div>

<a href="index.html" class="return-link">Return to Login Page</a>

<?php if (!$registration_successful): ?>
    <h2>Register</h2>

    <div class="hint-box">
        <p>Username must contain at least one letter and one number.</p>
        <p>Password must be at least 7 characters long, contain both upper and lower case letters, and at least one number.</p>
    </div>

    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
    <div class="form-group">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" oninput="validateUsername()" required>
            <div class="hints">
                <span id="letter-hint" class="hint invalid">One letter</span>
                <span id="number-hint" class="hint invalid">One number</span>
            </div>
        </div>
        <br><br>

        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" oninput="validatePassword()" required>
            <div class="hints">
                <span id="length-hint" class="hint invalid">At least 7 characters</span>
                <span id="uppercase-hint" class="hint invalid">One uppercase letter</span>
                <span id="lowercase-hint" class="hint invalid">One lowercase letter</span>
                <span id="password-number-hint" class="hint invalid">One number</span>
            </div>
        </div>
        <br><br>
        
        <div class="form-group">
            <label for="confirm_password">Confirm Password：</label>
            <input type="password" id="confirm_password" name="confirm_password" oninput="validateConfirmPassword()" required>
            <span id="password-match-hint" class="hint invalid" style="margin-left:10px; display:none;">Differnt from first input</span>
        </div>


        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required><br><br>

        <label for="ID_number">ID Number:</label>
        <input type="text" id="ID_number" name="ID_number" required><br><br>

        <label for="birthday">Birthday:</label>
        <input type="date" id="birthday" name="birthday" required><br><br>

        <label for="captcha">Captcha:</label>
        <img id="captcha-image" src="captcha.php" alt="CAPTCHA Image"><br>
        <button type="button" onclick="refreshCaptcha()">Refresh Captcha</button><br>
        <input type="text" id="captcha" name="captcha" required><br><br>

        <input type="submit" value="Register">
    </form>
<?php endif; ?>

</body>
</html>
