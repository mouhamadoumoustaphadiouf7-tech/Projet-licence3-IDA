<?php
session_start();
require_once 'database.php';
// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Récupérer les filtres
$status_filter = $_GET['status'] ?? '';
$date_filter = $_GET['date'] ?? '';

// Construction de la requête pour les réservations ENVOYÉES (client)
$sql_client = "SELECT l.*, a.titre, a.marque, a.modele, a.type_annonce, a.prix_location_journalier,
               a.image_url, u.nom as proprietaire_nom, u.telephone as proprietaire_tel, u.email as proprietaire_email
               FROM locations l 
               JOIN annonces a ON l.annonce_id = a.id 
               JOIN users u ON a.user_id = u.id
               WHERE l.user_id = ?";
$params_client = [$user_id];

// Récupérer les réservations REÇUES (propriétaire)
$sql_proprio = "SELECT l.*, a.titre, a.marque, a.modele, a.type_annonce, a.prix_location_journalier,
                a.image_url, u.nom as client_nom, u.telephone as client_tel, u.email as client_email
                FROM locations l 
                JOIN annonces a ON l.annonce_id = a.id 
                JOIN users u ON l.user_id = u.id
                WHERE a.user_id = ?";
$params_proprio = [$user_id];

// Appliquer les filtres
if (!empty($status_filter)) {
    $sql_client .= " AND l.statut = ?";
    $sql_proprio .= " AND l.statut = ?";
    $params_client[] = $status_filter;
    $params_proprio[] = $status_filter;
}

if (!empty($date_filter)) {
    if ($date_filter == 'upcoming') {
        $sql_client .= " AND l.date_debut >= CURDATE()";
        $sql_proprio .= " AND l.date_debut >= CURDATE()";
    } elseif ($date_filter == 'past') {
        $sql_client .= " AND l.date_fin < CURDATE()";
        $sql_proprio .= " AND l.date_fin < CURDATE()";
    }
}

$sql_client .= " ORDER BY l.date_reservation DESC";
$sql_proprio .= " ORDER BY l.date_reservation DESC";

$stmt_client = $pdo->prepare($sql_client);
$stmt_client->execute($params_client);
$reservations_client = $stmt_client->fetchAll();

$stmt_proprio = $pdo->prepare($sql_proprio);
$stmt_proprio->execute($params_proprio);
$reservations_proprio = $stmt_proprio->fetchAll();

