<?php
session_start();

require_once __DIR__ . '/../class/Database.php';
require_once __DIR__ . '/../class/User.php';
require_once __DIR__ . '/../controllers/UserController.php';


session_unset();
session_destroy();
header('Location: ../index/index.php');
exit;
