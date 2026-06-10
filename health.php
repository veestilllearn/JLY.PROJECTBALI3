<?php

header('Content-Type: application/json');

echo json_encode([
    "project" => "JLY.PROJECTBALI3",
    "status" => "UP",
    "timestamp" => date("Y-m-d H:i:s")
]);