<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "shoppinglist";

$conn = new mysqli($host, $user, $pass, $db);
$conn->set_charset("utf8");

if ($conn->connect_error) {
    die(json_encode(["error" => $conn->connect_error]));
}
