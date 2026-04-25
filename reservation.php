<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/config.php'; 
$pdo = $conn;

$erreur = null;
$total = null;
$nb_nuits = null;

$preselect_id = isset($_GET['id']) && ctype_digit($_GET['id']) ? (int)$_GET['id'] : null;

$stmt = $pdo->prepare("SELECT id_chambre, nom_chambre, prix_chambre FROM chambre WHERE disponibilite_chambre = 1 ORDER BY id_chambre ASC");
$stmt->execute();
$chambres = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $required = ['id_chambre','nom','prenom','email','date_arrivee','date_depart','nb_personne'];
    foreach ($required as $f) {
        if (empty($_POST[$f])) {
            $erreur = 'Champ manquant : ' . htmlspecialchars($f, ENT_QUOTES);
        }
    }

    if (!$erreur) {
        $id_chambre   = (int) $_POST['id_chambre'];
        $nom          = trim($_POST['nom']);
        $prenom       = trim($_POST['prenom']);
        $email        = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
        $date_arrivee = $_POST['date_arrivee'];
        $date_depart  = $_POST['date_depart'];
        $nb_personne  = (int) $_POST['nb_personne'];

        // --- CALCUL DU TOTAL ---
        $date1 = new DateTime($date_arrivee);
        $date2 = new DateTime($date_depart);
        $nb_nuits = $date1->diff($date2)->days;

        foreach ($chambres as $c) {
            if ((int)$c['id_chambre'] === $id_chambre) {
                $prix_chambre = (float)$c['prix_chambre'];
                break;
            }
        }

        if ($nb_nuits > 0) {
            $total = $nb_nuits * $prix_chambre;
        }

        if (!$email) {
            $erreur = 'Email invalide.';
        } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_arrivee) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_depart)) {
            $erreur = 'Format de date invalide.';
        } elseif ($date_depart <= $date_arrivee) {
            $erreur = 'La date de départ doit être après la date d\'arrivée.';
        } else {
            try {
                $pdo->beginTransaction();

                $stmt = $pdo->prepare("SELECT id_chambre FROM chambre WHERE id_chambre = ?");
                $stmt->execute([$id_chambre]);
                if (!$stmt->fetch()) {
                    $pdo->rollBack();
                    $erreur = 'Chambre introuvable.';
                } else {
                    $check = $pdo->prepare("
                        SELECT COUNT(*) AS cnt FROM reservation
                        WHERE id_chambre = ?
                          AND NOT (date_depart <= ? OR date_arrivee >= ?)
                    ");
                    $check->execute([$id_chambre, $date_arrivee, $date_depart]);
                    $row = $check->fetch();

                    if ($row && (int)$row['cnt'] > 0) {
                        $pdo->rollBack();
                        $erreur = 'La chambre est déjà réservée sur ces dates.';
                    } else {
                        $insert = $pdo->prepare("
                            INSERT INTO reservation (id_chambre, nom, prenom, email, date_arrivee, date_depart, nb_personne)
                            VALUES (?, ?, ?, ?, ?, ?, ?)
                        ");
                        $insert->execute([$id_chambre, $nom, $prenom, $email, $date_arrivee, $date_depart, $nb_personne]);

                        $reservationId = $pdo->lastInsertId();
                        $pdo->commit();

                        header('Location: confirmation.php?id=' . $reservationId);
                        exit;
                    }
                }
            } catch (Exception $e) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                $erreur = 'Erreur serveur : ' . htmlspecialchars($e->getMessage(), ENT_QUOTES);
            }
        }
    }
}
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réservation - Blue Horizon Hotel</title>
    <link rel="stylesheet" href="css/hotel.css">
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
        <a href="reservation.php" class="btn-book">RÉSERVER</a>
    </div>
</header>

<section class="page-hero">
    <h1>RÉSERVER UNE CHAMBRE</h1>
</section>

<section class="formulaire-section">

    <?php if ($erreur): ?>
        <p class="form-erreur"><?= $erreur ?></p>
    <?php endif; ?>

    <?php if ($total !== null): ?>
        <p style="font-size:18px; color:var(--or); letter-spacing:0.12em; margin-bottom:20px;">
            Séjour de <strong><?= $nb_nuits ?></strong> nuit(s) — 
            Total : <strong><?= number_format($total, 2, ',', ' ') ?> €</strong>
        </p>
    <?php endif; ?>

    <form method="POST" action="reservation.php">
        <input type="text" name="nom" placeholder="Nom" required>
        <input type="text" name="prenom" placeholder="Prénom" required>
        <input type="email" name="email" placeholder="Email" required>

        <select name="id_chambre" required>
            <option value="">-- Choisir une chambre --</option>
            <?php foreach ($chambres as $c): ?>
                <option value="<?= (int)$c['id_chambre'] ?>"
                    <?= ($preselect_id === (int)$c['id_chambre']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($c['nom_chambre']) ?> — 
                    <?= number_format((float)$c['prix_chambre'], 2, ',', ' ') ?> €/nuit
                </option>
            <?php endforeach; ?>
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
