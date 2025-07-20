<?php
// includes/validate_token.php

require_once __DIR__ . '/../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

include_once __DIR__ . '/../config/core.php';

function validate_token() {
    $headers = getallheaders();
    $jwt = isset($headers['Authorization']) ? str_replace('Bearer ', '', $headers['Authorization']) : '';

    if (empty($jwt)) {
        http_response_code(401);
        echo json_encode(array("message" => "Access denied. Token not provided."));
        exit();
    }

    try {
        $decoded = JWT::decode($jwt, new Key(Core::$SECRET_KEY, Core::$ALGORITHM[0]));
        return (array) $decoded->data; // Mengembalikan data pengguna dari token
    } catch (Exception $e) {
        http_response_code(401);
        echo json_encode(array("message" => "Access denied.", "error" => $e->getMessage()));
        exit();
    }
}
?>