<?php
session_start();

// tarkista onko käyttäjä kirjautunut
if (!isset($_SESSION["access_granted"]) || $_SESSION["access_granted"] !== true) {
    header("location: login.php");
    exit;
}

require_once "config.php";

$message = "";

// DELETE lause
if(isset($_GET["delete"])){
    $id = (int)$_GET["delete"]; // force integer

    $sql = "DELETE FROM tapahtumat WHERE id = ?";
    if($stmt = mysqli_prepare($link, $sql)){
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    header("location: ".$_SERVER["PHP_SELF"]);
    exit;
}

// INSERT lause
if($_SERVER["REQUEST_METHOD"] == "POST"){

    // basic validation
    $nimi = trim($_POST["nimi"]);
    $pvm_input = $_POST["pvm"];
    $lisatiedot = trim($_POST["lisatiedot"]);

    if(empty($nimi) || empty($pvm_input)){
        $message = "Täytä kaikki pakolliset kentät!";
    } else {

        // datan lisäys
        $pvm = date("Y-m-d H:i:s", strtotime($pvm_input));

        $sql = "INSERT INTO tapahtumat (nimi, pvm, lisatiedot) VALUES (?, ?, ?)";

        if($stmt = mysqli_prepare($link, $sql)){
            mysqli_stmt_bind_param($stmt, "sss", $nimi, $pvm, $lisatiedot);

            if(mysqli_stmt_execute($stmt)){
                $message = "Tapahtuma lisätty!";
            } else{
                $message = "Virhe lisäyksessä.";
            }

            mysqli_stmt_close($stmt);
        }
    }
}


$sql = "SELECT id, nimi, pvm, lisatiedot FROM tapahtumat ORDER BY pvm DESC";
$result = mysqli_query($link, $sql);
?>

<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <title>Lisää tapahtuma</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
     <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <style>
        .wrapper { width: 500px; margin: 50px auto; }
        .event { border: 1px solid #ddd; padding: 10px; margin-top: 10px; }
    </style>
</head>
<body>
     <div class="w3-container ">
    <img src="img/vamia_logo.jpg" style="float: left;">
</div>
<a href="welcome.php">← Takaisin</a>
<div class="wrapper">
    <h2>Lisää tapahtuma</h2>

    <?php if(!empty($message)) echo "<div class='alert alert-info'>$message</div>"; ?>

    <form method="post">
        <div class="form-group">
            <label>Nimi</label>
            <input type="text" name="nimi" class="form-control" required>
        </div>

        <div class="form-group">
            <label>Päivämäärä</label>
            <input type="datetime-local" name="pvm" class="form-control" required>
        </div>

        <div class="form-group">
            <label>Lisätiedot</label>
            <textarea name="lisatiedot" class="form-control"></textarea>
        </div>

        <input type="submit" class="btn btn-primary" value="Lisää">
    </form>

    <hr>

    <h3>Tapahtumat</h3>

    <?php while($row = mysqli_fetch_assoc($result)): ?>

    <?php
    $isPast = (strtotime($row["pvm"]) < time());
    ?>

    <div class="event" style="background-color: <?php echo $isPast ? '#f8d7da' : '#d4edda'; ?>;">
        
        <strong><?php echo htmlspecialchars($row["nimi"]); ?></strong><br>
        
        <small>
            <?php echo $row["pvm"]; ?>
            <?php if($isPast): ?>
                <span style="color: red;">(Tapahtuma mennyt)</span>
            <?php else: ?>
                <span style="color: green;">(Tulossa)</span>
            <?php endif; ?>
        </small>
        
        <p><?php echo nl2br(htmlspecialchars($row["lisatiedot"])); ?></p>

        <a href="?delete=<?php echo $row["id"]; ?>" 
           class="btn btn-danger btn-sm"
           onclick="return confirm('Poistetaanko tapahtuma?');">
           Poista
        </a>

        <a href="edit.php?id=<?php echo $row["id"]; ?>" 
           class="btn btn-warning btn-sm">
           Muokkaa
        </a>
    </div>

<?php endwhile; ?>

</div>

</body>
</html>

<?php mysqli_close($link); ?>