<?php
session_start();
include('../includes/db.php');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

$error = "";

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Check if email exists in the database
    $query = "SELECT * FROM User WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Verify password
        if (password_verify($password, $user['password'])) {
            $_SESSION['userID'] = $user['userID'];
            $_SESSION['role'] = $user['role'];

            // Redirect based on role
            if ($user['role'] == 'veterinarian') {
                header("Location: vet_dashboard.php");
            } else if ($user['role'] == 'user') {
                header("Location: dashboard.php");
            } else if ($user['role'] == 'admin') {
                header("Location: admin_dashboard.php");
            }
            exit();
        } else {
            $error = "❌ Invalid credentials.";
        }
    } else {
        $error = "❌ User not found.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Login - Pet Care System</title>
    <link rel="stylesheet" type="text/css" href="../assets/css/styles.css">
</head>

<body>
    <div class="container">
        <h1>Login</h1>

        <?php if (!empty($error)) {
            echo "<p class='error'>$error</p>";
        } ?>

        <form method="POST">
            <label for="email">Email:</label>
            <input type="email" name="email" required>

            <label for="password">Password:</label>
            <input type="password" name="password" required>

            <button type="submit">Login</button>
        </form>
    </div>
</body>

</html>