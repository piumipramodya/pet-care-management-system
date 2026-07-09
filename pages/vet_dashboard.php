<?php
session_start();
include('../includes/db.php');

// Ensure the user is logged in and is a veterinarian
if (!isset($_SESSION['userID']) || $_SESSION['role'] !== 'veterinarian') {
    die("Error: Not logged in as a veterinarian.");
}

// Get userID and fetch the actual vetID
$userID = $_SESSION['userID'];
$query = "SELECT vetID FROM Veterinarian WHERE userID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();
$vetData = $result->fetch_assoc();
$vetID = $vetData['vetID'] ?? null;

if (!$vetID) {
    die("Error: No veterinarian ID found for this user.");
}

// Get search filters
$searchName = $_GET['searchName'] ?? '';
$searchDate = $_GET['searchDate'] ?? '';
$statusFilter = $_GET['status'] ?? 'all';

// Set SQL condition based on filter
$statusCondition = "";
if ($statusFilter == 'active') {
    $statusCondition = "AND Appointment.status IN ('booked', 'confirmed', 'pending')";
} elseif ($statusFilter == 'completed') {
    $statusCondition = "AND Appointment.status = 'completed'";
} elseif ($statusFilter == 'canceled') {
    $statusCondition = "AND Appointment.status = 'canceled'";
}

// Build search condition
$searchCondition = "";
$params = ["i", $vetID];
if (!empty($searchName)) {
    $searchCondition .= " AND Owner.username LIKE ?";
    $params[0] .= "s";
    $params[] = "%" . $searchName . "%";
}
if (!empty($searchDate)) {
    $searchCondition .= " AND DATE(Appointment.date) = ?";
    $params[0] .= "s";
    $params[] = $searchDate;
}

// Fetch appointments based on filters
$query = "
    SELECT Appointment.appointmentID, Pet.petID, Pet.name AS pet_name, Owner.username AS owner_name, 
           Appointment.date, Appointment.status
    FROM Appointment
    JOIN Pet ON Appointment.petID = Pet.petID
    JOIN User AS Owner ON Pet.ownerID = Owner.userID
    WHERE Appointment.vetID = ?
    $statusCondition
    $searchCondition
    ORDER BY Appointment.date ASC
";

$stmt = $conn->prepare($query);
$stmt->bind_param(...$params);
$stmt->execute();
$appointments = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Veterinarian Dashboard</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body>
    <div class="container">
        <h1>Welcome to the Veterinarian Dashboard</h1>
        <h2>Your Appointments</h2>

        <!-- Search & Filter Container -->
        <div class="search-container">
            <input type="text" id="searchName" placeholder="Search by Owner Name"
                value="<?= htmlspecialchars($searchName) ?>">
            <input type="date" id="searchDate" value="<?= htmlspecialchars($searchDate) ?>">
            <button id="searchBtn">Search</button>
        </div>

        <!-- Filter Buttons -->
        <div class="filter-buttons">
            <button class="toggle" data-status="all">Show All</button>
            <button class="toggle" data-status="active">Show Active</button>
            <button class="toggle" data-status="completed">Show Completed</button>
            <button class="toggle" data-status="canceled">Show Canceled</button>
        </div>

        <!-- Appointments Table -->
        <table>
            <thead>
                <tr>
                    <th>Appointment ID</th>
                    <th>Pet Name</th>
                    <th>Owner Name</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Mark Completed</th>
                    <th>Cancel</th>
                    <th>Pet Details</th>
                </tr>
            </thead>
            <tbody id="appointmentTable">
                <?php if ($appointments->num_rows === 0): ?>
                    <tr>
                        <td colspan="8" style="text-align:center;">No appointments found.</td>
                    </tr>
                <?php else: ?>
                    <?php while ($appointment = $appointments->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($appointment['appointmentID']) ?></td>
                            <td><?= htmlspecialchars($appointment['pet_name']) ?></td>
                            <td><?= htmlspecialchars($appointment['owner_name']) ?></td>
                            <td><?= htmlspecialchars($appointment['date']) ?></td>
                            <td id="status-<?= $appointment['appointmentID'] ?>" style="color: 
                                <?= ($appointment['status'] == 'canceled' ? 'red' :
                                    ($appointment['status'] == 'completed' ? 'green' : 'black')) ?>;">
                                <?= ucfirst(htmlspecialchars($appointment['status'])) ?>
                            </td>
                            <td style="text-align:center;">
                                <?php if ($appointment['status'] != 'canceled'): ?>
                                    <input type="checkbox" class="markCompleted" data-id="<?= $appointment['appointmentID'] ?>"
                                        <?= ($appointment['status'] == 'completed') ? 'checked' : '' ?>>
                                <?php else: ?>
                                    <span>N/A</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align:center;">
                                <?php if ($appointment['status'] != 'completed' && $appointment['status'] != 'canceled'): ?>
                                    <input type="checkbox" class="markCanceled" data-id="<?= $appointment['appointmentID'] ?>">
                                <?php else: ?>
                                    <span>N/A</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align:center;">
                                <a href="vet_view_pet_details.php?pet_id=<?= htmlspecialchars($appointment['petID']) ?>"
                                    class="btn-details">View Details</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Back to Dashboard Button -->
        <div class="back-btn-container">
            <button class="back-btn" onclick="window.location.href='dashboard.php'">Back to Dashboard</button>
        </div>
    </div>

    <script>
        $(document).ready(function () {
            $(".toggle").click(function () {
                var status = $(this).data("status");
                window.location.href = "vet_dashboard.php?status=" + status;
            });

            $("#searchBtn").click(function () {
                var name = $("#searchName").val().trim();
                var date = $("#searchDate").val().trim();
                window.location.href = "vet_dashboard.php?searchName=" + encodeURIComponent(name) + "&searchDate=" + encodeURIComponent(date);
            });

            $(".markCompleted").change(function () {
                var appointmentID = $(this).data("id");
                var status = $(this).is(":checked") ? "completed" : "booked";
                updateAppointmentStatus(appointmentID, status);
            });

            $(".markCanceled").change(function () {
                var appointmentID = $(this).data("id");
                var status = $(this).is(":checked") ? "canceled" : "booked";
                updateAppointmentStatus(appointmentID, status);
            });

            function updateAppointmentStatus(appointmentID, status) {
                $.ajax({
                    url: "update_appointment.php",
                    type: "POST",
                    data: { appointmentID: appointmentID, status: status },
                    success: function (response) {
                        $("#status-" + appointmentID).text(status.charAt(0).toUpperCase() + status.slice(1))
                            .css("color", status === "completed" ? "green" : (status === "canceled" ? "red" : "black"));
                    },
                    error: function () {
                        alert("Failed to update.");
                    }
                });
            }
        });
    </script>
</body>

</html>