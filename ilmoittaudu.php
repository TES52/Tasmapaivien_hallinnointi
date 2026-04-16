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

$tapahtuma_ID = $_GET["id"] ?? null;

//delete
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete_id"])) {
    $delete_id = (int)$_POST["delete_id"];

    $del = mysqli_prepare($link, "
        DELETE FROM ilmoittautumiset 
        WHERE id = ? AND user_ID = ?
    ");

    mysqli_stmt_bind_param($del, "ii", $delete_id, $user_id);
    mysqli_stmt_execute($del);

    $_SESSION["success"] = true;
    header("Location: ilmoittaudu.php?id=" . $tapahtuma_ID);
    exit;
}

$events_stmt = mysqli_query($link, "SELECT id, nimi FROM tapahtumat ORDER BY nimi");
if (!$events_stmt) {
    die("Query failed: " . mysqli_error($link));
}

$event = null;
if ($tapahtuma_ID) {
    $stmt = mysqli_prepare($link, "SELECT nimi FROM tapahtumat WHERE id = ?");
    if (!$stmt) {
        die("Prepare failed: " . mysqli_error($link));
    }

    mysqli_stmt_bind_param($stmt, "i", $tapahtuma_ID);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $event = mysqli_fetch_assoc($result);

    if (!$event) {
        die("Tapahtumaa ei löydy.");
    }
}

//formi
$error = "";
$old = $_POST;

