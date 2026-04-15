<?php

session_start();
 
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}
?>
 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Welcome</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
     <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
     <link rel="stylesheet" href="tyyli.css">
    <style>
        body{ font: 14px sans-serif; text-align: center; }
    </style>
</head>
<body>
    
   <div class="w3-container">
    <img src="img/vamia_logo.jpg" style="float: left; margin-right: 10px;">
   
    <h2 style="float: left;" class="suuri">Täsmäpäivät</h2>
</div>


 <br>
<div class="w3-container  w3-teal">
     <h1 class="my-5">Hei, <b><?php echo htmlspecialchars($_SESSION["username"]); ?></b> <a href="logout.php" class="btn btn-danger ml-3">Kirjaudu ulos</a></h1>
     </div>
     <br>
    <br>
    <p>
      <center>
       
       <a href="index.php" class="btn btn-success button-tyyli" style=" margin-right:20px">
  <img src="img/kalenteri.png" style="height:45px; margin-right:10px;"> Tutustu tuleviin tapahtumiin
</a>
        <a href="ilmoittaudu.php" class="btn btn-primary button-tyyli">
            <img src="img/kalenteri_henkilo.png" style="height:45px; margin-right:10px;"> Hallitse ilmoittautumisia
            </a>
        <br> <br><br><br>
        <a href="tapahtumat.php" class="btn btn-primary button-tyyli">
            <img src="img/kalenteri_edit.png" style="height:45px; margin-right:10px;"> Hallitse tapahtumia (Vamia)
            </a>
          </center> 
    </p>

</body>
</html>
