<?php
session_start();
require_once 'database.php';

$id = $_GET['id'] ?? 0;

// Incrémenter les vues
$pdo->prepare("UPDATE annonces SET views = views + 1 WHERE id = ?")->execute([$id]);

// Récupérer l'annonce
$stmt = $pdo->prepare("SELECT a.*, u.nom as vendeur, u.telephone, u.email 
                       FROM annonces a 
                       LEFT JOIN users u ON a.user_id = u.id 
                       WHERE a.id = ?");
$stmt->execute([$id]);
$annonce = $stmt->fetch();

if (!$annonce) {
    header('Location: index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($annonce['titre']) ?> - SenAutoMarket</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

<nav class="navbar navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            <i class="fas fa-car"></i> SenAutoMarket
        </a>
        <a href="index.php" class="btn btn-outline-light">← Retour</a>
    </div>
</nav>

<div class="container my-5">
    <div class="card">
        <div class="row g-0">
            <div class="col-md-6">
                <img src="<?= $annonce['image'] ?? 'assets/uploads/HONDA-CIVIC.jpg'?>" ;
                     class="img-fluid rounded-start" style="height: 400px; width: 100%; object-fit: cover;">
            </div>
            <div class="col-md-6">
                <img src="<?= $annonce['image'] ?? 'assets/uploads/suzukiSwift.jpg'?>" ;
                     class="img-fluid rounded-start" style="height: 400px; width: 100%; object-fit: cover;">
            </div>
            <div class="col-md-6">
                <img src="<?= $annonce['image'] ?? 'assets/uploads/toyota-yaris.webp'?>" ;
                     class="img-fluid rounded-start" style="height: 400px; width: 100%; object-fit: cover;">
            </div>
            <div class="col-md-6">
                <img src="<?= $annonce['image'] ?? 'assets/uploads/hyundai.jpg'?>" ;
                     class="img-fluid rounded-start" style="height: 400px; width: 100%; object-fit: cover;">
            </div>
            <div class="col-md-6">
                <img src="<?= $annonce['image'] ?? 'assets/uploads/kia.jpg'?>" ;
                     class="img-fluid rounded-start" style="height: 400px; width: 100%; object-fit: cover;">
            </div>
            <div class="col-md-6">
                <div class="card-body">
                    <div class="mb-3">
                        <span class="badge <?= $annonce['type_annonce'] == 'vente' ? 'bg-warning' : 'bg-success' ?> fs-6">
                            <?= $annonce['type_annonce'] == 'vente' ? 'À VENDRE' : 'À LOUER' ?>
                        </span>
                    </div>
                    <h1 class="card-title"><?= htmlspecialchars($annonce['titre']) ?></h1>
                    
                    <!-- Bouton favoris -->
                    <div class="mb-3">
                        <?php
                        // Vérifier si déjà en favori
                        $stmt = $pdo->prepare("SELECT COUNT(*) FROM favoris WHERE user_id = ? AND annonce_id = ?");
                        $stmt->execute([$_SESSION['user_id'] ?? 0, $id]);
                        $is_favorite = $stmt->fetchColumn() > 0;
                        ?>
                        
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <a href="favoris.php?<?= $is_favorite ? 'remove' : 'add' ?>=<?= $id ?>" 
                               class="btn <?= $is_favorite ? 'btn-danger' : 'btn-outline-danger' ?>"
                               id="favoriBtn">
                                <i class="fas fa-heart"></i> 
                                <?= $is_favorite ? 'Retirer des favoris' : 'Ajouter aux favoris' ?>
                            </a>
                        <?php else: ?>
                            <a href="login.php" class="btn btn-outline-danger">
                                <i class="fas fa-heart"></i> Connectez-vous pour ajouter aux favoris
                            </a>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($annonce['type_annonce'] == 'vente'): ?>
                        <h2 class="text-warning"><?= number_format($annonce['prix'], 0, ',', ' ') ?> FCFA</h2>
                    <?php else: ?>
                        <h2 class="text-success"><?= number_format($annonce['prix_location_journalier'], 0, ',', ' ') ?> FCFA <small class="text-muted">/jour</small></h2>
                        <p class="text-muted">
                            <i class="fas fa-shield-alt"></i> Caution: <?= number_format($annonce['caution'], 0, ',', ' ') ?> FCFA
                        </p>
                        <p>
                            <strong>Disponibilité:</strong> 
                            <span class="<?= $annonce['disponibilite'] == 'disponible' ? 'text-success' : 'text-danger' ?>">
                                <?= $annonce['disponibilite'] == 'disponible' ? 'Disponible' : ($annonce['disponibilite'] == 'loue' ? 'Loué' : 'En maintenance') ?>
                            </span>
                        </p>
                    <?php endif; ?>
                    
                    <hr>
                    <div class="row">
                        <div class="col-6">
                            <p><strong>Marque:</strong> <?= $annonce['marque'] ?></p>
                            <p><strong>Modèle:</strong> <?= $annonce['modele'] ?></p>
                            <p><strong>Année:</strong> <?= $annonce['annee'] ?></p>
                            <p><strong>Kilométrage:</strong> <?= number_format($annonce['kilometrage'], 0, ',', ' ') ?> km</p>
                        </div>
                        <div class="col-6">
                            <p><strong>Carburant:</strong> <?= $annonce['carburant'] ?></p>
                            <p><strong>Boîte:</strong> <?= $annonce['boite'] ?></p>
                            <p><strong>Ville:</strong> <?= $annonce['ville'] ?></p>
                        </div>
                    </div>
                    <hr>
                    <p><strong>Description:</strong></p>
                    <p><?= nl2br(htmlspecialchars($annonce['description'])) ?></p>
                    <hr>
                    
                    <?php if ($annonce['type_annonce'] == 'location' && $annonce['disponibilite'] == 'disponible'): ?>
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <a href="reserver.php?id=<?= $annonce['id'] ?>" class="btn btn-success btn-lg w-100 mb-3">
                                <i class="fas fa-calendar-check"></i> Réserver ce véhicule
                            </a>
                        <?php else: ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> 
                                <a href="login.php">Connectez-vous</a> pour réserver ce véhicule
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                    
                    <h5>Contact du vendeur</h5>
                    <p><i class="fas fa-user"></i> <?= $annonce['vendeur'] ?></p>
                    <p><i class="fas fa-phone"></i> <?= $annonce['telephone'] ?? 'Non renseigné' ?></p>
                    <p><i class="fas fa-envelope"></i> <?= $annonce['email'] ?? 'Non renseigné' ?></p>
                    
                    <?php if ($annonce['type_annonce'] == 'vente'): ?>
                        <button class="btn btn-warning btn-lg w-100" onclick="window.location.href='tel:<?= $annonce['telephone'] ?>'">
                            <i class="fas fa-phone"></i> Contacter le vendeur
                        </button>
                    <?php endif; ?>
                    
                    <!-- Bouton message -->
                    <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] != $annonce['user_id']): ?>
                        <button class="btn btn-info w-100 mt-2" onclick="window.location.href='messages.php?chat_with=<?= $annonce['user_id'] ?>&annonce_id=<?= $id ?>'">
                            <i class="fas fa-envelope"></i> Envoyer un message
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Section avis -->
    <div class="card mt-4">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="fas fa-star text-warning"></i> Avis et évaluations</h5>
        </div>
        <div class="card-body">
            <?php
            // Récupérer les avis
            $stmt = $pdo->prepare("SELECT AVG(note) as moyenne, COUNT(*) as total FROM avis WHERE annonce_id = ?");
            $stmt->execute([$id]);
            $stats = $stmt->fetch();
            
            $stmt = $pdo->prepare("SELECT a.*, u.nom FROM avis a JOIN users u ON a.user_id = u.id WHERE a.annonce_id = ? ORDER BY a.date_creation DESC LIMIT 3");
            $stmt->execute([$id]);
            $avis = $stmt->fetchAll();
            ?>
            
            <div class="row">
                <div class="col-md-3 text-center">
                    <div class="display-4 text-warning"><?= number_format($stats['moyenne'] ?? 0, 1) ?></div>
                    <div class="text-warning">
                        <?php for($i = 1; $i <= 5; $i++): ?>
                            <i class="fas fa-star <?= $i <= round($stats['moyenne'] ?? 0) ? 'text-warning' : 'text-muted' ?>"></i>
                        <?php endfor; ?>
                    </div>
                    <p class="text-muted"><?= $stats['total'] ?? 0 ?> avis</p>
                </div>
                <div class="col-md-9">
                    <?php if (empty($avis)): ?>
                        <p class="text-muted">Aucun avis pour le moment</p>
                    <?php else: ?>
                        <?php foreach ($avis as $a): ?>
                            <div class="mb-3 pb-3 border-bottom">
                                <div class="d-flex justify-content-between">
                                    <strong><?= htmlspecialchars($a['nom']) ?></strong>
                                    <small class="text-muted"><?= date('d/m/Y', strtotime($a['date_creation'])) ?></small>
                                </div>
                                <div class="text-warning mb-2">
                                    <?php for($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star <?= $i <= $a['note'] ? 'text-warning' : 'text-muted' ?>"></i>
                                    <?php endfor; ?>
                                </div>
                                <p><?= nl2br(htmlspecialchars($a['commentaire'])) ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    
                    <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] != $annonce['user_id']): ?>
                        <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#avisModal">
                            <i class="fas fa-pen"></i> Donner mon avis
                        </button>
                    <?php endif; ?>
                    
                    <a href="avis.php?get_avis=<?= $id ?>" class="btn btn-outline-secondary">
                        Voir tous les avis
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal pour ajouter un avis -->
<div class="modal fade" id="avisModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="avis.php?annonce_id=<?= $id ?>">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title">Donner mon avis</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Note</label>
                        <div class="rating-input">
                            <?php for($i = 5; $i >= 1; $i--): ?>
                                <input type="radio" name="note" value="<?= $i ?>" id="star<?= $i ?>" required>
                                <label for="star<?= $i ?>" class="text-warning fs-4">★</label>
                            <?php endfor; ?>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label>Commentaire</label>
                        <textarea name="commentaire" class="form-control" rows="4" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-warning">Publier l'avis</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.rating-input {
    direction: rtl;
    unicode-bidi: bidi-override;
}
.rating-input input {
    display: none;
}
.rating-input label {
    cursor: pointer;
    font-size: 25px;
}
.rating-input input:checked ~ label {
    color: #ffc107;
}
.rating-input label:hover, .rating-input label:hover ~ label {
    color: #ffc107;
}
</style>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>