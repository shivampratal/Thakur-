<?php
// Check if the info.json file exists, if not create it
$filename = 'info.json';
if (!file_exists($filename)) {
    file_put_contents($filename, json_encode([]));
}

// Check if the log.json file exists, if not create it
$logFilename = 'log.json';
if (!file_exists($logFilename)) {
    file_put_contents($logFilename, json_encode([]));
}

// Check if the login.json file exists, if not create it
$loginFilename = 'login.json';
if (!file_exists($loginFilename)) {
    file_put_contents($loginFilename, json_encode([]));
}

session_start(); // Start the session
$message = ""; // Variable to hold messages for the user

// Function to get the user's IP address
function getUserIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        return $_SERVER['REMOTE_ADDR'];
    }
}

// Check session status
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    // Check if user is accessing the restricted page within 5 minutes
    if (time() - $_SESSION['login_time'] <= 300) {
        header("Location: index.php"); // Redirect to index.php
        exit();
    } else {
        // Session expired
        session_destroy();
        header("Location: index.php");
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Load existing data
    $data = json_decode(file_get_contents($filename), true);

    // Handle registration
    if (isset($_POST['register'])) {
        $name = $_POST['name'];
        $password = $_POST['password'];
        $ip = getUserIP(); // Get the IP address

        // Check if user already exists
        foreach ($data as $user) {
            if ($user['name'] === $name) {
                $message = "User already exists.";
                break;
            }
        }

        // Save new user data if not exists
        if (empty($message)) {
            $data[] = [
                'number' => count($data) + 1, // Set user number
                'name' => $name,
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'ip' => $ip // Save the IP address
            ];
            file_put_contents($filename, json_encode($data));
            $message = "Registration successful!";
        }
    }

    // Handle login
    if (isset($_POST['login'])) {
        $name = $_POST['login_name'];
        $password = $_POST['login_password'];

        $userFound = false;

        foreach ($data as $user) {
            if ($user['name'] === $name && password_verify($password, $user['password'])) {
                $userFound = true;
                
                // Log login details to login.json
                $ip = getUserIP();
                $loginDetails = [
                    'name' => $name,
                    'ip' => $ip,
                    'login_time' => time() // Save login time
                ];

                // Load existing login data
                $loginData = json_decode(file_get_contents($loginFilename), true);
                $loginData[] = $loginDetails; // Append new login details
                file_put_contents($loginFilename, json_encode($loginData)); // Save back to login.json

                // Set session variables
                $_SESSION['logged_in'] = true;
                $_SESSION['login_time'] = time(); // Save login time
                $_SESSION['user_ip'] = $ip; // Save user IP
                
                header("Location: index.php");
                exit();
            }
        }

        if (!$userFound) {
            $message = "Your password or name is incorrect.";
        }
    }
}

// Get the total number of users
$totalUsers = count(json_decode(file_get_contents($filename), true));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login & Register</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: black;
            font-family: 'Roboto', sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            color: #ff4757; /* Neon glowing red */
        }

        .container {
            background: gray;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            padding: 40px;
            width: 90%;
            max-width: 400px;
            text-align: center;
        }

        h1 {
            margin-bottom: 20px;
            font-size: 2.5em;
            color: #ff4757; /* Neon glowing red */
            text-shadow: 0 0 10px #ff4757; /* Glowing effect */
        }

        input {
            width: 100%;
            padding: 15px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1em;
            color: #00ffff; /* Glowing blue text */
            background-color: black; /* Input background */
            transition: border-color 0.3s;
        }

        input:focus {
            border-color: #ff4757; /* Neon glowing red */
            outline: none;
        }

        button {
            width: 100%;
            padding: 15px;
            margin: 10px 0;
            border: none;
            border-radius: 5px;
            font-size: 1em;
            color: white;
            cursor: pointer;
            transition: background-color 0.3s, transform 0.3s;
        }

        button[name="register"] {
            background-color: #00ffff; /* Neon glowing blue */
        }

        button[name="login"] {
            background-color: #ff4757; /* Neon glowing red */
        }

        button:hover {
            transform: translateY(-2px);
        }

        .toggle {
            color: #ff4757; /* Neon glowing red */
            cursor: pointer;
            text-decoration: underline;
            margin-top: 15px;
        }

        .message {
            margin-top: 20px;
            color: #ff4757; /* Neon glowing red */
            font-weight: bold;
        }

        .total-users {
            margin-top: 20px;
            font-size: 1.2em;
            color: #00ffff; /* Neon glowing blue */
            text-shadow: 0 0 10px #00ffff; /* Glowing effect */
        }

        .fab {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: url('https://telegram.org/img/t_logo.png') no-repeat center center;
            background-size: cover;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            text-align: center;
            cursor: pointer;
            transition: transform 0.3s ease;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.3);
            z-index: 1000;
        }

        .fab:hover {
            transform: scale(1.1);
        }
    </style>
</head>
<body>
<div class="container">
    <div class="logo"></div>
    <div id="register-form" class="form-wrapper active">
    <h1>SERVER COPY</h1>
        <h1>Register</h1>
        <form method="POST">
            <input type="text" name="name" placeholder="Enter Name" required>
            <input type="password" name="password" placeholder="Enter Password" required>
            <button type="submit" name="register">Register</button>
        </form>
        <span class="toggle" onclick="toggleForms()">Already have an account? Login</span>
    </div>

    <div id="login-form" class="form-wrapper" style="display: none;">
        <h1>SERVER COPY</h1>
        <h1>Login</h1>
        <form method="POST">
            <input type="text" name="login_name" placeholder="Enter Name" required>
            <input type="password" name="login_password" placeholder="Enter Password" required>
            <button type="submit" name="login">Login</button>
        </form>
        <span class="toggle" onclick="toggleForms()">Don't have an account? Register</span>
    </div>

    <?php if (!empty($message)): ?>
        <div class="message"><?php echo $message; ?></div>
    <?php endif; ?>

    <div class="total-users">
        Total Members: <?php echo $totalUsers; ?>
    </div>
</div>

<a href="https://t.me/BLACKCYBERTEAM1" class="fab" target="_blank"></a>

<script>
    function toggleForms() {
        var registerForm = document.getElementById('register-form');
        var loginForm = document.getElementById('login-form');
        registerForm.classList.toggle('active');
        loginForm.classList.toggle('active');
        loginForm.style.display = loginForm.style.display === "none" ? "block" : "none";
        registerForm.style.display = registerForm.style.display === "none" ? "block" : "none";
    }
</script>

</body>
</html>