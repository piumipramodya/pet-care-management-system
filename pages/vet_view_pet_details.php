<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include('../includes/db.php');

// Ensure the user is logged in and is a veterinarian
if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'veterinarian') {
    die("Error: Not logged in as a veterinarian.");
}

// Check if pet_id is provided
if (!isset($_GET['pet_id']) || empty($_GET['pet_id'])) {
    die("Pet ID is not provided.");
}

$petID = intval($_GET['pet_id']); // Convert to integer

// Fetch pet details including owner's name
$petQuery = "SELECT Pet.*, User.username AS owner_name
             FROM Pet 
             JOIN User ON Pet.ownerID = User.userID
             WHERE Pet.petID = ?";
$stmt = $conn->prepare($petQuery);
$stmt->bind_param("i", $petID);
$stmt->execute();
$pet = $stmt->get_result()->fetch_assoc();

if (!$pet) {
    die("Error: Pet not found.");
}

// Fetch medical records for the pet
$recordQuery = "SELECT recordID, date, details 
                FROM MedicalRecord 
                WHERE petID = ? 
                ORDER BY date DESC";
$stmt = $conn->prepare($recordQuery);
$stmt->bind_param("i", $petID);
$stmt->execute();
$records = $stmt->get_result();

// Fetch prescriptions and group them by recordID
$prescriptionQuery = "SELECT P.recordID, P.medication, P.dosage 
                      FROM Prescription P 
                      JOIN MedicalRecord MR ON P.recordID = MR.recordID
                      WHERE MR.petID = ?";
$stmt = $conn->prepare($prescriptionQuery);
$stmt->bind_param("i", $petID);
$stmt->execute();
$prescriptionsResult = $stmt->get_result();

// Store prescriptions in an array grouped by recordID
$prescriptions = [];
while ($prescription = $prescriptionsResult->fetch_assoc()) {
    $prescriptions[$prescription['recordID']][] = $prescription;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pet Details - Vet View</title>
    <link rel="stylesheet" type="text/css" href="../assets/css/styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #2c2c2c;
            color: white;
            text-align: center;
        }

        .container {
            width: 80%;
            margin: auto;
            background: #333;
            padding: 20px;
            border-radius: 10px;
        }

        h1 {
            color: #f1c40f;
        }

        .pet-info {
            font-size: 18px;
            margin-bottom: 20px;
        }

        .record-box {
            border: 2px solid #f1c40f;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            background-color: #2c3e50;
            color: white;
            text-align: left;
        }

        .prescription-box {
            border-left: 4px solid #27ae60;
            padding-left: 15px;
            margin-top: 10px;
            background-color: #34495e;
            padding: 10px;
            border-radius: 5px;
        }

        .btn {
            display: inline-block;
            padding: 10px 15px;
            margin-top: 10px;
            background: #f39c12;
            color: white;
            border-radius: 5px;
            text-decoration: none;
        }

        .btn:hover {
            background: #e67e22;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1><?= htmlspecialchars($pet['name']) ?>'s Details</h1>
        <div class="pet-info">
            <p><strong>Owner:</strong> <?= htmlspecialchars($pet['owner_name']) ?></p>
            <p><strong>Breed:</strong> <?= htmlspecialchars($pet['breed']) ?></p>
            <p><strong>Age:</strong> <?= htmlspecialchars($pet['age']) ?></p>
        </div>

        <h2 style="color: #f1c40f;">Medical Records & Prescriptions</h2>

        <?php if ($records->num_rows > 0): ?>
            <?php while ($record = $records->fetch_assoc()): ?>
                <div class="record-box">
                    <p><strong>Date:</strong> <?= htmlspecialchars($record['date']) ?></p>
                    <p><strong>Details:</strong> <?= htmlspecialchars($record['details']) ?></p>

                    <!-- Display prescriptions for this check-up -->
                    <?php if (isset($prescriptions[$record['recordID']])): ?>
                        <div class="prescription-box">
                            <h3>Prescriptions</h3>
                            <ul>
                                <?php foreach ($prescriptions[$record['recordID']] as $prescription): ?>
                                    <li>
                                        <strong>Medication:</strong> <?= htmlspecialchars($prescription['medication']) ?><br>
                                        <strong>Dosage:</strong> <?= htmlspecialchars($prescription['dosage']) ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php else: ?>
                        <p><em>No prescriptions for this check-up.</em></p>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No medical records found.</p>
        <?php endif; ?>

        <a href="vet_dashboard.php" class="btn">Back to Vet Dashboard</a>
    </div>
</body>

</html>