<?php
session_start();
include('../includes/db.php'); // Include database connection

// Check if the user is logged in
if (!isset($_SESSION['userID'])) {
    header("Location: login.php");
    exit();
}

$userID = $_SESSION['userID'];
$message = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $petID = $_POST['petID'];
    $vetID = $_POST['vetID'];
    $date = $_POST['date'];

    // Check for duplicate appointments
    $checkQuery = "SELECT * FROM Appointment WHERE petID = ? AND vetID = ? AND date = ?";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bind_param("iis", $petID, $vetID, $date);
    $checkStmt->execute();
    $result = $checkStmt->get_result();

    if ($result->num_rows > 0) {
        $message = "An appointment for this pet with this veterinarian on this date already exists.";
    } else {
        // Insert appointment into the database
        $query = "INSERT INTO Appointment (petID, vetID, date, status) VALUES (?, ?, ?, 'booked')";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iis", $petID, $vetID, $date);

        if ($stmt->execute()) {
            $message = "Appointment booked successfully!";
        } else {
            $message = "Error: " . $stmt->error;
        }
    }
}

// Fetch pets owned by the logged-in user
$petQuery = "SELECT * FROM Pet WHERE ownerID = ?";
$stmt = $conn->prepare($petQuery);
$stmt->bind_param("i", $userID);
$stmt->execute();
$pets = $stmt->get_result();

// Fetch veterinarians
$vetQuery = "SELECT * FROM Veterinarian";
$vets = $conn->query($vetQuery);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Appointment</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #2c2c2c;
            color: #ffffff;
            margin: 0;
            padding: 0;
        }

        h1 {
            color: #ffcc00;
            text-align: center;
            margin-bottom: 20px;
        }

        .container {
            max-width: 500px;
            margin: 50px auto;
            padding: 20px;
            background-color: #333;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        form {
            margin-top: 20px;
        }

        form label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #ffffff;
            text-align: left;
        }

        form select,
        form input,
        form button {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #f9f9f9;
            color: #333;
            font-size: 16px;
            box-sizing: border-box;
            /* Ensures proper alignment and padding */
        }

        form input[type="datetime-local"] {
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
        }

        form button {
            background-color: #4caf50;
            color: white;
            cursor: pointer;
            border: none;
        }

        form button:hover {
            background-color: #45a049;
        }

        .message {
            text-align: center;
            padding: 10px;
            margin-top: 20px;
            border-radius: 5px;
            font-size: 16px;
        }

        .message.success {
            background-color: #dff0d8;
            color: #3c763d;
            border: 1px solid #4caf50;
        }

        .message.error {
            background-color: #ffe6e6;
            color: #ff0000;
            border: 1px solid #ff0000;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Book an Appointment</h1>
        <form method="POST">
            <label for="petID">Select Pet:</label>
            <select name="petID" required>
                <?php while ($pet = $pets->fetch_assoc()): ?>
                    <option value="<?= htmlspecialchars($pet['petID']) ?>"><?= htmlspecialchars($pet['name']) ?></option>
                <?php endwhile; ?>
            </select>

            <label for="vetID">Select Veterinarian:</label>
            <select name="vetID" required>
                <?php while ($vet = $vets->fetch_assoc()): ?>
                    <option value="<?= htmlspecialchars($vet['vetID']) ?>"><?= htmlspecialchars($vet['name']) ?></option>
                <?php endwhile; ?>
            </select>

            <label for="date">Appointment Date:</label>
            <input type="datetime-local" name="date" required>

            <button type="submit">Book Appointment</button>
        </form>

        <?php if ($message): ?>
            <p class="message <?= strpos($message, 'Error') !== false ? 'error' : 'success' ?>">
                <?= htmlspecialchars($message) ?>
            </p>
        <?php endif; ?>
    </div>
</body>

</html>