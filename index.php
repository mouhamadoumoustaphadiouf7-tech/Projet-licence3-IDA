<?php
@session_start();
require_once 'database.php';

// Récupérer les filtres
$type_annonce = $_GET['type_annonce'] ?? '';
$marque = $_GET['marque'] ?? '';
$modele = $_GET['modele'] ?? '';
$carburant = $_GET['carburant'] ?? '';
$boite = $_GET['boite'] ?? '';
$prix_min = $_GET['prix_min'] ?? 0;
$prix_max = $_GET['prix_max'] ?? 20000000;
$annee_min = $_GET['annee_min'] ?? 1990;
$annee_max = $_GET['annee_max'] ?? 2025;

// Construction de la requête
$sql = "SELECT a.*, u.nom as vendeur, u.telephone, u.email,
        (SELECT image_url FROM annonce_images WHERE annonce_id = a.id ORDER BY ordre LIMIT 1) as image
        FROM annonces a 
        LEFT JOIN users u ON a.user_id = u.id 
        WHERE 1=1";

$params = [];

if (!empty($type_annonce)) {
    $sql .= " AND a.type_annonce = :type_annonce";
    $params['type_annonce'] = $type_annonce;
}

if (!empty($prix_min) && $type_annonce != 'location') {
    $sql .= " AND a.prix BETWEEN :prix_min AND :prix_max";
    $params['prix_min'] = $prix_min;
    $params['prix_max'] = $prix_max;
}

if (!empty($annee_min)) {
    $sql .= " AND a.annee BETWEEN :annee_min AND :annee_max";
    $params['annee_min'] = $annee_min;
    $params['annee_max'] = $annee_max;
}

if (!empty($marque)) {
    $sql .= " AND a.marque = :marque";
    $params['marque'] = $marque;
}
if (!empty($modele)) {
    $sql .= " AND a.modele LIKE :modele";
    $params['modele'] = "%$modele%";
}
if (!empty($carburant)) {
    $sql .= " AND a.carburant = :carburant";
    $params['carburant'] = $carburant;
}
if (!empty($boite)) {
    $sql .= " AND a.boite = :boite";
    $params['boite'] = $boite;
}

