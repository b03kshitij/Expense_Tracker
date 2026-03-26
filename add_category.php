<?php
require_once 'config/database.php';

if(isset($_POST['category'])) {

    $category = mysqli_real_escape_string($conn, $_POST['category']);

    if($category == "") {
        echo "Category cannot be empty";
        exit;
    }

    $sql = "INSERT INTO categories (name) VALUES ('$category')";

    if(mysqli_query($conn, $sql)) {
        echo "Category added successfully";
    } else {
        echo "Error adding category";
    }
}
?>