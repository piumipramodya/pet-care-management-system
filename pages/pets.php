<?php
include('../includes/db.php');
$sql = "SELECT * FROM pets";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html>

<head>
    <title>Pets</title>
</head>

<body>
    <h2>Pets</h2>
    <a href="add_pet.php">Add New Pet</a>
    <table>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Breed</th>
            <th>Age</th>
            <th>Actions</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()) { ?>
            <tr>
                <td><?php echo $row['petID']; ?></td>
                <td><?php echo $row['name']; ?></td>
                <td><?php echo $row['breed']; ?></td>
                <td><?php echo $row['age']; ?></td>
                <td>
                    <a href="edit_pet.php?id=<?php echo $row['petID']; ?>">Edit</a>
                    <a href="delete_pet.php?id=<?php echo $row['petID']; ?>">Delete</a>
                </td>
            </tr>
        <?php } ?>
    </table>
</body>

</html>