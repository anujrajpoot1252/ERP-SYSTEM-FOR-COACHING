<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/auth_check.php';

api_require_access(['admin']);
http_response_code(410);
echo json_encode([
    'status' => false,
    'message' => 'This legacy registration endpoint is retired. Use admin/students.php to create a complete student profile.'
]);