$sql .= " ORDER BY a.date_publication DESC LIMIT 20";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$annonces = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SenAutoMarket - Vente et Location de véhicules au Sénégal</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', 'Segoe UI', sans-serif; background: #00008B; }
        
        .navbar { background: linear-gradient(135deg, #0a2b3e 0%, #1a4a6f 100%); padding: 15px 0; box-shadow: 0 2px 20px rgba(0,0,0,0.1); }
        .navbar-brand { font-size: 1.8rem; font-weight: bold; }
        
        .hero { background: linear-gradient(135deg, #0a2b3e 0%, #1a4a6f 100%); color: white; padding: 80px 0 60px; position: relative; overflow: hidden; }
        .hero h1 { font-size: 3.5rem; font-weight: 800; margin-bottom: 20px; animation: fadeInUp 0.8s ease; }
        
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .filters-card { background: white; border-radius: 20px; padding: 25px; margin-top: -50px; position: relative; z-index: 10; box-shadow: 0 20px 40px rgba(0,0,0,0.1); }
        
        .car-card { background: white; border-radius: 15px; overflow: hidden; box-shadow: 0 5px 15px rgba(0,0,0,0.08); transition: all 0.3s; margin-bottom: 30px; cursor: pointer; position: relative; }
        .car-card:hover { transform: translateY(-10px); box-shadow: 0 15px 30px rgba(0,0,0,0.15); }
        
        .badge-type { position: absolute; top: 15px; left: 15px; padding: 5px 15px; border-radius: 20px; font-weight: bold; z-index: 2; }
        .badge-vente { background: #ffc107; color: #000; }
        .badge-location { background: #28a745; color: #fff; }
        
        .car-img { height: 200px; background-size: cover; background-position: center; position: relative; }
        .car-price { position: absolute; bottom: 15px; right: 15px; background: rgba(0,0,0,0.8); color: #ffc107; padding: 5px 15px; border-radius: 20px; font-weight: bold; }
        
        .type-tabs { margin-bottom: 20px; border-bottom: 2px solid #e0e0e0; }
        .type-tab { display: inline-block; padding: 10px 20px; margin-right: 10px; cursor: pointer; font-weight: bold; transition: all 0.3s; }
        .type-tab.active { color: #ffc107; border-bottom: 3px solid #ffc107; }
        
        .btn-location { background: #28a745; color: white; border: none; }
        .btn-location:hover { background: #218838; color: white; }
        
        footer { background: #0a2b3e; color: white; padding: 40px 0 20px; margin-top: 60px; }
        
        @media (max-width: 768px) {
            .hero h1 { font-size: 2rem; }
            .filters-card { margin-top: 20px; }
        }
        .car{
            width:5%;
        }
    </style>
</head>
<body>

<!-- Navigation -->
<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container">
        <a class="navbar-brand" href="index.php"><i class="fas fa-car"></i> SenAutoMarket</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link active" href="#">Accueil</a></li>
                <li class="nav-item"><a class="nav-link" href="#annonces">Annonces</a></li>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="my_reservations.php">Mes réservations</a></li>
                    <li class="nav-item"><a class="nav-link btn btn-outline-light ms-2 px-3" href="logout.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link btn btn-outline-light ms-2 px-3" href="login.php"><i class="fas fa-user"></i> Connexion</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<!-- Hero Section -->
<section class="hero">
    <div class="container text-center">
        <h1>Achetez ou Louez votre véhicule</h1>
        <p class="lead">+500 annonces vérifiées | Location à partir de 15 000 FCFA/jour</p>
    </div>
</section>

<!-- Filtres avancés -->
<div class="container">
    <div class="filters-card">
        <form method="GET" action="index.php" id="filterForm">
            <div class="row g-3">
                <div class="col-md-12">
                    <div class="type-tabs">
                        <span class="type-tab <?= !$type_annonce || $type_annonce == 'vente' ? 'active' : '' ?>" data-type="vente">
                            <i class="fas fa-shopping-cart"></i> Achat
                        </span>
                        <span class="type-tab <?= $type_annonce == 'location' ? 'active' : '' ?>" data-type="location">
                            <i class="fas fa-key"></i> Location
                        </span>
                    </div>
                    <input type="hidden" name="type_annonce" id="type_annonce" value="<?= $type_annonce ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Marque</label>
                    <select class="form-select" name="marque">
                        <option value="">Toutes</option>
                        <option value="Toyota" <?= $marque == 'Toyota' ? 'selected' : '' ?>>Toyota</option>
                        <option value="Honda" <?= $marque == 'Honda' ? 'selected' : '' ?>>Honda</option>
                        <option value="Suzuki" <?= $marque == 'Suzuki' ? 'selected' : '' ?>>Suzuki</option>
                        <option value="Hyundai" <?= $marque == 'Hyundai' ? 'selected' : '' ?>>Hyundai</option>
                        <option value="Kia" <?= $marque == 'Kia' ? 'selected' : '' ?>>Kia</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Modèle</label>
                    <input type="text" class="form-control" name="modele" value="<?= htmlspecialchars($modele) ?>" placeholder="Ex: Yaris">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Carburant</label>
                    <select class="form-select" name="carburant">
                        <option value="">Tous</option>
                        <option value="Essence" <?= $carburant == 'Essence' ? 'selected' : '' ?>>Essence</option>
                        <option value="Diesel" <?= $carburant == 'Diesel' ? 'selected' : '' ?>>Diesel</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Boîte</label>
                    <select class="form-select" name="boite">
                        <option value="">Toutes</option>
                        <option value="Manuelle" <?= $boite == 'Manuelle' ? 'selected' : '' ?>>Manuelle</option>
                        <option value="Automatique" <?= $boite == 'Automatique' ? 'selected' : '' ?>>Automatique</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold"><?= $type_annonce == 'location' ? 'Prix max / jour (FCFA)' : 'Prix (FCFA)' ?></label>
                    <div class="row">
                        <div class="col-6"><input type="number" class="form-control" name="prix_min" value="<?= $prix_min ?>" placeholder="Min"></div>
                        <div class="col-6"><input type="number" class="form-control" name="prix_max" value="<?= $prix_max ?>" placeholder="Max"></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Année min</label>
                    <input type="number" class="form-control" name="annee_min" value="<?= $annee_min ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Année max</label>
                    <input type="number" class="form-control" name="annee_max" value="<?= $annee_max ?>">
                </div>
            </div>
            <div class="text-center mt-4">
                <button type="submit" class="btn btn-warning btn-lg px-5"><i class="fas fa-search"></i> Rechercher</button>
                <a href="index.php" class="btn btn-outline-secondary btn-lg px-4"><i class="fas fa-undo"></i> Réinitialiser</a>
            </div>
        </form>
    </div>
</div>

<!-- Annonces Section -->
<div class="container my-5" id="annonces">
    <h2 class="mb-4"><i class="fas fa-star text-warning"></i> <?= $type_annonce == 'location' ? 'Véhicules à louer' : 'Véhicules à vendre' ?></h2>
    <div class="row">
        <?php if (empty($annonces)): ?>
            <div class="col-12 text-center py-5">
                <i class="fas fa-car-side fa-3x text-muted mb-3"></i>
                <h4>Aucune annonce trouvée</h4>
                <p>Modifiez vos critères de recherche</p>
            </div>
        <?php else: ?>
            <?php foreach ($annonces as $annonce): ?>
                <div class="col-md-6 col-lg-3">
                    <div class="car-card" onclick="window.location.href='detail.php?id=<?= $annonce['id'] ?>'">
                        <div class="badge-type <?= $annonce['type_annonce'] == 'vente' ? 'badge-vente' : 'badge-location' ?>">
                            <?= $annonce['type_annonce'] == 'vente' ? 'À VENDRE' : 'À LOUER' ?>
                        </div>
                        <div class="car-img" style="background-image: url('<?= $annonce['image'] ?? 'assets/uploads/kia.jpg' ?>')">
                            <div class="car-price">
                                <?php if ($annonce['type_annonce'] == 'vente'): ?>
                                    <?= number_format($annonce['prix'], 0, ',', ' ') ?> FCFA
                                <?php else: ?>
                                    <?= number_format($annonce['prix_location_journalier'], 0, ',', ' ') ?> FCFA/jour
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="p-3">
                            <h5><?= htmlspecialchars($annonce['titre']) ?></h5>
                            <p class="text-muted"><i class="fas fa-calendar"></i> <?= $annonce['annee'] ?> | <i class="fas fa-tachometer-alt"></i> <?= number_format($annonce['kilometrage'], 0, ',', ' ') ?> km</p>
                            <?php if ($annonce['type_annonce'] == 'location'): ?>
                                <p><small><i class="fas fa-hand-holding-usd"></i> Caution: <?= number_format($annonce['caution'], 0, ',', ' ') ?> FCFA</small></p>
                            <?php endif; ?>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge bg-secondary"><?= $annonce['carburant'] ?></span>
                                <small><i class="fas fa-eye"></i> <?= $annonce['views'] ?? 0 ?> vues</small>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Section Vendre/Louer -->
<section class="bg-warning text-dark py-5 mt-5">
    <div class="container text-center">
        <h2>Prêt à vendre ou louer votre véhicule ?</h2>
        <p class="lead mb-4">Publiez votre annonce gratuitement en 2 minutes</p>
        <div class="row justify-content-center">
            <div class="col-md-4 mb-3">
                <a href="annonce.php?type=vente" class="btn btn-dark btn-lg w-100"><i class="fas fa-tag"></i> Vendre mon véhicule</a>
            </div>
            <div class="col-md-4 mb-3">
                <a href="annonce.php?type=location" class="btn btn-success btn-lg w-100"><i class="fas fa-key"></i> Louer mon véhicule</a>
            </div>
        </div>
    </div>
</section>

<!-- Footer -->
<footer>
    <div class="container">
        <div class="row">
            <div class="col-md-4 mb-3"><h5><i class="fas fa-car"></i> SenAutoMarket</h5><p>Vente et location de véhicules au Sénégal</p></div>
            <div class="col-md-4 mb-3"><h5>Liens rapides</h5><ul class="list-unstyled"><li><a href="#" class="text-white text-decoration-none">Comment ça marche ?</a></li><li><a href="#" class="text-white text-decoration-none">FAQ</a></li></ul></div>
            <div class="col-md-4 mb-3"><h5>Contact</h5><p><i class="fas fa-phone"></i> +221 762641802</p><p><i class="fas fa-envelope"></i> contact@senautomarket.sn</p></div>
        </div>
        <hr class="bg-light"><p class="text-center mb-0">&copy; 2025 SenAutoMarket - Tous droits réservés</p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Gestion des onglets Achat/Location
document.querySelectorAll('.type-tab').forEach(tab => {
    tab.addEventListener('click', function() {
        document.getElementById('type_annonce').value = this.dataset.type;
        document.getElementById('filterForm').submit();
    });
});
</script>
</body>
</html>