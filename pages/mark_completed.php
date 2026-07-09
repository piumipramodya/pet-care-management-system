<?php
session_start();
include('../includes/db.php');

// Ensure the user is logged in and is a veterinarian
if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'veterinarian') {
    die("Error: Unauthorized access.");
}

// Get the appointment ID from the form
$appointmentID = $_POST['appointmentID'] ?? null;

if (!$appointmentID) {
    die("Error: Missing appointment ID.");
}

// Update the appointment status to 'completed'
$query = "UPDATE Appointment SET status = 'completed' WHERE appointmentID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $appointmentID);

if ($stmt->execute()) {
    // Redirect back to the dashboard with a success message
    header("Location: vet_dashboard.php?success=1");
} else {
    die("Error: Unable to update status.");
}
?>