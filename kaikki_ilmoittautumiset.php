<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

require_once "config.php"; 

//delete
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete_id"])) {
    $delete_id = (int)$_POST["delete_id"];

    $del = mysqli_prepare($link, "
        DELETE FROM ilmoittautumiset 
        WHERE id = ?
    ");

    mysqli_stmt_bind_param($del, "i", $delete_id);
    mysqli_stmt_execute($del);

    $_SESSION["success"] = true;

    // pidä tapahtuma_id filter uudelleenohjatessa
    $redirect = "kaikki_ilmoittautumiset.php";
    if (!empty($_GET['tapahtuma_id'])) {
        $redirect .= "?tapahtuma_id=" . (int)$_GET['tapahtuma_id'];
    }

    header("Location: $redirect");
    exit;
}

// Get filterrin arvo
$tapahtuma_filter = isset($_GET['tapahtuma_id']) ? (int)$_GET['tapahtuma_id'] : null;

// Sorting
$valid_columns = ['username', 'tapahtuma_nimi', 'nimi', 'koulu', 'erityisruokavalio', 'email'];
$sort_column = in_array($_GET['sort'] ?? '', $valid_columns) ? $_GET['sort'] : 'i.id';
$sort_order = ($_GET['order'] ?? '') === 'desc' ? 'DESC' : 'ASC';
$next_order = $sort_order === 'ASC' ? 'desc' : 'asc';

// Fetch tapahtumat (dropdown)
$events_stmt = mysqli_query($link, "SELECT id, nimi FROM tapahtumat ORDER BY nimi");
$events = [];
while ($row = mysqli_fetch_assoc($events_stmt)) {
    $events[] = $row;
}

// Fetch ilmoittautumiset filtterillä jos on
$where_clause = $tapahtuma_filter ? "WHERE i.tapahtuma_ID = $tapahtuma_filter" : "";
$all_forms_stmt = mysqli_query($link, "
    SELECT i.id, i.nimi, i.koulu, i.erityisruokavalio, i.email, t.nimi AS tapahtuma_nimi, u.username
    FROM ilmoittautumiset i
    JOIN tapahtumat t ON i.tapahtuma_ID = t.id
    JOIN users u ON i.user_ID = u.id
    $where_clause
    ORDER BY $sort_column $sort_order
");

if (!$all_forms_stmt) {
    die("Query failed: " . mysqli_error($link));
}

// Fetch määät per tapahtuma
$tapahtuma_counts_stmt = mysqli_query($link, "
    SELECT t.nimi AS tapahtuma_nimi, COUNT(i.id) AS count
    FROM tapahtumat t
    LEFT JOIN ilmoittautumiset i ON t.id = i.tapahtuma_ID
    GROUP BY t.id, t.nimi
    ORDER BY t.nimi
");

$tapahtuma_counts = [];
if ($tapahtuma_counts_stmt) {
    while ($row = mysqli_fetch_assoc($tapahtuma_counts_stmt)) {
        $tapahtuma_counts[] = $row;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Kaikki ilmoittautumiset</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="tyyli.css">
</head>
<body>

    <a ="float: left;" class="takaisin-button" style="margin-left:10px" href="javascript:history.back()">←</a>
<div class="container mt-4">
    
    
    <div class="w3-container" style="display: flex; align-items: center; justify-content: center; margin-top: 20px;">
      
    <img src="img/vamia_logo.jpg" style="height: 80px; margin-right: 20px;margin-top: 0;">
    <h1 style="margin-top:0">Ilmoittautumiset</h1>
</div>
<br><br>
    <h1>Kaikki ilmoittautumiset</h1>

    <!-- Tapahtumiin osallistuneiden määrä -->
    <div class="mb-4 osallistujat">
        <h4>Osallistujamäärät per tapahtuma</h4>
        <table class="table table-sm table-bordered">
            <thead>
                <tr>
                    <th>Tapahtuma</th>
                    <th>Osallistujia</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tapahtuma_counts as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['tapahtuma_nimi']) ?></td>
                        <td><?= (int)$row['count'] ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if (!empty($_SESSION["success"])): ?>
        <div class="alert alert-success">
            ✔ Ilmoittautuminen poistettu
        </div>
        <?php unset($_SESSION["success"]); ?>
    <?php endif; ?>
<br><br><br>

    <!-- formi jossa voi valita tapahtuman jonka mukaan suodatetaan -->
    <form method="GET" class="mb-4 form-tyyli">
        <label style="font-weight:bold;font-size:22px" for="tapahtuma_id">Suodata tapahtuman mukaan:</label>
        <select name="tapahtuma_id" id="tapahtuma_id" class="form-control w-50 d-inline-block">
            <option value="">-- Kaikki tapahtumat --</option>
            <?php foreach ($events as $event): ?>
                <option value="<?= $event['id'] ?>" <?= $tapahtuma_filter == $event['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($event['nimi']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-primary ml-2">Suodata</button>
    </form>

    <!-- table joka näyttää ilmoittautumistiedot ja tietoja klikkaamalla voi järjestää rivejä niiden mukaan -->
    <?php if (mysqli_num_rows($all_forms_stmt) > 0): ?>
        <table id="ilmoittautumiset-table" class="table table-bordered table-hover osallistujat">
            <thead>
                <tr>
                    <th><a href="?<?= http_build_query(array_merge($_GET, ['sort'=>'username','order'=>$next_order])) ?>#ilmoittautumiset-table">Käyttäjä</a></th>
                    <th><a href="?<?= http_build_query(array_merge($_GET, ['sort'=>'tapahtuma_nimi','order'=>$next_order])) ?>#ilmoittautumiset-table">Tapahtuma</a></th>
                    <th><a href="?<?= http_build_query(array_merge($_GET, ['sort'=>'nimi','order'=>$next_order])) ?>#ilmoittautumiset-table">Nimi</a></th>
                    <th><a href="?<?= http_build_query(array_merge($_GET, ['sort'=>'koulu','order'=>$next_order])) ?>#ilmoittautumiset-table">Koulu</a></th>
                    <th><a href="?<?= http_build_query(array_merge($_GET, ['sort'=>'erityisruokavalio','order'=>$next_order])) ?>#ilmoittautumiset-table">Erityisruokavalio</a></th>
                    <th><a href="?<?= http_build_query(array_merge($_GET, ['sort'=>'email','order'=>$next_order])) ?>#ilmoittautumiset-table">Sähköposti</a></th>
                    <th>Toiminnot</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($all_forms_stmt)): ?>
                    <tr>
                        <td><?= htmlspecialchars($row["username"]) ?></td>
                        <td><?= htmlspecialchars($row["tapahtuma_nimi"]) ?></td>
                        <td><?= htmlspecialchars($row["nimi"]) ?></td>
                        <td><?= htmlspecialchars($row["koulu"]) ?></td>
                        <td><?= htmlspecialchars($row["erityisruokavalio"]) ?></td>
                        <td><?= htmlspecialchars($row["email"]) ?></td>
                        <td>
                            <form method="POST" style="display:inline;" 
                                  onsubmit="return confirm('Poistetaanko varmasti?');">
                                <input type="hidden" name="delete_id" value="<?= $row["id"] ?>">
                                <button type="submit" class="btn btn-danger btn-sm">Poista</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Ei ilmoittautumisia.</p>
    <?php endif; ?>
</div>

    <br> 

</body>
</html>