<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

require_once "config.php";

$input = json_decode(file_get_contents("php://input"), true);
$id = $input["id"] ?? null;
$checked = $input["checked"] ?? null;

if ($id === null || $checked === null) {
    echo json_encode(["error" => "Missing parameters"]);
    exit;
}

$stmt = $conn->prepare("UPDATE items SET checked = ? WHERE id = ?");
$stmt->bind_param("ii", $checked, $id);
$stmt->execute();

echo json_encode(["success" => true]);
