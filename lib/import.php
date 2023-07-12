<?php
// import.php

$servername = "localhost";
$username = "root"; // Replace with your MySQL username
$password = ""; // Replace with your MySQL password
$database = "mydatabase";
$table = "products";

// Create a connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['file'])) {
        $file = $_FILES['file'];
        $fileName = $file['name'];
        $fileTmpPath = $file['tmp_name'];

        // Check if the uploaded file is a CSV
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        if ($fileExtension != 'csv') {
            echo "Invalid file format. Only CSV files are allowed.";
            exit;
        }

        // Read the contents of the CSV file
        $csvData = file_get_contents($fileTmpPath);

        // Convert CSV data to an array
        $csvArray = array_map("str_getcsv", explode("\n", $csvData));

        // Remove the header row
        $header = array_shift($csvArray);

        // Prepare and execute the SQL INSERT statement
        $stmt = $conn->prepare("INSERT INTO $table (id, name, price, description, category, in_stock) VALUES (?, ?, ?, ?, ?, ?)");
        foreach ($csvArray as $row) {
            if (count($row) === 6) {
                $id = $row[0];
                $name = $row[1];
                $price = $row[2];
                $description = $row[3];
                $category = $row[4];
                $inStock = ($row[5] === 'true') ? 1 : 0;

                $stmt->bind_param("issssi", $id, $name, $price, $description, $category, $inStock);
                $stmt->execute();
            }
        }

        echo "Data imported successfully";
    } else {
        echo "No file uploaded";
    }
} else {
    echo "Invalid request";
}

$conn->close();
?>
