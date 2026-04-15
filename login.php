<?php

session_start();
 
// Katsotaan, onko käyttäjä jo kirjautunut sisään
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    header("location: welcome.php");
    exit;
}
 
// config.php jonka avulla luodaan yhteys
require_once "config.php";
 
// määritetään tyhjät muuttujat
$username = $password = "";
$username_err = $password_err = $login_err = "";
 
// lähetetään tiedot ja prosessoidaan
if($_SERVER["REQUEST_METHOD"] == "POST"){
 
    // jos käyttäjänimi on tyhjä
    if(empty(trim($_POST["username"]))){
        $username_err = "Syötä käyttäjänimi";
    } else{
        $username = trim($_POST["username"]);
    }
    
    // jos salasana on tyhjä
    if(empty(trim($_POST["password"]))){
        $password_err = "Syötä salasana";
    } else{
        $password = trim($_POST["password"]);
    }
    
    // hyväksy tunnistautuminen
    if(empty($username_err) && empty($password_err)){
        
        $sql = "SELECT id, username, password FROM users WHERE username = ?";
        
        if($stmt = mysqli_prepare($link, $sql)){
            mysqli_stmt_bind_param($stmt, "s", $param_username);
            
            $param_username = $username;
            
            if(mysqli_stmt_execute($stmt)){
                mysqli_stmt_store_result($stmt);
                
                if(mysqli_stmt_num_rows($stmt) == 1){                    
                    mysqli_stmt_bind_result($stmt, $id, $username, $hashed_password);
                    
                    if(mysqli_stmt_fetch($stmt)){
                        if(password_verify($password, $hashed_password)){
                            
                            $_SESSION["loggedin"] = true;
                            $_SESSION["id"] = $id;
                            $_SESSION["username"] = $username;  
                            
                            header("location: welcome.php");
                            exit;
                            
                        } else{
                            $login_err = "Virheellinen käyttäjänimi tai salasana.";
                        }
                    }
                } else{
                    $login_err = "Virheellinen käyttäjänimi tai salasana.";
                }
            } else{
                echo "Oho! Jokin meni vikaan. Yritäthän myöhemmin uudelleen!";
            }

            mysqli_stmt_close($stmt);
        }
    }
    
    // sulje yhteys
    mysqli_close($link);
}
?>
 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Kirjaudu</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
     <link rel="stylesheet" href="tyyli.css">
    <style>
        body{ font: 14px sans-serif; }
        .wrapper{ width: 360px; padding: 20px; }
    </style>
</head>
<body class="background">
    <div class="w3-container">
    <img src="img/vamia_logo.jpg" alt="logo">
</div>
<div class="centered">
    <div class="wrapper forms_tyyli">
        <h2>Kirjaudu sisään</h2>
        <p>Täytäthän käyttäjätiedot kirjautuaksesi sisään.</p>

        <?php 
        if(!empty($login_err)){
            echo '<div class="alert alert-danger">' . $login_err . '</div>';
        }        
        ?>
<div class="forms_tyyli">
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <label>Käyttäjänimi</label>
                <input type="text" name="username" class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $username; ?>">
                <span class="invalid-feedback"><?php echo $username_err; ?></span>
            </div>    
            <div class="form-group">
                <label>Salasana</label>
                <input type="password" name="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>">
                <span class="invalid-feedback"><?php echo $password_err; ?></span>
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Login">
            </div>
            <p>Eikö sinulla ole vielä käyttäjää? <a href="register.php">Rekisteröidy nyt</a>!</p>
        </form>
        </div>
    </div>
   </div>
</body>
</html>