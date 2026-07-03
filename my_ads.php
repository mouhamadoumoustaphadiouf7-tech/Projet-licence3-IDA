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
$type_filter = $_GET['type'] ?? '';
$status_filter = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';

// Construction de la requête
$sql = "SELECT a.*, 
        (SELECT image_url FROM annonce_images WHERE annonce_id = a.id ORDER BY ordre LIMIT 1) as image,
        (SELECT COUNT(*) FROM locations WHERE annonce_id = a.id AND statut = 'en_attente') as pending_bookings
        FROM annonces a 
        WHERE a.user_id = ?";

$params = [$user_id];

if (!empty($type_filter)) {
    $sql .= " AND a.type_annonce = ?";
    $params[] = $type_filter;
}

if (!empty($status_filter)) {
    if ($status_filter == 'active') {
        $sql .= " AND a.disponibilite = 'disponible'";
    } elseif ($status_filter == 'inactive') {
        $sql .= " AND a.disponibilite = 'loue'";
    }
}

if (!empty($search)) {
    $sql .= " AND (a.titre LIKE ? OR a.marque LIKE ? OR a.modele LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

$sql .= " ORDER BY a.date_publication DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$annonces = $stmt->fetchAll();

// Statistiques des annonces
$stmt = $pdo->prepare("SELECT 
                       COUNT(*) as total,
                       SUM(CASE WHEN type_annonce = 'vente' THEN 1 ELSE 0 END) as vente,
                       SUM(CASE WHEN type_annonce = 'location' THEN 1 ELSE 0 END) as location,
                       SUM(CASE WHEN disponibilite = 'disponible' THEN 1 ELSE 0 END) as active
                       FROM annonces WHERE user_id = ?");
$stmt->execute([$user_id]);
$stats = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes annonces - SenAutoMarket</title>
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
        
        .ad-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: all 0.3s;
            margin-bottom: 20px;
        }
        
        .ad-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        
        .ad-image {
            height: 200px;
            background-size: cover;
            background-position: center;
            position: relative;
        }
        
        .ad-badge {
            position: absolute;
            top: 10px;
            left: 10px;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: bold;
        }
        
        .badge-vente { background: #ffc107; color: #000; }
        .badge-location { background: #28a745; color: #fff; }
        .badge-disponible { background: #28a745; color: #fff; }
        .badge-loue { background: #dc3545; color: #fff; }
        .badge-maintenance { background: #ffc107; color: #000; }
        
        .ad-actions {
            position: absolute;
            bottom: 10px;
            right: 10px;
            display: flex;
            gap: 5px;
        }
        
        .btn-icon {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(0,0,0,0.7);
            color: white;
            transition: all 0.3s;
        }
        
        .btn-icon:hover {
            transform: scale(1.1);
        }
        
        .pending-booking {
            background: #ffc107;
            color: #000;
            padding: 2px 8px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: bold;
        }
        
        .filter-bar {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        @media (max-width: 768px) {
            .sidebar {
                min-height: auto;
                position: relative;
            }
            .main-content {
                padding: 20px;
            }
            .ad-image {
                height: 150px;
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
                    <a class="nav-link active" href="my_ads.php">
                        <i class="fas fa-list"></i> Mes annonces
                    </a>
                    <a class="nav-link" href="annonce.php">
                        <i class="fas fa-plus-circle"></i> Nouvelle annonce
                    </a>
                    <a class="nav-link" href="my_reservations.php">
                        <i class="fas fa-calendar-check"></i> Mes réservations
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
                        <h2 class="mb-1"><i class="fas fa-list"></i> Mes annonces</h2>
                        <p class="text-muted">Gérez toutes vos annonces de vente et location</p>
                    </div>
                    <div>
                        <a href="annonce.php" class="btn btn-warning">
                            <i class="fas fa-plus-circle"></i> Nouvelle annonce
                        </a>
                    </div>
                </div>
                
                <!-- Statistiques -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="stat-card <?= empty($type_filter) ? 'active' : '' ?>" onclick="window.location.href='?<?= $type_filter ? 'type=' : '' ?>'">
                            <div class="stat-value"><?= $stats['total'] ?? 0 ?></div>
                            <div class="text-muted">Total annonces</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card <?= $type_filter == 'vente' ? 'active' : '' ?>" onclick="window.location.href='?type=vente'">
                            <div class="stat-value text-warning"><?= $stats['vente'] ?? 0 ?></div>
                            <div class="text-muted">En vente</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card <?= $type_filter == 'location' ? 'active' : '' ?>" onclick="window.location.href='?type=location'">
                            <div class="stat-value text-success"><?= $stats['location'] ?? 0 ?></div>
                            <div class="text-muted">En location</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card <?= $status_filter == 'active' ? 'active' : '' ?>" onclick="window.location.href='?status=active'">
                            <div class="stat-value text-info"><?= $stats['active'] ?? 0 ?></div>
                            <div class="text-muted">Actives</div>
                        </div>
                    </div>
                </div>
                
                <!-- Barre de recherche et filtres -->
                <div class="filter-bar">
                    <form method="GET" action="" class="row g-3">
                        <div class="col-md-6">
                            <div class="input-group">
                                <input type="text" class="form-control" name="search" 
                                       placeholder="Rechercher par titre, marque ou modèle..." 
                                       value="<?= htmlspecialchars($search) ?>">
                                <button type="submit" class="btn btn-warning">
                                    <i class="fas fa-search"></i> Rechercher
                                </button>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <select name="type" class="form-select" onchange="this.form.submit()">
                                <option value="">Tous les types</option>
                                <option value="vente" <?= $type_filter == 'vente' ? 'selected' : '' ?>>Vente</option>
                                <option value="location" <?= $type_filter == 'location' ? 'selected' : '' ?>>Location</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select name="status" class="form-select" onchange="this.form.submit()">
                                <option value="">Tous les statuts</option>
                                <option value="active" <?= $status_filter == 'active' ? 'selected' : '' ?>>Actives</option>
                                <option value="inactive" <?= $status_filter == 'inactive' ? 'selected' : '' ?>>Inactives</option>
                            </select>
                        </div>
                        <?php if (!empty($search) || !empty($type_filter) || !empty($status_filter)): ?>
                            <div class="col-md-12 text-end">
                                <a href="my_ads.php" class="btn btn-sm btn-secondary">
                                    <i class="fas fa-times"></i> Réinitialiser les filtres
                                </a>
                            </div>
                        <?php endif; ?>
                    </form>
                </div>
                
                <!-- Liste des annonces -->
                <?php if (empty($annonces)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-car-side fa-4x text-muted mb-3"></i>
                        <h4>Aucune annonce trouvée</h4>
                        <p class="text-muted">Vous n'avez pas encore publié d'annonces</p>
                        <a href="annonce.php" class="btn btn-warning">
                            <i class="fas fa-plus-circle"></i> Publier ma première annonce
                        </a>
                    </div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($annonces as $annonce): ?>
                            <div class="col-md-6 col-lg-4">
                                <div class="ad-card">
                                    <div class="ad-image" style="background-image: url('<?= $annonce['image'] ?? 'assets/uploads/HONDA-CIVIC.jpg' ?>')">
                                        <span class="ad-badge <?= $annonce['type_annonce'] == 'vente' ? 'badge-vente' : 'badge-location' ?>">
                                            <?= $annonce['type_annonce'] == 'vente' ? 'VENTE' : 'LOCATION' ?>
                                        </span>
                                        <span class="ad-badge" style="top: 50px; left: 10px; background: rgba(0,0,0,0.7);">
                                            <?= $annonce['disponibilite'] == 'disponible' ? '✓ Disponible' : ($annonce['disponibilite'] == 'loue' ? '❌ Loué' : '🔧 Maintenance') ?>
                                        </span>
                                        <div class="ad-actions">
                                            <a href="detail.php?id=<?= $annonce['id'] ?>" target="_blank" class="btn-icon" title="Voir">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="edit_ad.php?id=<?= $annonce['id'] ?>" class="btn-icon" title="Modifier" style="background: #ffc107; color: #000;">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="delete_ad.php?id=<?= $annonce['id'] ?>" class="btn-icon" title="Supprimer" style="background: #dc3545;" 
                                               onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette annonce ?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="p-3">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h6 class="mb-0"><?= htmlspecialchars($annonce['titre']) ?></h6>
                                            <?php if ($annonce['pending_bookings'] > 0): ?>
                                                <span class="pending-booking">
                                                    <i class="fas fa-clock"></i> <?= $annonce['pending_bookings'] ?> demande(s)
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        <p class="text-muted small mb-2">
                                            <i class="fas fa-calendar"></i> <?= $annonce['annee'] ?> |
                                            <i class="fas fa-tachometer-alt"></i> <?= number_format($annonce['kilometrage'], 0, ',', ' ') ?> km
                                        </p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <?php if ($annonce['type_annonce'] == 'vente'): ?>
                                                    <span class="text-warning fw-bold">
                                                        <?= number_format($annonce['prix'], 0, ',', ' ') ?> FCFA
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-success fw-bold">
                                                        <?= number_format($annonce['prix_location_journalier'], 0, ',', ' ') ?> FCFA/jour
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                            <div>
                                                <small class="text-muted">
                                                    <i class="fas fa-eye"></i> <?= number_format($annonce['views'], 0, ',', ' ') ?> vues
                                                </small>
                                            </div>
                                        </div>
                                        <div class="mt-2">
                                            <small class="text-muted">
                                                <i class="fas fa-clock"></i> Publiée le <?= date('d/m/Y', strtotime($annonce['date_publication'])) ?>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>