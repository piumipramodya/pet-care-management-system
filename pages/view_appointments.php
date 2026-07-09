<?php
session_start();
include('../includes/db.php');

// Ensure user is logged in
if (!isset($_SESSION['userID'])) {
    header("Location: login.php");
    exit();
}

$userID = $_SESSION['userID']; // Logged-in user's ID

// Fetch appointments for the logged-in user
$appointmentQuery = "
    SELECT Appointment.appointmentID, Appointment.date, Appointment.status,
           Pet.name AS pet_name,
           Veterinarian.name AS vet_name, Veterinarian.specialty
    FROM Appointment
    JOIN Pet ON Appointment.petID = Pet.petID
    JOIN Veterinarian ON Appointment.vetID = Veterinarian.vetID
    WHERE Pet.ownerID = ?
    ORDER BY Appointment.date ASC
";
$stmt = $conn->prepare($appointmentQuery);
$stmt->bind_param("i", $userID); // Bind the user's ID
$stmt->execute();
$appointments = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Appointments</title>
    <link rel="stylesheet" type="text/css" href="../assets/css/styles.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <!-- Include jQuery -->
</head>

<body>
    <div class="container">
        <h1>Your Appointments</h1>

        <!-- Success message area -->
        <p id="success-message" style="display: none; color: green; font-weight: bold;"></p>

        <table>
            <thead>
                <tr>
                    <th>Pet</th>
                    <th>Veterinarian</th>
                    <th>Specialty</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $appointments->fetch_assoc()) { ?>
                    <tr id="appointment-<?php echo $row['appointmentID']; ?>">
                        <td><?php echo htmlspecialchars($row['pet_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['vet_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['specialty']); ?></td>
                        <td><?php echo htmlspecialchars(date("d-M-Y h:i A", strtotime($row['date']))); ?></td>
                        <td class="status"><?php echo htmlspecialchars($row['status']); ?></td>
                        <td>
                            <?php if ($row['status'] == 'booked') { ?>
                                <button class="btn btn-danger cancel-button"
                                    data-id="<?php echo $row['appointmentID']; ?>">Cancel</button>
                            <?php } else { ?>
                                <span class="disabled">Not Available</span>
                            <?php } ?>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>

        <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
    </div>

    <script>
        $(document).ready(function () {
            $(".cancel-button").click(function () {
                const appointmentID = $(this).data("id");
                const button = $(this); // Reference to the button clicked

                // Send AJAX request to cancel the appointment
                $.ajax({
                    url: "cancel_appointment_ajax.php", // Ensure correct file path
                    method: "POST",
                    data: { appointmentID: appointmentID },
                    success: function (response) {
                        try {
                            const res = JSON.parse(response); // Parse JSON response
                            if (res.success) {
                                // Update the status in the table
                                $("#appointment-" + appointmentID + " .status").text("Cancelled");
                                // Replace the cancel button with "Not Available"
                                button.replaceWith('<span class="disabled">Not Available</span>');
                                // Show success message
                                $("#success-message").text("Appointment successfully cancelled!").fadeIn().delay(2000).fadeOut();
                            } else {
                                // Show an error message in case of failure
                                $("#success-message").text("Error: " + res.message).css("color", "red").fadeIn().delay(3000).fadeOut();
                            }
                        } catch (e) {
                            console.error("Invalid response from server.");
                        }
                    },
                    error: function () {
                        // Handle AJAX request errors
                        $("#success-message").text("An error occurred. Please try again.").css("color", "red").fadeIn().delay(3000).fadeOut();
                    }
                });
            });
        });
    </script>
</body>

</html>