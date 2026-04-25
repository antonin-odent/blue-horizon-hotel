<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/config.php';
$pdo = $conn;


$stmt = $pdo->prepare("
    SELECT id_chambre, nom_chambre, prix_chambre, type_chambre, capacite_chambre, description_chambre
    FROM chambre
    WHERE disponibilite_chambre = 1
    ORDER BY id_chambre ASC
");
$stmt->execute();
$chambres = $stmt->fetchAll();

$images = [
    'chambre simple'         => 'images/simple.jpg',
    'suite double'         => 'images/balcon.jpg',
    'suite junior'   => 'images/junior.jpg',
    'suite présidentielle' => 'images/presidentielle.jpg',
];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nos Chambres - Blue Horizon Hotel</title>
    <link rel="stylesheet" href="css/hotel.css">
</head>
<body>

    <header>
        <div class="header-top">
            <span class="hotel-ville">New York</span>
            <nav>
                <a href="index.php">ACCUEIL</a>
                <a href="chambres.php">CHAMBRES</a>
                <a href="reservation.php">RÉSERVER</a>
            </nav>
            <a href="reservation.php" class="btn-book">RÉSERVER</a>
        </div>
    </header>

    <section class="page-hero">
        <h1>NOS CHAMBRES & SUITES</h1>
    </section>

    <section class="grille-chambres">
        <?php if (empty($chambres)): ?>
            <p class="aucune-chambre">Aucune chambre disponible pour le moment.</p>
        <?php else: ?>
            <?php foreach ($chambres as $chambre): ?>
                <?php
                    $type = strtolower(trim($chambre['type_chambre']));
                    $image = $images[$type] ?? 'images/default.jpg';
                    $alt   = htmlspecialchars($chambre['nom_chambre']);
                    $prix  = number_format($chambre['prix_chambre'], 2, '.', ' ');
                ?>
                <div class="card-chambre">
                    <img src="<?= $image ?>" alt="<?= $alt ?>">
                    <div class="card-corps">
                        <h2><?= htmlspecialchars($chambre['nom_chambre']) ?></h2>
                        <p><?= htmlspecialchars($chambre['type_chambre']) ?> • <?= (int)$chambre['capacite_chambre'] ?> personne<?= $chambre['capacite_chambre'] > 1 ? 's' : '' ?></p>
                        <p class="prix"><?= $prix ?> EUR / nuit</p>
                        <?php if (!empty($chambre['description_chambre'])): ?>
                            <p class="desc"><?= htmlspecialchars($chambre['description_chambre']) ?></p>
                        <?php endif; ?>
                        <a href="chambre.php?id=<?= (int)$chambre['id_chambre'] ?>" class="btn-card">VOIR LA CHAMBRE</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>

    <footer>
        <p>9447 Cambridge Road Far Rockaway, NY 11691 | 07 67 75 63 23 | contact@bluehorizon.fr</p>
    </footer>

</body>
</html>
