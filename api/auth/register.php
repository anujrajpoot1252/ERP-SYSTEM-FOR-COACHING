<?php
header('Content-Type: application/json');
http_response_code(410);
echo json_encode([
    'status' => false,
    'message' => 'Standalone registration is unavailable. An institute administrator must create a complete student profile.'
]);
