<?php
// chambres.php
require_once __DIR__ . '/database.php';
$pdo = getPDO();

// Récupérer les chambres disponibles
$stmt = $pdo->prepare("
    SELECT id_chambre, nom_chambre, prix_chambre, type_chambre, capacite_chambre, description_chambre
    FROM chambre
    WHERE disponibilite_chambre = 1
    ORDER BY id_chambre ASC
");
$stmt->execute();
$chambres = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nos Chambres - Blue Horizon Hotel</title>
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
        <h1>NOS CHAMBRES & SUITES</h1>
    </section>

    <section class="grille-chambres">
        <div class="card-chambre">
            <img src="images/simple.jpg" alt="Chambre Simple">
            <div class="card-corps">
                <h2>Chambre Simple</h2>
                <p>1 personne</p>
                <p class="prix">79.00 EUR / nuit</p>
                <a href="chambre.php?id=1" class="btn-card">VOIR LA CHAMBRE</a>
            </div>
        </div>

        <br> <br>

        <div class="card-chambre">
            <img src="images/balcon.jpg" alt="Chambre Balcon">
            <div class="card-corps">
                <h2>Chambre Balcon</h2>
                <p>Chambre Double • 2 personnes</p>
                <p class="prix">119.00 EUR / nuit</p>
                <p class="desc">Grande chambre avec lit double et balcon.</p>
                <a href="chambre.php?id=2" class="btn-card">VOIR LA CHAMBRE</a>
             </div>
        </div>
         <br> <br>

        <div class="card-chambre">
            <img src="images/junior.jpg" alt="Suite Junior">
            <div class="card-corps">
                <h2>Suite Junior</h2>
                <p>Suite Junior • 2 personnes</p>
                <p class="prix">189.00 EUR / nuit</p>
                <p class="desc">Suite élégante avec coin salon.</p>
                <a href="chambre.php?id=3" class="btn-card">VOIR LA CHAMBRE</a>
            </div>
        </div>
         <br> <br>

        <div class="card-chambre">
            <img src="images/presidentielle.jpg" alt="Suite Présidentielle">
            <div class="card-corps">
                <h2>Suite Présidentielle</h2>
                <p>Suite Présidentielle • 4 personnes</p>
                <p class="prix">349.00 EUR / nuit</p>
                <p class="desc">Notre suite la plus luxueuse, vue mer panoramique.</p>
                <a href="chambre.php?id=4" class="btn-card">VOIR LA CHAMBRE</a>
            </div>
        </div>
    </section>

    <footer>
        <p>123 Avenue de la Mer, Paris | 01 23 45 67 89 | contact@bluehorizon.fr</p>
    </footer>

</body>
</html>