if ($_SERVER["REQUEST_METHOD"] === "POST" && !isset($_POST["delete_id"])) {

    $email = $_POST["email"];
    $koulu = $_POST["koulu"];
    $nimi = $_POST["nimi"];
    $erityisruokavalio = $_POST["erityisruokavalio"];
    $tapahtuma_ID = $_POST["tapahtuma_ID"] ?? null;

    if (!$tapahtuma_ID) {
        $error = "Tapahtuma puuttuu.";
    } else {

        $insert = mysqli_prepare($link, "
            INSERT INTO ilmoittautumiset
            (email, user_ID, nimi, koulu, erityisruokavalio, tapahtuma_ID)
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        mysqli_stmt_bind_param(
            $insert,
            "sisssi",
            $email,
            $user_id,
            $nimi,
            $koulu,
            $erityisruokavalio,
            $tapahtuma_ID
        );

        try {
            mysqli_stmt_execute($insert);

            setcookie("email", $email, time() + (86400 * 30), "/");
            setcookie("koulu", $koulu, time() + (86400 * 30), "/");

            $_SESSION["success"] = true;

            header("Location: ilmoittaudu.php?id=" . $tapahtuma_ID);
            exit;

        } catch (mysqli_sql_exception $e) {

            if ($e->getCode() == 1062) {
                $error = "⚠️ Oppilas kyseisellä nimellä on jo ilmoitettu tähän tapahtumaan.";
            } else {
                $error = "Tietokantavirhe: " . $e->getMessage();
            }
        }
    }
}

//forms
$user_forms_stmt = mysqli_prepare($link, "
    SELECT i.id, i.nimi, i.koulu, i.erityisruokavalio, i.email, t.nimi AS tapahtuma_nimi
    FROM ilmoittautumiset i
    JOIN tapahtumat t ON i.tapahtuma_ID = t.id
    WHERE i.user_ID = ?
    ORDER BY i.id DESC
");

mysqli_stmt_bind_param($user_forms_stmt, "i", $user_id);
mysqli_stmt_execute($user_forms_stmt);
$user_forms_result = mysqli_stmt_get_result($user_forms_stmt);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Ilmoittautuminen</title>
    
</head>
<body>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <link rel="stylesheet" href="tyyli.css">
    <style>
        body { font: 14px sans-serif; }
    </style>

    
    <a class="takaisin-button" href="welcome.php"> ← </a>

   
    <div class="w3-container" style="display: flex; align-items: center; justify-content: center; margin-top: 20px;">
        <img src="img/vamia_logo.jpg" style="height: 80px; margin-right: 20px;">
        <h1 style="margin-top:0">Ilmoittautuminen</h1>
    </div>
    
    <br>

    <!-- Form Wrapperi -->
    <div class="forms_tyyli" style="margin: 40px auto; max-width:600px;">
        
        <!-- Success / Error viesti -->
        <?php if (!empty($_SESSION["success"])): ?>
            <p style="color: green; text-align: center; font-weight: bold; margin-bottom: 20px;">
                ✔ Ilmoittautuminen onnistui / poistettu
            </p>
            <?php unset($_SESSION["success"]); ?>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <p style="color: red; text-align: center; font-weight: bold; margin-bottom: 20px;">
                <?= htmlspecialchars($error) ?>
            </p>
        <?php endif; ?>

        <!-- Form Headeri -->
        <h2>Ilmoittautuminen</h2>
        <p>Täytäthän tiedot ilmoittautuaksesi tapahtumaan.</p>

        <!-- Form -->
        <form method="POST" class="form-tyyli">
           <div class="form-group">
        <label for="tapahtuma_ID">Tapahtuma:</label>
        <select name="tapahtuma_ID" id="tapahtuma_ID" class="form-control full-width-select" required>
            <?php while ($row = mysqli_fetch_assoc($events_stmt)): ?>
                <option value="<?= htmlspecialchars($row["id"]) ?>"
                    <?= ($row["id"] == $tapahtuma_ID) ? "selected" : "" ?>>
                    <?= htmlspecialchars($row["nimi"]) ?>
                </option>
            <?php endwhile; ?>
        </select>
    </div>

            <div class="form-group">
                <label for="email">Opinnonohjaajan sähköposti:</label>
                <input type="email" id="email" name="email" class="form-control" required
                    value="<?= htmlspecialchars($old["email"] ?? $_COOKIE["email"] ?? "") ?>">
            </div>

            <div class="form-group">
                <label for="koulu">Oppilaan koulu:</label>
                <input type="text" id="koulu" name="koulu" class="form-control" required
                    value="<?= htmlspecialchars($old["koulu"] ?? $_COOKIE["koulu"] ?? "") ?>">
            </div>

            <div class="form-group">
                <label for="nimi">Oppilaan nimi (Etu- ja sukunimi):</label>
                <input type="text" id="nimi" name="nimi" class="form-control" required
                    value="<?= htmlspecialchars($old["nimi"] ?? "") ?>">
            </div>

            <div class="form-group">
                <label for="erityisruokavalio">Erityisruokavalio:</label>
                <input type="text" id="erityisruokavalio" name="erityisruokavalio" class="form-control"
                    value="<?= htmlspecialchars($old["erityisruokavalio"] ?? "") ?>">
            </div>

            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Lähetä">
                <input type="reset" class="btn btn-secondary ml-2" value="Nollaa">
            </div>
        </form>
    </div>

    <!-- Table -->
    <div style="margin: 40px auto; max-width: 95%;">
        <hr>
        <h3>Omat ilmoittautumiset</h3>

        <?php if (mysqli_num_rows($user_forms_result) > 0): ?>
            <table class="table-tyyli">
                <tr>
                    <th>Tapahtuma</th>
                    <th>Nimi</th>
                    <th>Koulu</th>
                    <th>Erityisruokavalio</th>
                    <th>Sähköposti</th>
                    <th>Toiminnot</th>
                </tr>

                <?php while ($row = mysqli_fetch_assoc($user_forms_result)): ?>
                    <tr>
                        <td><?= htmlspecialchars($row["tapahtuma_nimi"]) ?></td>
                        <td><?= htmlspecialchars($row["nimi"]) ?></td>
                        <td><?= htmlspecialchars($row["koulu"]) ?></td>
                        <td><?= htmlspecialchars($row["erityisruokavalio"]) ?></td>
                        <td><?= htmlspecialchars($row["email"]) ?></td>
                        <td>
                            <a href="edit_ilmoittautuminen.php?id=<?= $row["id"] ?>">
                                <button type="button" class="btn btn-warning">Muokkaa</button>
                            </a>

                            <form method="POST" style="display:inline;"
                                  onsubmit="return confirm('Poistetaanko varmasti?');">
                                <input type="hidden" name="delete_id" value="<?= $row["id"] ?>">
                                <button type="submit" class="btn btn-danger">Poista</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
        <?php else: ?>
            <p>Ei vielä ilmoittautumisia.</p>
        <?php endif; ?>
    </div>
</body>
</html>