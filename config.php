<?php
/* Database kirjautumistiedot */
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'tiiahtvtpt23a_vamia_admin');
define('DB_PASSWORD', 'Rootme123_!');
define('DB_NAME', 'tiiahtvtpt23a_vamia_tapahtumatietokanta');
 
/* Yritetään luoda yhteys */
$link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
 
// tarkistetaan yhteys
if($link === false){
    die("ERROR: Could not connect. " . mysqli_connect_error());
}
?>