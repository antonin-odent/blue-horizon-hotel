<?php
// Active l'affichage des erreurs PHP (utile en développement)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Connexion via config.php (UNIQUE source de connexion)
require_once __DIR__ . '/config.php'; 
$pdo = $conn; // Harmonisation : $pdo = $conn

// Variable qui contiendra le message d'erreur
$erreur = null;

// Récupère l'ID de chambre depuis l'URL si présent
$preselect_id = isset($_GET['id']) && ctype_digit($_GET['id']) ? (int)$_GET['id'] : null;

// Récupère les chambres disponibles
$stmt = $pdo->prepare("SELECT id_chambre, nom_chambre, prix_chambre FROM chambre WHERE disponibilite_chambre = 1 ORDER BY id_chambre ASC");
$stmt->execute();
$chambres = $stmt->fetchAll();

// Traitement du formulaire
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

        if (!$email) {
            $erreur = 'Email invalide.';
        } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_arrivee) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_depart)) {
            $erreur = 'Format de date invalide.';
        } elseif ($date_depart <= $date_arrivee) {
            $erreur = 'La date de départ doit être après la date d\'arrivée.';
        } else {
            try {
                $pdo->beginTransaction();

                // Vérifie que la chambre existe
                $stmt = $pdo->prepare("SELECT id_chambre FROM chambre WHERE id_chambre = ?");
                $stmt->execute([$id_chambre]);
                if (!$stmt->fetch()) {
                    $pdo->rollBack();
                    $erreur = 'Chambre introuvable.';
                } else {
                    // Vérifie les chevauchements
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
                        // Insère la réservation
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


<!DOCTYPE html> <!-- Déclare le type de document HTML5 -->
<html lang="fr"> <!-- Définit la langue de la page en français -->
<head>
    <meta charset="UTF-8"> <!-- Définit l'encodage des caractères en UTF-8 -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Rend la page responsive sur mobile -->
    <title>Réservation - Blue Horizon Hotel</title> <!-- Titre affiché dans l'onglet du navigateur -->
    <link rel="stylesheet" href="css/hotel.css"> <!-- Lien vers la feuille de style principale -->
</head>
<body> <!-- Début du contenu visible de la page -->

    <header> <!-- En-tête du site -->
        <div class="header-top"> <!-- Conteneur de la barre de navigation -->
            <span class="hotel-ville">PARIS</span> <!-- Affiche la ville de l'hôtel -->
            <nav> <!-- Barre de navigation principale -->
                <a href="index.php">ACCUEIL</a> <!-- Lien vers la page d'accueil -->
                <a href="chambres.php">CHAMBRES</a> <!-- Lien vers la page des chambres -->
                <a href="reservation.php">RÉSERVER</a> <!-- Lien vers la page de réservation -->
            </nav>
            <a href="reservation.php" class="btn-book">RÉSERVER</a> <!-- Bouton de réservation rapide -->
        </div>
    </header>

    <section class="page-hero"> <!-- Section bannière en haut de page -->
        <h1>RÉSERVER UNE CHAMBRE</h1> <!-- Titre principal de la page -->
    </section>

    <section class="formulaire-section"> <!-- Section contenant le formulaire de réservation -->

        <?php if ($erreur): ?> <!-- Affiche le bloc d'erreur seulement si une erreur existe -->
            <p class="form-erreur"><?= $erreur ?></p> <!-- Message d'erreur stylisé dans la page -->
        <?php endif; ?> <!-- Fin de la condition d'affichage de l'erreur -->

        <form method="POST" action="reservation.php"> <!-- Formulaire envoyé en POST vers la même page -->
            <input type="text" name="nom" placeholder="Nom" required> <!-- Champ obligatoire pour le nom -->
            <input type="text" name="prenom" placeholder="Prénom" required> <!-- Champ obligatoire pour le prénom -->
            <input type="email" name="email" placeholder="Email" required> <!-- Champ obligatoire pour l'email avec validation HTML -->

            <!-- Liste déroulante des chambres disponibles avec présélection automatique si venu de chambre.php -->
            <select name="id_chambre" required> <!-- Sélecteur de chambre obligatoire -->
                <option value="">-- Choisir une chambre --</option> <!-- Option par défaut non sélectionnable -->
                <?php foreach ($chambres as $c): ?> <!-- Boucle sur chaque chambre disponible -->
                    <option value="<?= (int)$c['id_chambre'] ?>"
                        <?= ($preselect_id === (int)$c['id_chambre']) ? 'selected' : '' ?>> <!-- Présélectionne la chambre si l'ID correspond à celui de l'URL -->
                        <?= htmlspecialchars($c['nom_chambre']) ?> — <?= number_format((float)$c['prix_chambre'], 2, '.', ' ') ?> EUR/nuit <!-- Affiche le nom et le prix formaté de la chambre -->
                    </option>
                <?php endforeach; ?> <!-- Fin de la boucle sur les chambres -->
            </select>

            <input type="date" name="date_arrivee" required> <!-- Champ obligatoire pour la date d'arrivée -->
            <input type="date" name="date_depart" required> <!-- Champ obligatoire pour la date de départ -->
            <input type="number" name="nb_personne" placeholder="Nombre de personnes" min="1" required> <!-- Champ obligatoire pour le nombre de personnes, minimum 1 -->
            <button type="submit">CONFIRMER LA RÉSERVATION</button> <!-- Bouton de soumission du formulaire -->
        </form>
    </section>

    <footer> <!-- Pied de page du site -->
        <p>123 Avenue de la Mer, Paris | 01 23 45 67 89 | contact@bluehorizon.fr</p> <!-- Coordonnées de l'hôtel -->
    </footer>

</body> <!-- Fin du contenu visible -->
</html> <!-- Fin du document HTML -->
