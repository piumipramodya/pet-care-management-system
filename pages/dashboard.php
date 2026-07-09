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

$userID = $_SESSION['userID'];

// Fetch user details
$userQuery = "SELECT * FROM User WHERE userID = ?";
$stmt = $conn->prepare($userQuery);
$stmt->bind_param("i", $userID);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Check user role
$role = $_SESSION['role'] ?? 'user';  // Default to 'user' if not set

// Fetch pets of the logged-in user along with details (for pet owners only)
$petQuery = "SELECT petID, name, breed, age FROM Pet WHERE ownerID = ?";
$stmt = $conn->prepare($petQuery);
$stmt->bind_param("i", $userID);
$stmt->execute();
$pets = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" type="text/css" href="../assets/css/styles.css">
</head>

<body>
    <div class="container">
        <h1>Welcome, <?php echo htmlspecialchars($user['username']); ?>!</h1>

        <!-- Show "Your Pets" only for pet owners -->
        <?php if ($role !== 'veterinarian') { ?>
            <h2>Your Pets</h2>
            <ul>
                <?php if ($pets->num_rows > 0) { ?>
                    <?php while ($pet = $pets->fetch_assoc()) { ?>
                        <li>
                            <strong><?php echo htmlspecialchars($pet['name']); ?></strong>
                            (<?php echo htmlspecialchars($pet['breed']); ?>, Age: <?php echo htmlspecialchars($pet['age']); ?>)
                            - <a href="view_pet_details.php?petID=<?php echo $pet['petID']; ?>" style="color: green;">View
                                Details</a>
                        </li>
                    <?php } ?>
                <?php } else { ?>
                    <p>No pets found. <a href="add_pet.php">Add a pet</a></p>
                <?php } ?>
            </ul>

            <button onclick="window.location.href='view_appointments.php'">View Appointments</button>
            <button onclick="window.location.href='book_appointment.php'">Book an Appointment</button>
        <?php } ?>

        <!-- Show "View Veterinarian Dashboard" button only for veterinarians -->
        <?php if ($role === 'veterinarian') { ?>
            <button onclick="window.location.href='vet_dashboard.php'">View Appointments</button>
        <?php } ?>

        <button onclick="window.location.href='logout.php'">Logout</button>
    </div>
</body>

</html>