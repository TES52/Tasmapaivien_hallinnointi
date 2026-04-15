<?php
session_start();

// tämä on jaettu salasana joka pitäisi vaihtaa ja piilottaa selaimesta
$access_code = "12345";

$error = "";

//Jos tunnistauduttu menee eteenpäin
if(isset($_SESSION["access_granted"]) && $_SESSION["access_granted"] === true){
    header("location: uusitapahtuma.php");
    exit;
}

// Handle form
if($_SERVER["REQUEST_METHOD"] == "POST"){
    
    if(empty($_POST["code"])){
        $error = "Syötä koodi";
    } else{
        $code = $_POST["code"];

        if($code === $access_code){
            $_SESSION["access_granted"] = true;

            header("location: uusitapahtuma.php");
            exit;
        } else{
            $error = "Väärä koodi";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <title>Pääsykoodi</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
     <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
      <link rel="stylesheet" href="tyyli.css">
    <style>
        body{ font: 14px sans-serif; }
        .wrapper{ width: 360px; padding: 20px; margin: auto; margin-top: 100px; }
    </style>
</head>
<body>
        <div class="w3-container ">
    <img src="img/vamia_logo.jpg" style="float: left;">
</div>
<br>
 
    <div class="wrapper">
        <a class="takaisin-button" href="welcome.php">←</a>
        <h2>Syötä pääsykoodi</h2>
        <p>Tämä sivu vaatii koodin.</p>

        <?php 
        if(!empty($error)){
            echo '<div class="alert alert-danger">' . $error . '</div>';
        }        
        ?>

        <form method="post">
            <div class="form-group">
                <label>Koodi</label>
                <input type="password" name="code" class="form-control">
            </div>    
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Jatka">
            </div>
        </form>
    </div>
</body>
</html>