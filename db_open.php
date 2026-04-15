<?php

$servername = "localhost";
$mysqluser = "tiiahtvtpt23a_vamia_admin";
$mysqlpassword = "Rootme123_!";
$databasename = "tiiahtvtpt23a_vamia_tapahtumatietokanta";


$con = new mysqli($servername, $mysqluser, $mysqlpassword, $databasename);

if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}
?>
