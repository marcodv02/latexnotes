<?php
require_once("../init.php");

$status= 2;

$Server= new Background();
$data= $Server->show_all("", "nodi");
header('Content-type: application/json');
echo json_encode(["status"=>2, "data"=>$data, "response"=>"nothing"]);
