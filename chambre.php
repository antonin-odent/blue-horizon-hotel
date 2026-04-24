<?php
// Active l'affichage des erreurs PHP (utile en développement)
ini_set('display_errors', 1);
// Signale toutes les erreurs PHP
error_reporting(E_ALL);

// Inclut le fichier de connexion à la base de données
require_once __DIR__ . '/database.php';
// Crée la connexion PDO
$pdo = getPDO();

// Vérifie que l'ID est présent dans l'URL et qu'il est bien un nombre entier
if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    // Renvoie une erreur HTTP 400 si l'ID est invalide
    http_response_code(400);
    echo 'ID de chambre invalide.';
    exit;
}
// Convertit l'ID en entier pour sécuriser la requête
$id = (int)$_GET['id'];

try {
    // Prépare la requête pour récupérer la chambre correspondant à l'ID
    $stmt = $pdo->prepare("SELECT * FROM chambre WHERE id_chambre = ? LIMIT 1");
    // Exécute la requête avec l'ID
    $stmt->execute([$id]);
    // Récupère la ligne résultat
    $chambre = $stmt->fetch();

    // Si aucune chambre trouvée, on arrête le script
    if (!$chambre) { echo 'Chambre introuvable.'; exit; }

    // Sécurise et prépare le nom de la chambre pour l'affichage HTML
    $nom      = htmlspecialchars($chambre['nom_chambre'] ?? '—', ENT_QUOTES, 'UTF-8');
    // Formate le prix avec 2 décimales
    $prix     = number_format((float)($chambre['prix_chambre'] ?? 0), 2, '.', '');
    // Sécurise le type de chambre pour l'affichage HTML
    $type     = htmlspecialchars($chambre['type_chambre'] ?? '', ENT_QUOTES, 'UTF-8');
    // Convertit la capacité en entier
    $capacite = (int)($chambre['capacite_chambre'] ?? 1);
    // Sécurise la description et convertit les sauts de ligne en balises <br>
    $desc     = nl2br(htmlspecialchars($chambre['description_chambre'] ?? '', ENT_QUOTES, 'UTF-8'));

    // Prépare la requête pour récupérer toutes les images de cette chambre
    $stmtImg = $pdo->prepare("
        SELECT chemin_image FROM chambre_images
        WHERE id_chambre = ?
        ORDER BY ordre ASC
    ");
    // Exécute la requête avec l'ID de la chambre
    $stmtImg->execute([$id]);
    // Récupère uniquement les chemins des images dans un tableau simple
    $images = $stmtImg->fetchAll(PDO::FETCH_COLUMN);

    // Si aucune image n'est trouvée dans chambre_images
    if (empty($images)) {
        // Vérifie si une image est stockée directement dans la table chambre
        $stored = !empty($chambre['image_chambre']) ? $chambre['image_chambre'] : '';
        // Utilise l'image de la table chambre si elle existe, sinon une image par défaut
        $images[] = ($stored && file_exists(__DIR__ . '/' . $stored))
            ? $stored
            : 'images/default.jpg';
    }

// Attrape les erreurs SQL et les affiche
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
    <!-- Titre dynamique avec le nom de la chambre -->
    <title><?= $nom ?> - Blue Horizon Hotel</title>
    <link rel="stylesheet" href="css/hotel.css">
    <style>
        /* Conteneur principal du carrousel, positionné en relatif pour les boutons absolus */
        .carrousel {
            position: relative;
            width: 100%;
            height: 100%;
            overflow: hidden; /* Cache les images qui débordent */
        }
        /* Conteneur flex qui aligne toutes les images côte à côte */
        .carrousel-track {
            display: flex;
            height: 100%;
            transition: transform 0.5s ease; /* Animation fluide au changement d'image */
        }
        /* Chaque image occupe 100% de la largeur du carrousel */
        .carrousel-track img {
            min-width: 100%;
            height: 100%;
            object-fit: cover; /* Recadre l'image sans la déformer */
            flex-shrink: 0;    /* Empêche les images de rétrécir */
        }
        /* Style commun des boutons précédent / suivant */
        .carrousel-btn {
            position: absolute;
            top: 50%;
            transform: translateY(-50%); /* Centre verticalement le bouton */
            background: rgba(0,0,0,0.45);
            color: #fff;
            border: none;
            width: 44px;
            height: 44px;
            border-radius: 50%; /* Bouton rond */
            font-size: 1.2rem;
            cursor: pointer;
            z-index: 10; /* Par-dessus les images */
            transition: background 0.2s;
        }
        /* Assombrit le bouton au survol */
        .carrousel-btn:hover { background: rgba(0,0,0,0.75); }
        /* Positionne le bouton précédent à gauche */
        .carrousel-btn.prev  { left: 14px; }
        /* Positionne le bouton suivant à droite */
        .carrousel-btn.next  { right: 14px; }
        /* Conteneur des points de navigation en bas du carrousel */
        .carrousel-dots {
            position: absolute;
            bottom: 12px;
            left: 50%;
            transform: translateX(-50%); /* Centre horizontalement */
            display: flex;
            gap: 7px;
        }
        /* Style de chaque point de navigation */
        .carrousel-dots span {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: rgba(255,255,255,0.45); /* Point inactif semi-transparent */
            cursor: pointer;
            transition: background 0.2s;
        }
        /* Point actif en blanc plein */
        .carrousel-dots span.active { background: #fff; }
        /* Compteur "1 / 2" affiché en haut à droite du carrousel */
        .carrousel-compteur {
            position: absolute;
            top: 12px;
            right: 16px;
            color: #fff;
            font-size: 0.8rem;
            letter-spacing: 0.1em;
            background: rgba(0,0,0,0.35);
            padding: 3px 9px;
            border-radius: 20px;
        }
    </style>
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

<section class="detail-chambre">

    <div class="detail-media">
        <?php if (count($images) > 1): ?>
        <!-- Affiche le carrousel uniquement s'il y a plusieurs images -->
        <div class="carrousel" id="carrousel">
            <div class="carrousel-track" id="carrouselTrack">
                <?php foreach ($images as $img): ?>
                    <!-- Affiche chaque image du carrousel -->
                    <img src="<?= htmlspecialchars($img, ENT_QUOTES) ?>" alt="<?= $nom ?>">
                <?php endforeach; ?>
            </div>

            <!-- Bouton pour aller à l'image précédente -->
            <button class="carrousel-btn prev" onclick="carrouselMove(-1)">&#8592;</button>
            <!-- Bouton pour aller à l'image suivante -->
            <button class="carrousel-btn next" onclick="carrouselMove(1)">&#8594;</button>

            <!-- Points de navigation cliquables -->
            <div class="carrousel-dots" id="carrouselDots">
                <?php foreach ($images as $i => $img): ?>
                    <!-- Le premier point est actif par défaut -->
                    <span class="<?= $i === 0 ? 'active' : '' ?>"
                          onclick="carrouselGoto(<?= $i ?>)"></span>
                <?php endforeach; ?>
            </div>

            <!-- Compteur dynamique mis à jour par JavaScript -->
            <div class="carrousel-compteur" id="carrouselCompteur">
                1 / <?= count($images) ?>
            </div>
        </div>

        <?php else: ?>
        <!-- S'il n'y a qu'une seule image, on affiche juste une balise img classique -->
        <img src="<?= htmlspecialchars($images[0], ENT_QUOTES) ?>"
             alt="<?= $nom ?>" class="detail-img">
        <?php endif; ?>
    </div>

    <!-- Bloc d'informations de la chambre -->
    <div class="detail-info">
        <!-- Nom de la chambre -->
        <h1><?= $nom ?></h1>
        <!-- Type et capacité -->
        <p class="detail-meta"><?= $type ?> • <?= $capacite ?> personne(s)</p>
        <!-- Prix par nuit -->
        <p class="detail-prix"><?= $prix ?> EUR / nuit</p>
        <!-- Description avec sauts de ligne -->
        <div class="detail-desc"><?= $desc ?></div>
        <!-- Équipements fixes (pourrait venir de la BDD) -->
        <p><strong>Équipements :</strong> Wi‑Fi, TV, Climatisation</p>
        <!-- Lien vers la page de réservation avec l'ID de la chambre -->
        <a href="reservation.php?id=<?= $id ?>" class="btn-book">RÉSERVER CETTE CHAMBRE</a>
    </div>

</section>

<footer>
    <p>9447 Cambridge Road Far Rockaway, NY 11691 | 07 67 75 63 23 | contact@bluehorizon.fr</p>
</footer>

<script>
    // Index de l'image actuellement affichée
    let current = 0;
    // Référence au conteneur des images
    const track    = document.getElementById('carrouselTrack');
    // Référence à tous les points de navigation
    const dots     = document.querySelectorAll('#carrouselDots span');
    // Référence au compteur "1 / 2"
    const compteur = document.getElementById('carrouselCompteur');
    // Nombre total d'images (injecté depuis PHP)
    const total    = <?= count($images) ?>;

    // Affiche l'image à l'index donné
    function carrouselGoto(index) {
        // Calcule l'index en bouclant (revient au début si on dépasse la fin)
        current = (index + total) % total;
        // Déplace le track horizontalement pour montrer l'image courante
        track.style.transform = `translateX(-${current * 100}%)`;
        // Met à jour les points : active celui correspondant à l'image courante
        dots.forEach((d, i) => d.classList.toggle('active', i === current));
        // Met à jour le compteur "X / total"
        if (compteur) compteur.textContent = `${current + 1} / ${total}`;
    }

    // Avance ou recule d'une image selon la direction (+1 ou -1)
    function carrouselMove(dir) {
        carrouselGoto(current + dir);
    }

    // Permet de naviguer avec les touches fléchées du clavier
    document.addEventListener('keydown', e => {
        if (e.key === 'ArrowLeft')  carrouselMove(-1); // Flèche gauche → image précédente
        if (e.key === 'ArrowRight') carrouselMove(1);  // Flèche droite → image suivante
    });

    // Gestion du swipe tactile sur mobile
    let touchStartX = 0; // Position X du début du swipe
    const el = document.getElementById('carrousel');
    if (el) {
        // Enregistre la position de départ au toucher
        el.addEventListener('touchstart', e => { touchStartX = e.touches[0].clientX; });
        // Calcule la direction du swipe à la fin du toucher
        el.addEventListener('touchend',   e => {
            const diff = touchStartX - e.changedTouches[0].clientX;
            // Si le swipe dépasse 40px, on change d'image dans la bonne direction
            if (Math.abs(diff) > 40) carrouselMove(diff > 0 ? 1 : -1);
        });
    }
</script>

</body>
</html>