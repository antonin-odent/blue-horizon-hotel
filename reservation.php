<?php
// Active l'affichage des erreurs PHP (utile en développement)
ini_set('display_errors', 1);
// Signale toutes les erreurs PHP
error_reporting(E_ALL);

// Inclut le fichier de connexion à la base de données
require_once __DIR__ . '/database.php';
// Crée la connexion PDO
$pdo = getPDO();

// Variable qui contiendra le message d'erreur à afficher dans la page
$erreur = null;

// Récupère l'ID de chambre depuis l'URL si présent et valide (pré-sélection depuis chambre.php)
$preselect_id = isset($_GET['id']) && ctype_digit($_GET['id']) ? (int)$_GET['id'] : null;

// Prépare la requête pour récupérer les chambres disponibles pour le <select>
$stmt = $pdo->prepare("SELECT id_chambre, nom_chambre, prix_chambre FROM chambre WHERE disponibilite_chambre = 1 ORDER BY id_chambre ASC");
// Exécute la requête sans paramètres
$stmt->execute();
// Récupère toutes les chambres disponibles dans un tableau associatif
$chambres = $stmt->fetchAll();

// Traitement du formulaire uniquement si la méthode HTTP est POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Liste des champs obligatoires du formulaire
    $required = ['id_chambre','nom','prenom','email','date_arrivee','date_depart','nb_personne'];
    // Vérifie que chaque champ requis est bien rempli
    foreach ($required as $f) {
        if (empty($_POST[$f])) {
            // Stocke le message d'erreur sans couper la page
            $erreur = 'Champ manquant : ' . htmlspecialchars($f, ENT_QUOTES);
        }
    }

    // Continue le traitement seulement s'il n'y a pas déjà une erreur de champ manquant
    if (!$erreur) {
        // Caste l'ID chambre en entier pour sécuriser la requête
        $id_chambre   = (int) $_POST['id_chambre'];
        // Supprime les espaces inutiles autour du nom
        $nom          = trim($_POST['nom']);
        // Supprime les espaces inutiles autour du prénom
        $prenom       = trim($_POST['prenom']);
        // Valide et nettoie l'adresse email
        $email        = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
        // Récupère la date d'arrivée
        $date_arrivee = $_POST['date_arrivee'];
        // Récupère la date de départ
        $date_depart  = $_POST['date_depart'];
        // Caste le nombre de personnes en entier
        $nb_personne  = (int) $_POST['nb_personne'];

        if (!$email) {
            // Stocke l'erreur si l'email est invalide
            $erreur = 'Email invalide.';
        } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_arrivee) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_depart)) {
            // Stocke l'erreur si le format de date ne correspond pas à AAAA-MM-JJ
            $erreur = 'Format de date invalide.';
        } elseif ($date_depart <= $date_arrivee) {
            // Stocke l'erreur si la date de départ n'est pas après la date d'arrivée
            $erreur = 'La date de départ doit être après la date d\'arrivée.';
        } else {
            try {
                // Démarre une transaction pour garantir l'intégrité des données
                $pdo->beginTransaction();

                // Vérifie que la chambre existe bien en base de données
                $stmt = $pdo->prepare("SELECT id_chambre FROM chambre WHERE id_chambre = ?");
                // Exécute la vérification avec l'ID de la chambre
                $stmt->execute([$id_chambre]);
                if (!$stmt->fetch()) {
                    // Annule la transaction si la chambre est introuvable
                    $pdo->rollBack();
                    // Stocke l'erreur chambre introuvable
                    $erreur = 'Chambre introuvable.';
                } else {
                    // Vérifie qu'il n'y a pas de réservation qui chevauche les dates demandées
                    $check = $pdo->prepare("
                        SELECT COUNT(*) AS cnt FROM reservation
                        WHERE id_chambre = ?
                          AND NOT (date_depart <= ? OR date_arrivee >= ?)
                    ");
                    // Exécute la vérification de disponibilité sur les dates choisies
                    $check->execute([$id_chambre, $date_arrivee, $date_depart]);
                    // Récupère le résultat du comptage
                    $row = $check->fetch();

                    if ($row && (int)$row['cnt'] > 0) {
                        // Annule la transaction si la chambre est déjà réservée sur ces dates
                        $pdo->rollBack();
                        // Stocke l'erreur de conflit de dates
                        $erreur = 'La chambre est déjà réservée sur ces dates.';
                    } else {
                        // Insère la nouvelle réservation en base de données
                        $insert = $pdo->prepare("
                            INSERT INTO reservation (id_chambre, nom, prenom, email, date_arrivee, date_depart, nb_personne)
                            VALUES (?, ?, ?, ?, ?, ?, ?)
                        ");
                        // Exécute l'insertion avec toutes les données validées
                        $insert->execute([$id_chambre, $nom, $prenom, $email, $date_arrivee, $date_depart, $nb_personne]);
                        // Récupère l'ID de la réservation nouvellement créée
                        $reservationId = $pdo->lastInsertId();
                        // Valide la transaction : toutes les opérations ont réussi
                        $pdo->commit();
                        // Redirige vers la page de confirmation avec l'ID de réservation
                        header('Location: confirmation.php?id=' . $reservationId);
                        // Seul exit légitime : la réservation a été créée avec succès
                        exit;
                    }
                }
            } catch (Exception $e) {
                // Annule la transaction en cas d'exception si elle est encore active
                if ($pdo->inTransaction()) $pdo->rollBack();
                // Stocke le message d'erreur serveur sécurisé
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