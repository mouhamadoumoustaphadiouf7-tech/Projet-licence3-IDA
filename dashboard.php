<?php
session_start();
require_once 'database.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Récupérer les informations de l'utilisateur
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Statistiques des annonces
$stmt = $pdo->prepare("SELECT 
    COUNT(*) as total_ads,
    SUM(CASE WHEN type_annonce = 'vente' THEN 1 ELSE 0 END) as vente_ads,
    SUM(CASE WHEN type_annonce = 'location' THEN 1 ELSE 0 END) as location_ads,
    SUM(views) as total_views,
    SUM(CASE WHEN type_annonce = 'vente' THEN prix ELSE 0 END) as total_ventes,
    AVG(CASE WHEN type_annonce = 'location' THEN prix_location_journalier ELSE NULL END) as avg_location_price
    FROM annonces WHERE user_id = ?");
$stmt->execute([$user_id]);
$stats = $stmt->fetch();

// Dernières annonces
$stmt = $pdo->prepare("SELECT a.*, 
                       (SELECT image_url FROM annonce_images WHERE annonce_id = a.id ORDER BY ordre LIMIT 1) as image
                       FROM annonces a 
                       WHERE a.user_id = ? 
                       ORDER BY a.date_publication DESC LIMIT 5");
$stmt->execute([$user_id]);
$recent_ads = $stmt->fetchAll();

// Réservations reçues (pour les locations)
$stmt = $pdo->prepare("SELECT l.*, a.titre, a.marque, a.modele, u.nom as client_nom, u.telephone as client_tel
                       FROM locations l 
                       JOIN annonces a ON l.annonce_id = a.id 
                       JOIN users u ON l.user_id = u.id
                       WHERE a.user_id = ? 
                       ORDER BY l.date_reservation DESC LIMIT 5");
$stmt->execute([$user_id]);
$received_bookings = $stmt->fetchAll();

// Messages non lus
$stmt = $pdo->prepare("SELECT COUNT(*) as unread_count FROM messages WHERE destinataire_id = ? AND lu = FALSE");
$stmt->execute([$user_id]);
$unread_count = $stmt->fetch()['unread_count'];

// Favoris reçus (nombre de fois que les annonces de l'utilisateur ont été ajoutées aux favoris)
$stmt = $pdo->prepare("SELECT COUNT(*) as total_favorites FROM favoris f 
                       JOIN annonces a ON f.annonce_id = a.id 
                       WHERE a.user_id = ?");
$stmt->execute([$user_id]);
$favorites_count = $stmt->fetch()['total_favorites'];

// Avis reçus et note moyenne
$stmt = $pdo->prepare("SELECT 
                       COUNT(*) as total_reviews,
                       AVG(note) as avg_rating
                       FROM avis a 
                       JOIN annonces an ON a.annonce_id = an.id 
                       WHERE an.user_id = ?");
$stmt->execute([$user_id]);
$reviews_stats = $stmt->fetch();

// Statistiques mensuelles pour le graphique
$stmt = $pdo->prepare("SELECT 
                       DATE_FORMAT(date_publication, '%Y-%m') as month,
                       COUNT(*) as ads_count
                       FROM annonces 
                       WHERE user_id = ? 
                       GROUP BY DATE_FORMAT(date_publication, '%Y-%m')
                       ORDER BY month DESC LIMIT 6");
$stmt->execute([$user_id]);
$monthly_stats = $stmt->fetchAll();
$monthly_stats = array_reverse($monthly_stats);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord - SenAutoMarket</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background: #f5f7fb;
            font-family: 'Poppins', 'Segoe UI', sans-serif;
        }
        
        /* Sidebar Styles */
        .sidebar {
            background: linear-gradient(135deg, #0a2b3e 0%, #1a4a6f 100%);
            min-height: 100vh;
            color: white;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
        }
        
        .sidebar .user-info {
            padding: 30px 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .user-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: #ffc107;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 2rem;
            font-weight: bold;
            color: #0a2b3e;
        }
        
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 12px 20px;
            transition: all 0.3s;
            border-radius: 10px;
            margin: 5px 10px;
        }
        
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background: rgba(255,255,255,0.1);
            color: white;
            transform: translateX(5px);
        }
        
        .sidebar .nav-link i {
            width: 25px;
            margin-right: 10px;
        }
        
        /* Main Content Styles */
        .main-content {
            padding: 30px;
        }
        
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: transform 0.3s, box-shadow 0.3s;
            border: none;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        
        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
        
        .stat-value {
            font-size: 1.8rem;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: #6c757d;
            font-size: 0.85rem;
        }
        
        .card-custom {
            border: none;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }
        
        .card-custom .card-header {
            background: white;
            border-bottom: 1px solid #e0e0e0;
            padding: 15px 20px;
            font-weight: bold;
            border-radius: 15px 15px 0 0;
        }
        
        .badge-status {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
        }
        
        .badge-vente { background: #ffc107; color: #000; }
        .badge-location { background: #28a745; color: #fff; }
        .badge-en_attente { background: #ffc107; color: #000; }
        .badge-confirmee { background: #28a745; color: #fff; }
        .badge-terminee { background: #17a2b8; color: #fff; }
        .badge-annulee { background: #dc3545; color: #fff; }
        
        .rating-stars {
            color: #ffc107;
            font-size: 0.85rem;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                min-height: auto;
                position: relative;
            }
            .main-content {
                padding: 20px;
            }
            .stat-value {
                font-size: 1.2rem;
            }
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 px-0 sidebar">
                <div class="user-info">
                    <div class="user-avatar">
                        <?= strtoupper(substr($user['nom'], 0, 1)) ?>
                    </div>
                    <h6 class="mb-1"><?= htmlspecialchars($user['nom']) ?></h6>
                    <small class="text-white-50"><?= htmlspecialchars($user['email']) ?></small>
                    <div class="mt-2">
                        <span class="badge bg-info">Membre depuis <?= @date('Y', strtotime($user['date_inscription'])) ?></span>
                    </div>
                </div>
                <nav class="nav flex-column mt-3">
                    <a class="nav-link active" href="dashboard.php">
                        <i class="fas fa-tachometer-alt"></i> Tableau de bord
                    </a>
                    <a class="nav-link" href="my_ads.php">
                        <i class="fas fa-list"></i> Mes annonces
                    </a>
                    <a class="nav-link" href="annonce.php">
                        <i class="fas fa-plus-circle"></i> Nouvelle annonce
                    </a>
                    <a class="nav-link" href="my_reservations.php">
                        <i class="fas fa-calendar-check"></i> Mes réservations
                        <?php if(!empty($received_bookings)): ?>
                            <span class="badge bg-danger float-end"><?= count($received_bookings) ?></span>
                        <?php endif; ?>
                    </a>
                    <a class="nav-link" href="favoris.php">
                        <i class="fas fa-heart"></i> Mes favoris
                    </a>
                    <a class="nav-link" href="messages.php">
                        <i class="fas fa-envelope"></i> Messages
                        <?php if($unread_count > 0): ?>
                            <span class="badge bg-danger float-end"><?= $unread_count ?></span>
                        <?php endif; ?>
                    </a>
                    <a class="nav-link" href="profile.php">
                        <i class="fas fa-user"></i> Mon profil
                    </a>
                    <a class="nav-link" href="settings.php">
                        <i class="fas fa-cog"></i> Paramètres
                    </a>
                    <hr class="bg-light mx-3">
                    <a class="nav-link" href="logout.php">
                        <i class="fas fa-sign-out-alt"></i> Déconnexion
                    </a>
                </nav>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content">
                <!-- En-tête -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2 class="mb-1">Bonjour, <?= htmlspecialchars($user['nom']) ?> !</h2>
                        <p class="text-muted">Bienvenue sur votre espace personnel</p>
                    </div>
                    <div>
                        <a href="annonce.php" class="btn btn-warning">
                            <i class="fas fa-plus-circle"></i> Publier une annonce
                        </a>
                    </div>
                </div>
                
                <!-- Statistiques -->
                <div class="row">
                    <div class="col-md-6 col-lg-3">
                        <div class="stat-card">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="stat-value"><?= $stats['total_ads'] ?? 0 ?></div>
                                    <div class="stat-label">Total annonces</div>
                                </div>
                                <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                                    <i class="fas fa-car"></i>
                                </div>
                            </div>
                            <div class="mt-2">
                                <small class="text-muted">
                                    <i class="fas fa-tag"></i> Vente: <?= $stats['vente_ads'] ?? 0 ?> |
                                    <i class="fas fa-key"></i> Location: <?= $stats['location_ads'] ?? 0 ?>
                                </small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6 col-lg-3">
                        <div class="stat-card">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="stat-value"><?= number_format($stats['total_views'] ?? 0, 0, ',', ' ') ?></div>
                                    <div class="stat-label">Vues totales</div>
                                </div>
                                <div class="stat-icon bg-info bg-opacity-10 text-info">
                                    <i class="fas fa-eye"></i>
                                </div>
                            </div>
                            <div class="mt-2">
                                <small class="text-muted">
                                    <i class="fas fa-chart-line"></i> Moyenne: <?= $stats['total_ads'] > 0 ? round($stats['total_views'] / $stats['total_ads']) : 0 ?> vues/annonce
                                </small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6 col-lg-3">
                        <div class="stat-card">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="stat-value"><?= $favorites_count ?></div>
                                    <div class="stat-label">Favoris reçus</div>
                                </div>
                                <div class="stat-icon bg-danger bg-opacity-10 text-danger">
                                    <i class="fas fa-heart"></i>
                                </div>
                            </div>
                            <div class="mt-2">
                                <small class="text-muted">
                                    <i class="fas fa-star"></i> Intérêt des acheteurs
                                </small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6 col-lg-3">
                        <div class="stat-card">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="stat-value">
                                        <?= number_format($reviews_stats['avg_rating'] ?? 0, 1) ?>
                                        <small class="fs-6">/5</small>
                                    </div>
                                    <div class="stat-label">Note moyenne</div>
                                </div>
                                <div class="stat-icon bg-success bg-opacity-10 text-success">
                                    <i class="fas fa-star"></i>
                                </div>
                            </div>
                            <div class="mt-2">
                                <small class="text-muted">
                                    <i class="fas fa-comments"></i> <?= $reviews_stats['total_reviews'] ?? 0 ?> avis
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Graphique et Messages -->
                <div class="row">
                    <div class="col-md-7">
                        <div class="card-custom">
                            <div class="card-header">
                                <i class="fas fa-chart-line"></i> Évolution des publications
                            </div>
                            <div class="card-body">
                                <canvas id="adsChart" style="height: 300px;"></canvas>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-5">
                        <div class="card-custom">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-envelope"></i> Messages récents</span>
                                <?php if($unread_count > 0): ?>
                                    <a href="messages.php" class="btn btn-sm btn-warning"><?= $unread_count ?> non lu(s)</a>
                                <?php endif; ?>
                            </div>
                            <div class="card-body">
                                <?php
                                $stmt = $pdo->prepare("SELECT m.*, u.nom as expediteur_nom 
                                                       FROM messages m
                                                       JOIN users u ON m.expediteur_id = u.id
                                                       WHERE m.destinataire_id = ?
                                                       ORDER BY m.date_envoi DESC LIMIT 5");
                                $stmt->execute([$user_id]);
                                $recent_messages = $stmt->fetchAll();
                                ?>
                                
                                <?php if (empty($recent_messages)): ?>
                                    <p class="text-muted text-center py-3">Aucun message</p>
                                <?php else: ?>
                                    <?php foreach ($recent_messages as $msg): ?>
                                        <div class="mb-3 pb-3 border-bottom">
                                            <div class="d-flex justify-content-between">
                                                <strong><?= htmlspecialchars($msg['expediteur_nom']) ?></strong>
                                                <small class="text-muted"><?= date('d/m H:i', strtotime($msg['date_envoi'])) ?></small>
                                            </div>
                                            <p class="mb-1 small"><?= htmlspecialchars(substr($msg['message'], 0, 60)) ?>...</p>
                                            <?php if(!$msg['lu']): ?>
                                                <span class="badge bg-danger">Nouveau</span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Dernières annonces -->
                <div class="card-custom">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-clock"></i> Mes dernières annonces</span>
                        <a href="my_ads.php" class="btn btn-sm btn-link">Voir tout →</a>
                    </div>
                    <div class="card-body">
                        <?php if (empty($recent_ads)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-car-side fa-3x text-muted mb-3"></i>
                                <p>Vous n'avez pas encore d'annonces</p>
                                <a href="annonce.php" class="btn btn-warning">Publier ma première annonce</a>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Photo</th>
                                            <th>Titre</th>
                                            <th>Type</th>
                                            <th>Prix</th>
                                            <th>Vues</th>
                                            <th>Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recent_ads as $ad): ?>
                                            <tr>
                                                <td>
                                                    <img src="<?= $ad['image'] ?? 'assets/uploads/HONDA-CIVIC.jpg' ?>" 
                                                         style="width: 50px; height: 40px; object-fit: cover; border-radius: 8px;">
                                                </td>
                                                <td><?= htmlspecialchars($ad['titre']) ?></td>
                                                <td>
                                                    <span class="badge <?= $ad['type_annonce'] == 'vente' ? 'badge-vente' : 'badge-location' ?>">
                                                        <?= $ad['type_annonce'] == 'vente' ? 'Vente' : 'Location' ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if ($ad['type_annonce'] == 'vente'): ?>
                                                        <?= number_format($ad['prix'], 0, ',', ' ') ?> FCFA
                                                    <?php else: ?>
                                                        <?= number_format($ad['prix_location_journalier'], 0, ',', ' ') ?> FCFA/jour
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= number_format($ad['views'], 0, ',', ' ') ?></td>
                                                <td><?= @date('d/m/Y', strtotime($ad['date_publication'])) ?></td>
                                                <td>
                                                    <a href="detail.php?id=<?= $ad['id'] ?>" class="btn btn-sm btn-info" target="_blank">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="edit_ad.php?id=<?= $ad['id'] ?>" class="btn btn-sm btn-warning">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="delete_ad.php?id=<?= $ad['id'] ?>" class="btn btn-sm btn-danger" 
                                                       onclick="return confirm('Supprimer cette annonce ?')">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Réservations reçues -->
                <?php if (!empty($received_bookings)): ?>
                <div class="card-custom">
                    <div class="card-header">
                        <i class="fas fa-calendar-alt"></i> Demandes de réservation reçues
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Client</th>
                                        <th>Véhicule</th>
                                        <th>Période</th>
                                        <th>Montant</th>
                                        <th>Statut</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($received_bookings as $booking): 
                                        $jours = (strtotime($booking['date_fin']) - strtotime($booking['date_debut'])) / (60 * 60 * 24);
                                    ?>
                                        <tr>
                                            <td>
                                                <strong><?= htmlspecialchars($booking['client_nom']) ?></strong><br>
                                                <small><i class="fas fa-phone"></i> <?= $booking['client_tel'] ?? 'Non renseigné' ?></small>
                                            </td>
                                            <td><?= htmlspecialchars($booking['marque'] . ' ' . $booking['modele']) ?></td>
                                            <td>
                                                <?= date('d/m/Y', strtotime($booking['date_debut'])) ?><br>
                                                → <?= date('d/m/Y', strtotime($booking['date_fin'])) ?>
                                                <small>(<?= $jours ?> jours)</small>
                                            </td>
                                            <td><?= number_format($booking['prix_total'], 0, ',', ' ') ?> FCFA</td>
                                            <td>
                                                <span class="badge badge-<?= $booking['statut'] ?>">
                                                    <?= $booking['statut'] == 'en_attente' ? 'En attente' : 
                                                       ($booking['statut'] == 'confirmee' ? 'Confirmée' :
                                                       ($booking['statut'] == 'terminee' ? 'Terminée' : 'Annulée')) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($booking['statut'] == 'en_attente'): ?>
                                                    <button class="btn btn-sm btn-success" onclick="confirmerReservation(<?= $booking['id'] ?>)">
                                                        <i class="fas fa-check"></i> Confirmer
                                                    </button>
                                                    <button class="btn btn-sm btn-danger" onclick="annulerReservation(<?= $booking['id'] ?>)">
                                                        <i class="fas fa-times"></i> Refuser
                                                    </button>
                                                <?php endif; ?>
                                                <a href="tel:<?= $booking['client_tel'] ?>" class="btn btn-sm btn-info">
                                                    <i class="fas fa-phone"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Conseils rapides -->
                <div class="row mt-3">
                    <div class="col-md-4">
                        <div class="stat-card text-center">
                            <i class="fas fa-camera fa-2x text-info mb-2"></i>
                            <h6>Ajoutez des photos</h6>
                            <small class="text-muted">Les annonces avec photos reçoivent 85% plus de vues</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-card text-center">
                            <i class="fas fa-tag fa-2x text-warning mb-2"></i>
                            <h6>Prix compétitif</h6>
                            <small class="text-muted">Comparez les prix du marché pour vendre plus vite</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-card text-center">
                            <i class="fas fa-reply-all fa-2x text-success mb-2"></i>
                            <h6>Répondez rapidement</h6>
                            <small class="text-muted">Les acheteurs apprécient les réponses rapides</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Graphique des annonces
        const ctx = document.getElementById('adsChart').getContext('2d');
        const monthlyData = <?php 
            $months = [];
            $counts = [];
            foreach ($monthly_stats as $stat) {
                $months[] = date('M Y', strtotime($stat['month'] . '-01'));
                $counts[] = $stat['ads_count'];
            }
            echo json_encode(['months' => $months, 'counts' => $counts]);
        ?>;
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: monthlyData.months,
                datasets: [{
                    label: 'Nombre d\'annonces',
                    data: monthlyData.counts,
                    borderColor: '#ffc107',
                    backgroundColor: 'rgba(255, 193, 7, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1 }
                    }
                }
            }
        });
        
        // Fonctions pour gérer les réservations
        function confirmerReservation(id) {
            if (confirm('Confirmer cette réservation ?')) {
                window.location.href = 'update_booking.php?id=' + id + '&status=confirmee';
            }
        }
        
        function annulerReservation(id) {
            if (confirm('Annuler cette réservation ?')) {
                window.location.href = 'update_booking.php?id=' + id + '&status=annulee';
            }
        }
        
        // Animation au scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);
        
        document.querySelectorAll('.stat-card, .card-custom').forEach(el => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(20px)';
            el.style.transition = 'all 0.6s ease-out';
            observer.observe(el);
        });
    </script>
</body>
</html>