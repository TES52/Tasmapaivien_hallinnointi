<?php

session_start();

// tarkista onko käyttäjä kirjautunut
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

require_once "config.php";

$sql = "SELECT id, nimi, pvm, lisatiedot FROM tapahtumat ORDER BY pvm DESC";
$result = mysqli_query($link, $sql);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Tasmapaiva</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <link rel="stylesheet" href="tyyli.css">
</head>
<body>

<div class="w3-container w3-teal">
   <a class="takaisin-button" href="javascript:history.back()">←</a>
    <center>
     <img src="img/vamia_logo.jpg" alt="logo" style="margin-top:10px;margin-right:10px">
     <br>
    
<h1>Vamian täsmäpäivät</h1>
</center>
</div>


<div class="w3-container">
<br>

<?php while($row = mysqli_fetch_assoc($result)): ?>

    <?php
    $isPast = (strtotime($row["pvm"]) < time());
    ?>

    <div class="event-wrapper" style="max-width: 600px; margin: 10px auto;">
        <div class="event" style="background-color: <?php echo $isPast ? '#f8d7da' : '#d4edda'; ?>; padding:15px; border-radius:8px; box-shadow:0 2px 5px rgba(0,0,0,0.1);">

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

            <?php if (!$isPast): ?>
                <a href="ilmoittaudu.php?id=<?php echo $row["id"]; ?>" 
                   class="w3-button w3-blue w3-small">
                   Ilmoittaudu
                </a>
            <?php else: ?>
                <span style="color: gray;">Ilmoittautuminen suljettu</span>
            <?php endif; ?>

        </div>
    </div>

<?php endwhile; ?>

</div>
<br>

</body>
</html>