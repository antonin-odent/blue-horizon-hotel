<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once "config.php";

if (!isset($_GET['id'])) {
    echo "<p>Aucune chambre sélectionnée.</p>";
    exit;
}

$id = $_GET['id'];

$stmt = $conn->prepare("SELECT * FROM chambre WHERE id_chambre = ?");
$stmt->execute([$id]);
$chambre = $stmt->fetch();

if (!$chambre) {
    echo "<p>Chambre introuvable.</p>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($chambre['nom_chambre']); ?></title>
    <link rel="stylesheet" href="css/hotel.css">
</head>

<body>

<header>
    <div class="header-top">
        <span class="hotel-ville">PARIS</span>
        <nav>
            <a href="index.php">Accueil</a>
            <a href="chambres.php">Chambres</a>
            <a href="reservation.php">Réserver</a>
        </nav>
        <a href="reservation.php" class="btn-book">Book Now</a>
    </div>
</header>

<section class="page-hero">
    <h1><?php echo htmlspecialchars($chambre['nom_chambre']); ?></h1>
</section>

<section class="detail-chambre">

    <div class="detail-media">
        <img src="<?php echo htmlspecialchars($chambre['image_chambre']); ?>"
             alt="Image de la chambre"
             class="detail-img">
    </div>

    <div class="detail-info">

        <h1><?php echo htmlspecialchars($chambre['nom_chambre']); ?></h1>

        <div class="detail-meta">
            <?php echo htmlspecialchars($chambre['type_chambre']); ?> · 
            Capacité : <?php echo htmlspecialchars($chambre['capacite_chambre']); ?> pers.
        </div>

        <div class="detail-prix">
            <?php echo number_format($chambre['prix_chambre'], 2, ',', ' '); ?> € / nuit
        </div>

        <p class="detail-desc">
            <?php echo nl2br(htmlspecialchars($chambre['description_chambre'])); ?>
        </p>

        <a href="reservation.php?id=<?php echo $chambre['id_chambre']; ?>" class="btn-book">
            Réserver
        </a>

    </div>

</section>

<footer>
    <p>9447 Cambridge Road Far Rockaway, NY 11691 | 07 67 75 63 23 | contact@bluehorizon.fr</p>
</footer>

</body>
</html>
