<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

require_once "config.php";

$user_id = $_SESSION["id"] ?? null;
if (!$user_id) {
    die("User ID puuttuu sessiosta.");
}

$id = $_GET["id"] ?? null;
if (!$id) {
    die("Missing ID");
}

/* ---------------- FETCH olemassa oleva data ---------------- */
$stmt = mysqli_prepare($link, "
    SELECT * FROM ilmoittautumiset
    WHERE id = ? AND user_ID = ?
");
mysqli_stmt_bind_param($stmt, "ii", $id, $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$data = mysqli_fetch_assoc($result);

if (!$data) {
    die("Ilmoittautumista ei löydy tai et omista sitä.");
}

/* ---------------- päivitä ---------------- */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $update = mysqli_prepare($link, "
        UPDATE ilmoittautumiset
        SET nimi = ?, koulu = ?, erityisruokavalio = ?, email = ?
        WHERE id = ? AND user_ID = ?
    ");

    mysqli_stmt_bind_param(
        $update,
        "ssssii",
        $_POST["nimi"],
        $_POST["koulu"],
        $_POST["erityisruokavalio"],
        $_POST["email"],
        $id,
        $user_id
    );

    mysqli_stmt_execute($update);

    header("Location: ilmoittaudu.php?id=" . $data["tapahtuma_ID"]);
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Muokkaa ilmoittautumista</title>
    <link rel="stylesheet" href="tyyli.css">
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
</head>
<body class="tausta">
<center>
<h2 class="suuri">Muokkaa ilmoittautumista</h2>
</center>
<form method="POST" class="form-tyyli centered">

    Email:<br>
    <input type="email" name="email"
           value="<?= htmlspecialchars($data["email"]) ?>" required>
    <br><br>

    Koulu:<br>
    <input type="text" name="koulu"
           value="<?= htmlspecialchars($data["koulu"]) ?>" required>
    <br><br>

    Nimi:<br>
    <input type="text" name="nimi"
           value="<?= htmlspecialchars($data["nimi"]) ?>" required>
    <br><br>

    Erityisruokavalio:<br>
    <input type="text" name="erityisruokavalio"
           value="<?= htmlspecialchars($data["erityisruokavalio"]) ?>">
    <br><br>

   <button style="btn btn-warning; " type="submit">← Takaisin</button>
    <button type="submit" style="margin-left:12px">Tallenna</button>
</form>

<br>


</body>
</html>