<?php
// Traitement du formulaire de réservation (placer tout en haut de reservation.php)
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/database.php'; // adapter le chemin si besoin
$pdo = getPDO();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Champs requis
    $required = ['id_chambre','nom','prenom','email','date_arrivee','date_depart','nb_personne'];
    foreach ($required as $f) {
        if (empty($_POST[$f])) {
            http_response_code(400);
            echo 'Champ manquant : ' . htmlspecialchars($f, ENT_QUOTES);
            exit;
        }
    }

    // Récupération et nettoyage
    $id_chambre    = (int) $_POST['id_chambre'];
    $nom           = trim($_POST['nom']);
    $prenom        = trim($_POST['prenom']);
    $email         = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
    $date_arrivee  = $_POST['date_arrivee'];
    $date_depart   = $_POST['date_depart'];
    $nb_personne   = (int) $_POST['nb_personne'];

    if (!$email) { echo 'Email invalide.'; exit; }
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_arrivee) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_depart)) {
        echo 'Format de date invalide.'; exit;
    }
    if ($date_depart <= $date_arrivee) { echo 'La date de départ doit être après la date d\'arrivée.'; exit; }

    try {
        $pdo->beginTransaction();

        // Vérifier que la chambre existe
        $stmt = $pdo->prepare("SELECT id_chambre FROM chambre WHERE id_chambre = ?");
        $stmt->execute([$id_chambre]);
        if (!$stmt->fetch()) {
            $pdo->rollBack();
            echo 'Chambre introuvable.'; exit;
        }

        // Vérification anti-chevauchement (empêche deux réservations qui se recoupent)
        $check = $pdo->prepare("
            SELECT COUNT(*) AS cnt
            FROM reservation
            WHERE id_chambre = ?
              AND NOT (date_depart <= ? OR date_arrivee >= ?)
        ");
        $check->execute([$id_chambre, $date_arrivee, $date_depart]);
        $row = $check->fetch();
        if ($row && (int)$row['cnt'] > 0) {
            $pdo->rollBack();
            echo 'La chambre est déjà réservée sur ces dates.'; exit;
        }

        // Insérer la réservation
        $insert = $pdo->prepare("
            INSERT INTO reservation (id_chambre, nom, prenom, email, date_arrivee, date_depart, nb_personne)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $insert->execute([$id_chambre, $nom, $prenom, $email, $date_arrivee, $date_depart, $nb_personne]);
        $reservationId = $pdo->lastInsertId();

        $pdo->commit();

        // Redirection vers la page de confirmation
        header('Location: confirmation.php?id=' . $reservationId);
        exit;
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        echo 'Erreur serveur : ' . htmlspecialchars($e->getMessage(), ENT_QUOTES);
        exit;
    }
}
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réservation - Blue Horizon Hotel</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <header>
        <div class="header-top">
            <span class="hotel-ville">PARIS</span>
            <nav>
                <a href="index.php">ACCUEIL</a>
                <a href="chambres.php">CHAMBRES</a>
                <a href="reservation.php">RÉSERVER</a>
            </nav>
            <a href="reservation.php" class="btn-book">BOOK NOW</a>
        </div>
    </header>

    <section class="page-hero">
        <h1>RÉSERVER UNE CHAMBRE</h1>
    </section>

    <section class="formulaire-section">
        <form method="POST" action="reservation.php">
            <input type="text" name="nom" placeholder="Nom" required>
            <input type="text" name="prenom" placeholder="Prénom" required>
            <input type="email" name="email" placeholder="Email" required>
            <select name="id_chambre" required>
                <option value="">-- Choisir une chambre --</option>
                <option value="1">Chambre Simple - 79 EUR/nuit</option>
                <option value="2">Chambre Double - 119 EUR/nuit</option>
                <option value="3">Suite Junior - 189 EUR/nuit</option>
                <option value="4">Suite Présidentielle - 349 EUR/nuit</option>
            </select>
            <input type="date" name="date_arrivee" required>
            <input type="date" name="date_depart" required>
            <input type="number" name="nb_personne" placeholder="Nombre de personnes" min="1" required>
            <button type="submit">CONFIRMER LA RÉSERVATION</button>
        </form>
    </section>

    <footer>
        <p>123 Avenue de la Mer, Paris | 01 23 45 67 89 | contact@bluehorizon.fr</p>
    </footer>

</body>
</html>
