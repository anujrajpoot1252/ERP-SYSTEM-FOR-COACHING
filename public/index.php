<?php $request = $_SERVER["REQUEST_URI"];

switch ($request) {

    case "/api/register":
        require "routes/register.php";
        break;

    case "/api/login":
        require "routes/login.php";
        break;

    case "/api/users":
        require "routes/getUsers.php";
        break;

    default:
        echo json_encode(["error"=>"Route not found"]);
}