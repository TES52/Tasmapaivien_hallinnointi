<?php
session_start();

// suojaus
if (!isset($_SESSION["access_granted"]) || $_SESSION["access_granted"] !== true) {
    header("location: login.php");
    exit;
}

require_once "config.php";

$message = "";

// hae ID
if(!isset($_GET["id"])){
    header("location: uusitapahtuma.php");
    exit;
}

$id = (int)$_GET["id"];

// UPDATE
if($_SERVER["REQUEST_METHOD"] == "POST"){

    $nimi = trim($_POST["nimi"]);
    $pvm = date("Y-m-d H:i:s", strtotime($_POST["pvm"]));
    $lisatiedot = trim($_POST["lisatiedot"]);

    $sql = "UPDATE tapahtumat SET nimi = ?, pvm = ?, lisatiedot = ? WHERE id = ?";

    if($stmt = mysqli_prepare($link, $sql)){
        mysqli_stmt_bind_param($stmt, "sssi", $nimi, $pvm, $lisatiedot, $id);

        if(mysqli_stmt_execute($stmt)){
            $message = "Tapahtuma päivitetty!";
        } else{
            $message = "Virhe päivityksessä.";
        }

        mysqli_stmt_close($stmt);
    }
}


$sql = "SELECT * FROM tapahtumat WHERE id = ?";
if($stmt = mysqli_prepare($link, $sql)){
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
}


$pvm_input = date("Y-m-d\\TH:i", strtotime($row["pvm"]));
?>

<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <title>Muokkaa tapahtumaa</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
     <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <style>
        .wrapper { width: 400px; margin: 50px auto; }
    </style>
</head>
<body>
  <div class="w3-container ">
    <img src="img/vamia_logo.jpg" style="float: left;">
</div>
<div class="wrapper">
    <h2>Muokkaa tapahtumaa</h2>

    <?php if(!empty($message)) echo "<div class='alert alert-info'>$message</div>"; ?>

    <form method="post">
        <div class="form-group">
            <label>Nimi</label>
            <input type="text" name="nimi" class="form-control" 
                   value="<?php echo htmlspecialchars($row["nimi"]); ?>" required>
        </div>

        <div class="form-group">
            <label>Päivämäärä</label>
            <input type="datetime-local" name="pvm" class="form-control" 
                   value="<?php echo $pvm_input; ?>" required>
        </div>

        <div class="form-group">
            <label>Lisätiedot</label>
            <textarea name="lisatiedot" class="form-control"><?php echo htmlspecialchars($row["lisatiedot"]); ?></textarea>
        </div>

        <input type="submit" class="btn btn-primary" value="Tallenna">
        <a href="uusitapahtuma.php" class="btn btn-secondary">Takaisin</a>
    </form>
</div>

</body>
</html>

<?php mysqli_close($link); ?>