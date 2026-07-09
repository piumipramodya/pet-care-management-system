<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
session_start();
include('../includes/db.php');

// Check if the user is logged in
if (!isset($_SESSION['userID'])) {
    header("Location: login.php");
    exit();
}

// Check if petID is provided
if (!isset($_GET['petID'])) {
    die("Pet ID is not provided.");
}

$petID = intval($_GET['petID']); // Sanitize petID
$userID = $_SESSION['userID'];

// Fetch pet details
$petQuery = "SELECT * FROM Pet WHERE petID = ? AND ownerID = ?";
$stmt = $conn->prepare($petQuery);
$stmt->bind_param("ii", $petID, $userID);
$stmt->execute();
$pet = $stmt->get_result()->fetch_assoc();

// Check if the pet exists and belongs to the user
if (!$pet) {
    die("Pet not found or you do not have permission to view it.");
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
    <title><?php echo htmlspecialchars($pet['name']); ?>'s Medical Records</title>
    <link rel="stylesheet" type="text/css" href="../assets/css/styles.css">
    <style>
        .record-box {
            border: 2px solid #f1c40f;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            background-color: #2c3e50;
            color: white;
        }

        .prescription-box {
            border-left: 4px solid #27ae60;
            padding-left: 15px;
            margin-top: 10px;
            background-color: #34495e;
            padding: 10px;
            border-radius: 5px;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1><?php echo htmlspecialchars($pet['name']); ?>'s Details</h1>
        <p><strong>Breed:</strong> <?php echo htmlspecialchars($pet['breed']); ?></p>
        <p><strong>Age:</strong> <?php echo htmlspecialchars($pet['age']); ?></p>

        <h2>Medical Records & Prescriptions</h2>
        <?php if ($records->num_rows > 0) { ?>
            <?php while ($record = $records->fetch_assoc()) { ?>
                <div class="record-box">
                    <p><strong>Date:</strong> <?php echo htmlspecialchars($record['date']); ?></p>
                    <p><strong>Details:</strong> <?php echo htmlspecialchars($record['details']); ?></p>

                    <!-- Display prescriptions for this check-up -->
                    <?php if (isset($prescriptions[$record['recordID']])) { ?>
                        <div class="prescription-box">
                            <h3>Prescriptions</h3>
                            <ul>
                                <?php foreach ($prescriptions[$record['recordID']] as $prescription) { ?>
                                    <li>
                                        <strong>Medication:</strong> <?php echo htmlspecialchars($prescription['medication']); ?><br>
                                        <strong>Dosage:</strong> <?php echo htmlspecialchars($prescription['dosage']); ?>
                                    </li>
                                <?php } ?>
                            </ul>
                        </div>
                    <?php } else { ?>
                        <p><em>No prescriptions for this check-up.</em></p>
                    <?php } ?>
                </div>
            <?php } ?>
        <?php } else { ?>
            <p>No medical records found.</p>
        <?php } ?>

        <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
    </div>
</body>

</html>