<?php
session_start();
include('../includes/db.php'); // Ensure correct path

// Check if the request is POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $appointmentID = isset($_POST['appointmentID']) ? intval($_POST['appointmentID']) : 0;

    if ($appointmentID > 0) {
        $query = "UPDATE Appointment SET status = 'cancelled' WHERE appointmentID = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $appointmentID);

        if ($stmt->execute()) {
            echo json_encode(["success" => true]);
        } else {
            echo json_encode(["success" => false, "message" => "Database error: " . $stmt->error]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "Invalid appointment ID."]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method."]);
}
?>