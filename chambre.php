<?php
// chambre.php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/database.php';
$pdo = getPDO();

// Validation de l'ID
if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    http_response_code(400);
    echo 'ID de chambre invalide.';
    exit;
}
$id = (int)$_GET['id'];

try {
    // Requête sûre
    $stmt = $pdo->prepare("SELECT * FROM chambre WHERE id_chambre = ? LIMIT 1");
    $stmt->execute([$id]);
    $chambre = $stmt->fetch();

    if (!$chambre) {
        echo 'Chambre introuvable.';
        exit;
    }

    // Préparer les variables pour l'affichage
    $nom = htmlspecialchars($chambre['nom_chambre'] ?? '—', ENT_QUOTES, 'UTF-8');
    $prix = number_format((float)($chambre['prix_chambre'] ?? 0), 2, '.', '');
    $type = htmlspecialchars($chambre['type_chambre'] ?? '', ENT_QUOTES, 'UTF-8');
    $capacite = (int)($chambre['capacite_chambre'] ?? 1);
    $desc = nl2br(htmlspecialchars($chambre['description_chambre'] ?? '', ENT_QUOTES, 'UTF-8'));

    // Gestion de l'image : chemin stocké en base ou fallback
    $stored = !empty($chambre['image_chambre']) ? $chambre['image_chambre'] : '';
    $image = ($stored && file_exists(__DIR__ . '/' . $stored)) ? $stored : 'images/default.jpg';
} catch (PDOException $e) {
    echo 'Erreur SQL : ' . $e->getMessage();
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $nom; ?> - Blue Horizon Hotel</title>
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

    <section class="detail-chambre">
        <div class="detail-media">
            <img src="<?php echo htmlspecialchars($image, ENT_QUOTES); ?>" alt="<?php echo $nom; ?>" class="detail-img">
        </div>

        <div class="detail-info">
            <h1><?php echo $nom; ?></h1>
            <p class="detail-meta"><?php echo $type; ?> • <?php echo $capacite; ?> personne(s)</p>
            <p class="detail-prix"><?php echo $prix; ?> EUR / nuit</p>
            <div class="detail-desc"><?php echo $desc; ?></div>
            <p><strong>Équipements :</strong> Wi‑Fi, TV, Climatisation</p>
            <a href="reservation.php?id=<?php echo $id; ?>" class="btn-book">RÉSERVER CETTE CHAMBRE</a>
        </div>
    </section>

    <footer>
        <p>123 Avenue de la Mer, Paris | 01 23 45 67 89 | contact@bluehorizon.fr</p>
    </footer>

</body>
</html>
