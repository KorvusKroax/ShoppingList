<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

require_once "config.php";

$input = json_decode(file_get_contents("php://input"), true);
$name = $input["name"] ?? "";

$stmt = $conn->prepare("INSERT INTO items (name) VALUES (?)");
$stmt->bind_param("s", $name);
$stmt->execute();

echo json_encode(["success" => true]);
