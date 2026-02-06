<?php
$conn = new mysqli("localhost", "root", "", "event_management");
if ($conn->connect_error) {
    die("Connection failed");
}
?>
