<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

require_once "config.php";

$result = $conn->query("SELECT * FROM items ORDER BY id DESC");
$data = [];

while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data);
