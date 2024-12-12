<?php
session_start();

// Function to get client IP address
function getClientIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        return $_SERVER['REMOTE_ADDR'];
    }
}

$logFile = 'login.json';
$coinFile = 'coin.json';
$apiUrl = 'https://example.com/svsapizx.php?nid={nid}&dob={dob}';

$clientIP = getClientIP();
$loginData = json_decode(file_get_contents($logFile), true) ?? [];
$coinData = json_decode(file_get_contents($coinFile), true) ?? [];
$user = "Guest";
$coinLimit = 5;
$costPerSubmit = 5;

// Check if IP is in login.json and retrieve name and coin limit if available in coin.json
foreach ($loginData as $login) {
    if ($login['ip'] === $clientIP) {
        $user = $login['name'];
        if (isset($coinData[$user])) {
            $coinLimit = $coinData[$user]['coin_limit'];
        }
        break;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nid = $_POST['nid'] ?? '';
    $dob = $_POST['dob'] ?? '';
    $totalCost = $costPerSubmit;

    if ($coinLimit >= $totalCost) {
        // Update coin balance
        $coinData[$user]['coin_limit'] -= $totalCost;
        file_put_contents($coinFile, json_encode($coinData));

        // Fetch response from the API
        $apiUrlFormatted = str_replace(['{nid}', '{dob}'], [$nid, urlencode($dob)], $apiUrl);
        $apiResponse = file_get_contents($apiUrlFormatted);
        echo json_encode([
            'status' => 'success',
            'response' => json_decode($apiResponse, true),
            'remainingCoins' => $coinData[$user]['coin_limit']
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Insufficient coins']);
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NID Lookup</title>
    <style>
        body {
            margin: 0;
            font-family: 'Arial', sans-serif;
            background-image: url('https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQVcVmcFiADWEFD3-B18dsYSmmrrIU9ZDCnqkSSMZO-9w&s');
            background-size: cover;
            background-position: center;
            color: #ffffff;
            height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        .container {
            background: rgba(0, 0, 0, 0.7);
            padding: 20px 30px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.5);
            width: 80%;
            max-width: 500px;
        }
        h1 {
            font-size: 2.5em;
            color: #39ff14;
            text-shadow: 0 0 10px #39ff14, 0 0 20px #39ff14, 0 0 30px #39ff14;
            margin-bottom: 15px;
        }
        input, button {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: none;
            border-radius: 25px;
            font-size: 16px;
            outline: none;
        }
        input {
            background: rgba(255, 255, 255, 0.1);
            color: #ffffff;
            text-align: center;
        }
        input::placeholder {
            color: #cccccc;
        }
        button {
            background: linear-gradient(90deg, #ff0000, #ff7373);
            color: #ffffff;
            cursor: pointer;
        }
        #responseBox {
            background: rgba(0, 0, 0, 0.7);
            padding: 20px;
            border-radius: 15px;
            margin-top: 20px;
            width: 80%;
            max-width: 500px;
            color: #00c6ff;
            font-weight: bold;
            white-space: pre-wrap;
            overflow-y: auto;
            max-height: 200px;
        }
        #copyButton {
            display: none;
            background: #007bff;
            color: #fff;
            margin-top: 10px;
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        #copyButton:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Welcome, <?php echo htmlspecialchars($user); ?></h1>
        <p>Coins Available: <span id="coin_limit"><?php echo htmlspecialchars($coinLimit); ?></span></p>
        <p>Submit NID & DOB for 5 coins per request</p>
        <input type="text" id="nid" placeholder="Enter NID" required>
        <input type="text" id="dob" placeholder="Enter Date of Birth" required>
        <button id="submit" onclick="submitData()">Submit</button>
        <div id="responseBox"></div>
        <button id="copyButton" onclick="copyResponse()">Copy</button>
    </div>

    <script>
        function submitData() {
            const nid = document.getElementById("nid").value;
            const dob = document.getElementById("dob").value;
            const coinLimit = parseInt(document.getElementById("coin_limit").innerText);

            // Hide the copy button initially
            document.getElementById("copyButton").style.display = "none";

            if (coinLimit < 5) {
                document.getElementById("responseBox").innerText = "Insufficient coins.";
                return;
            }

            fetch("", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: new URLSearchParams({
                    nid: nid,
                    dob: dob
                })
            })
            .then(response => response.json())
            .then(data => {
                const responseBox = document.getElementById("responseBox");

                if (data.status === "success") {
                    responseBox.innerText = JSON.stringify(data.response, null, 2);
                    document.getElementById("coin_limit").innerText = data.remainingCoins;
                    document.getElementById("copyButton").style.display = "block";
                } else {
                    responseBox.innerText = data.message;
                }
            })
            .catch(error => {
                document.getElementById("responseBox").innerText = "Error: " + error;
            });
        }

        function copyResponse() {
            const responseBox = document.getElementById("responseBox");
            const text = responseBox.innerText;

            navigator.clipboard.writeText(text)
                .then(() => {
                    alert("Response copied to clipboard!");
                })
                .catch(err => {
                    alert("Failed to copy response: " + err);
                });
        }
    </script>
</body>
</html>