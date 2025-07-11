<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Connect to DB
    $conn = new mysqli("localhost", "root", "", "jobquest");

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Sanitize and collect form data
    $id = $_POST['id']; // This must be in the form as a hidden input
    $job_title = $_POST['job_title'];
    $status = $_POST['status'];
    $notes = $_POST['notes'];

    // Prepare the update
    $sql = "UPDATE applications 
            SET job_title = ?, status = ?, notes = ?
            WHERE id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $job_title, $status, $notes, $id);

    if ($stmt->execute()) {
        header("Location: index.php?updated=true");
        exit();
    } else {
        echo "Error updating record: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