// Statistiques des réservations
$stmt = $pdo->prepare("SELECT 
                       COUNT(*) as total,
                       SUM(CASE WHEN statut = 'en_attente' THEN 1 ELSE 0 END) as en_attente,
                       SUM(CASE WHEN statut = 'confirmee' THEN 1 ELSE 0 END) as confirmee,
                       SUM(CASE WHEN statut = 'terminee' THEN 1 ELSE 0 END) as terminee,
                       SUM(CASE WHEN statut = 'annulee' THEN 1 ELSE 0 END) as annulee
                       FROM locations WHERE user_id = ?");
$stmt->execute([$user_id]);
$stats_client = $stmt->fetch();

$stmt = $pdo->prepare("SELECT 
                       COUNT(*) as total,
                       SUM(CASE WHEN statut = 'en_attente' THEN 1 ELSE 0 END) as en_attente
                       FROM locations l 
                       JOIN annonces a ON l.annonce_id = a.id 
                       WHERE a.user_id = ?");
$stmt->execute([$user_id]);
$stats_proprio = $stmt->fetch();

// Traitement des actions (confirmer/annuler)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reservation_id = $_POST['reservation_id'] ?? 0;
    $action = $_POST['action'] ?? '';
    $new_status = '';
    
    if ($action === 'confirm') {
        $new_status = 'confirmee';
        $message = 'Réservation confirmée avec succès !';
    } elseif ($action === 'cancel') {
        $new_status = 'annulee';
        $message = 'Réservation annulée.';
    } elseif ($action === 'complete') {
        $new_status = 'terminee';
        $message = 'Location terminée. Merci !';
    }
    
    if ($new_status) {
        $stmt = $pdo->prepare("UPDATE locations SET statut = ? WHERE id = ?");
        $stmt->execute([$new_status, $reservation_id]);
        
        // Redirection pour éviter la resoumission du formulaire
        header('Location: my_reservations.php?success=1&message=' . urlencode($message));
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes réservations - SenAutoMarket</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: #f5f7fb;
            font-family: 'Poppins', 'Segoe UI', sans-serif;
        }
        
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
        
        .main-content {
            padding: 30px;
        }
        
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 15px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            border: 2px solid transparent;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        
        .stat-card.active {
            border-color: #ffc107;
            background: #fff8e1;
        }
        
        .stat-value {
            font-size: 2rem;
            font-weight: bold;
        }
        
        .booking-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: all 0.3s;
            margin-bottom: 20px;
        }
        
        .booking-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        
        .booking-header {
            background: linear-gradient(135deg, #0a2b3e 0%, #1a4a6f 100%);
            color: white;
            padding: 15px 20px;
        }
        
        .booking-body {
            padding: 20px;
        }
        
        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: bold;
        }
        
        .status-en_attente { background: #ffc107; color: #000; }
        .status-confirmee { background: #28a745; color: #fff; }
        .status-terminee { background: #17a2b8; color: #fff; }
        .status-annulee { background: #dc3545; color: #fff; }
        
        .info-row {
            padding: 10px 0;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .info-row:last-child {
            border-bottom: none;
        }
        
        .filter-bar {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
            border-bottom: 2px solid #e0e0e0;
        }
        
        .tab {
            padding: 10px 20px;
            cursor: pointer;
            font-weight: bold;
            color: #6c757d;
            border: none;
            background: none;
            transition: all 0.3s;
        }
        
        .tab.active {
            color: #ffc107;
            border-bottom: 3px solid #ffc107;
        }
        
        .tab:hover {
            color: #ffc107;
        }
        
        .price {
            font-size: 1.3rem;
            font-weight: bold;
            color: #ffc107;
        }
        
        .btn-action {
            padding: 8px 20px;
            border-radius: 10px;
            font-weight: bold;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                min-height: auto;
                position: relative;
            }
            .main-content {
                padding: 20px;
            }
            .tabs {
                flex-wrap: wrap;
            }
            .tab {
                flex: 1;
                text-align: center;
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
                        <?php 
                        $stmt = $pdo->prepare("SELECT nom FROM users WHERE id = ?");
                        $stmt->execute([$user_id]);
                        $user = $stmt->fetch();
                        echo strtoupper(substr($user['nom'], 0, 1));
                        ?>
                    </div>
                    <h6 class="mb-1"><?= htmlspecialchars($user['nom']) ?></h6>
                    <small class="text-white-50">Mon espace</small>
                </div>
                <nav class="nav flex-column mt-3">
                    <a class="nav-link" href="dashboard.php">
                        <i class="fas fa-tachometer-alt"></i> Tableau de bord
                    </a>
                    <a class="nav-link" href="my_ads.php">
                        <i class="fas fa-list"></i> Mes annonces
                    </a>
                    <a class="nav-link" href="annonce.php">
                        <i class="fas fa-plus-circle"></i> Nouvelle annonce
                    </a>
                    <a class="nav-link active" href="my_reservations.php">
                        <i class="fas fa-calendar-check"></i> Mes réservations
                        <?php if($stats_proprio['en_attente'] > 0): ?>
                            <span class="badge bg-danger float-end"><?= $stats_proprio['en_attente'] ?></span>
                        <?php endif; ?>
                    </a>
                    <a class="nav-link" href="favoris.php">
                        <i class="fas fa-heart"></i> Mes favoris
                    </a>
                    <a class="nav-link" href="messages.php">
                        <i class="fas fa-envelope"></i> Messages
                    </a>
                    <a class="nav-link" href="profile.php">
                        <i class="fas fa-user"></i> Mon profil
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
                        <h2 class="mb-1"><i class="fas fa-calendar-check"></i> Mes réservations</h2>
                        <p class="text-muted">Gérez vos locations de véhicules</p>
                    </div>
                </div>
                
                <!-- Message de succès -->
                <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle"></i> <?= htmlspecialchars($_GET['message'] ?? 'Opération réussie !') ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <!-- Onglets -->
                <div class="tabs">
                    <button class="tab <?= !isset($_GET['tab']) || $_GET['tab'] == 'client' ? 'active' : '' ?>" onclick="showTab('client')">
                        <i class="fas fa-car"></i> Mes locations (<?= count($reservations_client) ?>)
                    </button>
                    <button class="tab <?= isset($_GET['tab']) && $_GET['tab'] == 'proprio' ? 'active' : '' ?>" onclick="showTab('proprio')">
                        <i class="fas fa-users"></i> Demandes reçues 
                        <?php if($stats_proprio['en_attente'] > 0): ?>
                            <span class="badge bg-danger"><?= $stats_proprio['en_attente'] ?> nouvelle(s)</span>
                        <?php endif; ?>
                    </button>
                </div>
                
                <!-- Filtres -->
                <div class="filter-bar">
                    <form method="GET" action="" id="filterForm" class="row g-3">
                        <input type="hidden" name="tab" id="activeTab" value="<?= $_GET['tab'] ?? 'client' ?>">
                        <div class="col-md-4">
                            <select name="status" class="form-select" onchange="this.form.submit()">
                                <option value="">Tous les statuts</option>
                                <option value="en_attente" <?= $status_filter == 'en_attente' ? 'selected' : '' ?>>En attente</option>
                                <option value="confirmee" <?= $status_filter == 'confirmee' ? 'selected' : '' ?>>Confirmée</option>
                                <option value="terminee" <?= $status_filter == 'terminee' ? 'selected' : '' ?>>Terminée</option>
                                <option value="annulee" <?= $status_filter == 'annulee' ? 'selected' : '' ?>>Annulée</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <select name="date" class="form-select" onchange="this.form.submit()">
                                <option value="">Toutes les périodes</option>
                                <option value="upcoming" <?= $date_filter == 'upcoming' ? 'selected' : '' ?>>À venir</option>
                                <option value="past" <?= $date_filter == 'past' ? 'selected' : '' ?>>Passées</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <a href="my_reservations.php" class="btn btn-secondary w-100">
                                <i class="fas fa-undo"></i> Réinitialiser
                            </a>
                        </div>
                    </form>
                </div>
                
                <!-- Section: Mes locations (en tant que client) -->
                <div id="clientTab" class="tab-content" style="display: <?= !isset($_GET['tab']) || $_GET['tab'] == 'client' ? 'block' : 'none' ?>">
                    <h4 class="mb-3"><i class="fas fa-car text-warning"></i> Véhicules que j'ai loués</h4>
                    
                    <?php if (empty($reservations_client)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-calendar-times fa-4x text-muted mb-3"></i>
                            <h4>Aucune réservation trouvée</h4>
                            <p class="text-muted">Vous n'avez pas encore effectué de réservation</p>
                            <a href="index.php?type_annonce=location" class="btn btn-warning">
                                <i class="fas fa-search"></i> Parcourir les véhicules à louer
                            </a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($reservations_client as $resa): 
                            $debut = new DateTime($resa['date_debut']);
                            $fin = new DateTime($resa['date_fin']);
                            $jours = $debut->diff($fin)->days;
                            $est_terminee = $resa['date_fin'] < date('Y-m-d');
                        ?>
                            <div class="booking-card">
                                <div class="booking-header">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h5 class="mb-0"><?= htmlspecialchars($resa['marque'] . ' ' . $resa['modele']) ?></h5>
                                            <small><i class="fas fa-map-marker-alt"></i> Dakar</small>
                                        </div>
                                        <div>
                                            <span class="status-badge status-<?= $resa['statut'] ?>">
                                                <?= $resa['statut'] == 'en_attente' ? 'En attente' : 
                                                   ($resa['statut'] == 'confirmee' ? 'Confirmée' :
                                                   ($resa['statut'] == 'terminee' ? 'Terminée' : 'Annulée')) ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="booking-body">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <img src="<?= $resa['image_url'] ?? 'assets/uploads/HONDA-CIVIC.jpg' ?>" 
                                                 style="width: 100%; height: 100px; object-fit: cover; border-radius: 10px;">
                                        </div>
                                        <div class="col-md-5">
                                            <div class="info-row">
                                                <strong><i class="fas fa-calendar"></i> Période:</strong><br>
                                                Du <?= date('d/m/Y', strtotime($resa['date_debut'])) ?> 
                                                au <?= date('d/m/Y', strtotime($resa['date_fin'])) ?>
                                                <span class="badge bg-secondary"><?= $jours ?> jour(s)</span>
                                            </div>
                                            <div class="info-row">
                                                <strong><i class="fas fa-user"></i> Propriétaire:</strong><br>
                                                <?= htmlspecialchars($resa['proprietaire_nom']) ?>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="info-row">
                                                <strong><i class="fas fa-money-bill-wave"></i> Montant total:</strong><br>
                                                <span class="price"><?= number_format($resa['prix_total'], 0, ',', ' ') ?> FCFA</span>
                                                <br><small>(<?= number_format($resa['prix_location_journalier'], 0, ',', ' ') ?> FCFA/jour)</small>
                                            </div>
                                            <div class="info-row">
                                                <strong><i class="fas fa-phone"></i> Contact:</strong><br>
                                                <a href="tel:<?= $resa['proprietaire_tel'] ?>"><?= $resa['proprietaire_tel'] ?? 'Non renseigné' ?></a>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-3 d-flex gap-2">
                                        <?php if ($resa['statut'] == 'confirmee' && !$est_terminee): ?>
                                            <a href="contact_proprietaire.php?id=<?= $resa['annonce_id'] ?>" class="btn btn-info btn-action">
                                                <i class="fas fa-comment"></i> Contacter
                                            </a>
                                        <?php endif; ?>
                                        
                                        <?php if ($resa['statut'] == 'en_attente'): ?>
                                            <form method="POST" style="display: inline;" onsubmit="return confirm('Annuler cette réservation ?')">
                                                <input type="hidden" name="reservation_id" value="<?= $resa['id'] ?>">
                                                <input type="hidden" name="action" value="cancel">
                                                <button type="submit" class="btn btn-danger btn-action">
                                                    <i class="fas fa-times"></i> Annuler
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        
                                        <?php if ($resa['statut'] == 'terminee'): ?>
                                            <a href="ajouter_avis.php?id=<?= $resa['annonce_id'] ?>" class="btn btn-warning btn-action">
                                                <i class="fas fa-star"></i> Donner mon avis
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
                <!-- Section: Demandes reçues (en tant que propriétaire) -->
                <div id="proprioTab" class="tab-content" style="display: <?= isset($_GET['tab']) && $_GET['tab'] == 'proprio' ? 'block' : 'none' ?>">
                    <h4 class="mb-3"><i class="fas fa-users text-info"></i> Demandes de location reçues</h4>
                    
                    <?php if (empty($reservations_proprio)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                            <h4>Aucune demande reçue</h4>
                            <p class="text-muted">Vous n'avez pas encore reçu de demande de location</p>
                            <a href="annonce.php?type=location" class="btn btn-warning">
                                <i class="fas fa-plus-circle"></i> Mettre un véhicule en location
                            </a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($reservations_proprio as $resa): 
                            $debut = new DateTime($resa['date_debut']);
                            $fin = new DateTime($resa['date_fin']);
                            $jours = $debut->diff($fin)->days;
                            $est_passee = $resa['date_fin'] < date('Y-m-d');
                        ?>
                            <div class="booking-card">
                                <div class="booking-header">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h5 class="mb-0"><?= htmlspecialchars($resa['marque'] . ' ' . $resa['modele']) ?></h5>
                                            <small><i class="fas fa-user"></i> Demandé par: <?= htmlspecialchars($resa['client_nom']) ?></small>
                                        </div>
                                        <div>
                                            <span class="status-badge status-<?= $resa['statut'] ?>">
                                                <?= $resa['statut'] == 'en_attente' ? 'En attente' : 
                                                   ($resa['statut'] == 'confirmee' ? 'Confirmée' :
                                                   ($resa['statut'] == 'terminee' ? 'Terminée' : 'Annulée')) ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="booking-body">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <img src="<?= $resa['image_url'] ?? 'assets/uploads/HONDA-CIVIC.jpg' ?>" 
                                                 style="width: 100%; height: 100px; object-fit: cover; border-radius: 10px;">
                                        </div>
                                        <div class="col-md-5">
                                            <div class="info-row">
                                                <strong><i class="fas fa-calendar"></i> Période souhaitée:</strong><br>
                                                Du <?= date('d/m/Y', strtotime($resa['date_debut'])) ?> 
                                                au <?= date('d/m/Y', strtotime($resa['date_fin'])) ?>
                                                <span class="badge bg-secondary"><?= $jours ?> jour(s)</span>
                                            </div>
                                            <div class="info-row">
                                                <strong><i class="fas fa-phone"></i> Contact client:</strong><br>
                                                <a href="tel:<?= $resa['client_tel'] ?>"><?= $resa['client_tel'] ?? 'Non renseigné' ?></a>
                                                | <a href="mailto:<?= $resa['client_email'] ?>"><?= $resa['client_email'] ?></a>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="info-row">
                                                <strong><i class="fas fa-money-bill-wave"></i> Montant total:</strong><br>
                                                <span class="price"><?= number_format($resa['prix_total'], 0, ',', ' ') ?> FCFA</span>
                                                <br><small>(<?= number_format($resa['prix_location_journalier'], 0, ',', ' ') ?> FCFA/jour)</small>
                                            </div>
                                            <div class="info-row">
                                                <strong><i class="fas fa-calendar-week"></i> Date réservation:</strong><br>
                                                <?= date('d/m/Y H:i', strtotime($resa['date_reservation'])) ?>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-3 d-flex gap-2">
                                        <?php if ($resa['statut'] == 'en_attente'): ?>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="reservation_id" value="<?= $resa['id'] ?>">
                                                <input type="hidden" name="action" value="confirm">
                                                <button type="submit" class="btn btn-success btn-action" onclick="return confirm('Confirmer cette réservation ?')">
                                                    <i class="fas fa-check"></i> Confirmer
                                                </button>
                                            </form>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="reservation_id" value="<?= $resa['id'] ?>">
                                                <input type="hidden" name="action" value="cancel">
                                                <button type="submit" class="btn btn-danger btn-action" onclick="return confirm('Refuser cette réservation ?')">
                                                    <i class="fas fa-times"></i> Refuser
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        
                                        <?php if ($resa['statut'] == 'confirmee' && !$est_passee): ?>
                                            <a href="messages.php?chat_with=<?= $resa['user_id'] ?>" class="btn btn-info btn-action">
                                                <i class="fas fa-comment"></i> Contacter le client
                                            </a>
                                        <?php endif; ?>
                                        
                                        <?php if ($resa['statut'] == 'confirmee' && $est_passee): ?>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="reservation_id" value="<?= $resa['id'] ?>">
                                                <input type="hidden" name="action" value="complete">
                                                <button type="submit" class="btn btn-primary btn-action" onclick="return confirm('Marquer cette location comme terminée ?')">
                                                    <i class="fas fa-check-double"></i> Marquer terminée
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function showTab(tab) {
            document.getElementById('activeTab').value = tab;
            
            if (tab === 'client') {
                document.getElementById('clientTab').style.display = 'block';
                document.getElementById('proprioTab').style.display = 'none';
            } else {
                document.getElementById('clientTab').style.display = 'none';
                document.getElementById('proprioTab').style.display = 'block';
            }
            
            // Mettre à jour l'URL sans recharger
            const url = new URL(window.location.href);
            url.searchParams.set('tab', tab);
            window.history.pushState({}, '', url);
            
            // Recharger la page pour appliquer les filtres
            document.getElementById('filterForm').submit();
        }
        
        // Gestionnaires d'onglets actifs
        document.querySelectorAll('.tab').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.tab').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
            });
        });
    </script>
</body>
</html>