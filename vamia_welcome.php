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
    
<div class="w3-container" style="display: flex; align-items: center; justify-content: center; margin-top: 20px;">
    <img src="img/vamia_logo.jpg" style="height: 80px; margin-right: 20px;">
    <h1 style="margin-top:0">Täsmäpäivät</h1>
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
        <a href="ilmoittaudu.php" class="btn btn-success button-tyyli">
            <img src="img/kalenteri_ilmoittautuminen.png" style="height:45px; margin-right:10px;"> Ilmoittautumiset
            </a>
            
        <br> <br><br><br>
        
    
            
        <a href="uusitapahtuma.php" class="btn btn-primary button-tyyli">
            <img src="img/kalenteri_edit.png" style="height:45px; margin-right:10px;"> Hallitse tapahtumia
            </a>
            
            <a href="kaikki_ilmoittautumiset.php" class="btn btn-primary button-tyyli" style="margin-left:20px">
            <img src="img/kalenteri_henkilo.png" style="height:45px;margin-right:10px;"> Näytä ilmoittautumiset
            </a>
          </center> 
    </p>

</body>
</html>
