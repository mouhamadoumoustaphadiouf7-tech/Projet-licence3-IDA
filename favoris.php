<?php
session_start();
require_once 'database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Ajouter aux favoris
if (isset($_GET['add'])) {
    $annonce_id = $_GET['add'];
    $stmt = $pdo->prepare("INSERT IGNORE INTO favoris (user_id, annonce_id) VALUES (?, ?)");
    $stmt->execute([$user_id, $annonce_id]);
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
}

// Retirer des favoris
if (isset($_GET['remove'])) {
    $annonce_id = $_GET['remove'];
    $stmt = $pdo->prepare("DELETE FROM favoris WHERE user_id = ? AND annonce_id = ?");
    $stmt->execute([$user_id, $annonce_id]);
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
}

// Récupérer les favoris
$stmt = $pdo->prepare("SELECT a.*, f.date_ajout, 
                       (SELECT image_url FROM annonce_images WHERE annonce_id = a.id ORDER BY ordre LIMIT 1) as image
                       FROM favoris f 
                       JOIN annonces a ON f.annonce_id = a.id 
                       WHERE f.user_id = ? 
                       ORDER BY f.date_ajout DESC");
$stmt->execute([$user_id]);
$favoris = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes favoris - SenAutoMarket</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .favorite-card {
            transition: transform 0.3s;
            margin-bottom: 20px;
        }
        .favorite-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .car-img {
            height: 200px;
            object-fit: cover;
        }
        .price {
            color: #ffc107;
            font-weight: bold;
            font-size: 1.2rem;
        }
        .btn-remove {
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 10;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-car"></i> SenAutoMarket
            </a>
            <div>
                <a href="index.php" class="btn btn-outline-light me-2">Accueil</a>
                <a href="dashboard.php" class="btn btn-outline-light">Dashboard</a>
            </div>
        </div>
    </nav>

    <div class="container my-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-heart text-danger"></i> Mes favoris</h2>
            <span class="badge bg-secondary"><?= count($favoris) ?> véhicule(s)</span>
        </div>

        <?php if (empty($favoris)): ?>
            <div class="alert alert-info text-center py-5">
                <i class="fas fa-heart-broken fa-3x mb-3"></i>
                <h4>Vous n'avez aucun favori</h4>
                <p>Ajoutez des véhicules à vos favoris en cliquant sur l'icône ❤️ sur les annonces</p>
                <a href="index.php" class="btn btn-warning mt-3">
                    <i class="fas fa-search"></i> Parcourir les annonces
                </a>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($favoris as $favori): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card favorite-card h-100 position-relative">
                            <a href="favoris.php?remove=<?= $favori['id'] ?>" 
                               class="btn btn-danger btn-sm btn-remove"
                               onclick="return confirm('Retirer ce véhicule des favoris ?')">
                                <i class="fas fa-trash"></i>
                            </a>
                            <img src="<?= $favori['image'] ?? 'assets/uploads/kia.jpg' ?>" 
                                 class="card-img-top car-img" alt="<?= $favori['titre'] ?>">
                            <div class="card-body">
                                <div class="mb-2">
                                    <span class="badge <?= $favori['type_annonce'] == 'vente' ? 'bg-warning' : 'bg-success' ?>">
                                        <?= $favori['type_annonce'] == 'vente' ? 'À VENDRE' : 'À LOUER' ?>
                                    </span>
                                </div>
                                <h5 class="card-title"><?= htmlspecialchars($favori['titre']) ?></h5>
                                <p class="card-text text-muted">
                                    <i class="fas fa-calendar"></i> <?= $favori['annee'] ?> |
                                    <i class="fas fa-tachometer-alt"></i> <?= number_format($favori['kilometrage'], 0, ',', ' ') ?> km
                                </p>
                                <p class="price">
                                    <?php if ($favori['type_annonce'] == 'vente'): ?>
                                        <?= number_format($favori['prix'], 0, ',', ' ') ?> FCFA
                                    <?php else: ?>
                                        <?= number_format($favori['prix_location_journalier'], 0, ',', ' ') ?> FCFA/jour
                                    <?php endif; ?>
                                </p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        <i class="fas fa-clock"></i> Ajouté le <?= date('d/m/Y', strtotime($favori['date_ajout'])) ?>
                                    </small>
                                    <a href="detail.php?id=<?= $favori['id'] ?>" class="btn btn-primary btn-sm">
                                        <i class="fas fa-eye"></i> Voir
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>