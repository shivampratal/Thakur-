<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Balance Adder</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #000; /* Black background */
        }
        .container {
            background: #808080; /* Gray container */
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
            width: 300px;
            text-align: center;
        }
        .container h2 {
            margin-bottom: 20px;
            color: #00ffff; /* Neon glowing blue */
            text-shadow: 0 0 20px #00ffff, 0 0 40px #00ffff; /* Glowing effect */
        }
        .container input[type="text"],
        .container input[type="number"] {
            width: 100%;
            padding: 10px;
            margin: 8px 0;
            border: 1px solid #ddd;
            border-radius: 4px;
            color: #00ff00; /* Neon glowing green for input text */
        }
        .container input::placeholder {
            color: #ffd700; /* Neon glowing golden for placeholders */
        }
        .container button {
            width: 100%;
            padding: 10px;
            background-color: #ff0000; /* Neon glowing red button */
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            text-shadow: 0 0 20px #ff0000, 0 0 40px #ff0000; /* Glowing effect */
        }
        .container button:hover {
            background-color: #ff4d4d; /* Lighter red on hover */
        }
        .message {
            margin-top: 20px;
            color: green;
        }
        .error {
            margin-top: 20px;
            color: red;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Balance Adder</h2>
    <input type="text" id="name" placeholder="Enter Name" required>
    <input type="number" id="coinLimit" placeholder="Enter Coin Limit" required>
    <button onclick="addBalance()">Add Balance</button>
    <div id="message" class="message"></div>
</div>

<script>
    function addBalance() {
        const name = document.getElementById("name").value;
        const coinLimit = document.getElementById("coinLimit").value;
        const messageBox = document.getElementById("message");

        if (name && coinLimit) {
            const xhr = new XMLHttpRequest();
            xhr.open("POST", "", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

            xhr.onreadystatechange = function () {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    const response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        messageBox.className = "message";
                        messageBox.innerText = response.message;
                    } else {
                        messageBox.className = "error";
                        messageBox.innerText = response.message;
                    }
                }
            };

            xhr.send("name=" + encodeURIComponent(name) + "&coinLimit=" + encodeURIComponent(coinLimit));
        } else {
            messageBox.className = "error";
            messageBox.innerText = "Please enter both name and coin limit.";
        }
    }
</script>

<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = $_POST["name"];
    $coinLimit = $_POST["coinLimit"];

    if (!empty($name) && is_numeric($coinLimit)) {
        $coinLimit = (int)$coinLimit;
        
        $file = 'coin.json';
        $data = [];

        if (file_exists($file)) {
            $data = json_decode(file_get_contents($file), true) ?? [];
        }

        $data[$name] = [
            "coin_limit" => $coinLimit
        ];

        if (file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT))) {
            echo json_encode(["success" => true, "message" => "Balance added successfully!"]);
        } else {
            echo json_encode(["success" => false, "message" => "Failed to save balance."]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "Invalid input."]);
    }
    exit;
}
?>

</body>
</html>