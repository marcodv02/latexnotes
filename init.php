<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

define("HOST", "localhost");
define("USER", "graph");
define("PASSWORD", "graphene");
define("DATABASE", "grafo_mat");

date_default_timezone_set('Europe/Rome');

include_once "Controller/Server.php";

//if (session_status() === PHP_SESSION_NONE)
//    session_start();

