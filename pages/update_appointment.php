<?php
session_start();
include('../includes/db.php');

// Ensure user is logged in and is a veterinarian
if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'veterinarian') {
    echo "error: Unauthorized access.";
    exit;
}

// Validate input
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['appointmentID']) && isset($_POST['status'])) {
    $appointmentID = intval($_POST['appointmentID']);
    $status = $_POST['status'];

    // Ensure status is valid
    $validStatuses = ['completed', 'booked', 'canceled'];
    if (!in_array($status, $validStatuses)) {
        echo "error: Invalid status.";
        exit;
    }

    // Update status in database
    $query = "UPDATE Appointment SET status = ? WHERE appointmentID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $status, $appointmentID);

    if ($stmt->execute()) {
        echo "success"; // Proper response for AJAX success
    } else {
        echo "error: Update failed.";
    }
    exit;
} else {
    echo "error: Invalid request.";
    exit;
}
