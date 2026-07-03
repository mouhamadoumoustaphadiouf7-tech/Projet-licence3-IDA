<?php
session_start();
require_once 'database.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Récupérer les informations de l'utilisateur
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    session_destroy();
    header('Location: login.php');
    exit;
}

// Traitement du formulaire de mise à jour
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telephone = trim($_POST['telephone'] ?? '');
    $ville = trim($_POST['ville'] ?? '');
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validation des champs obligatoires
    if (empty($nom) || empty($email)) {
        $error = 'Le nom et l\'email sont obligatoires.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Veuillez entrer une adresse email valide.';
    } else {
        // Vérifier si l'email est déjà utilisé par un autre utilisateur
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $user_id]);
        if ($stmt->fetch()) {
            $error = 'Cet email est déjà utilisé par un autre compte.';
        } else {
            // Préparer la mise à jour
            $update_fields = [];
            $params = [];
            
            // Champs de base
            $update_fields[] = "nom = ?";
            $params[] = $nom;
            
            $update_fields[] = "email = ?";
            $params[] = $email;
            
            $update_fields[] = "telephone = ?";
            $params[] = $telephone;
            
            $update_fields[] = "ville = ?";
            $params[] = $ville;
            
            // Gestion du mot de passe
            if (!empty($new_password)) {
                // Vérifier le mot de passe actuel
                if (empty($current_password)) {
                    $error = 'Veuillez entrer votre mot de passe actuel pour le modifier.';
                } elseif (!password_verify($current_password, $user['mot_de_passe'])) {
                    $error = 'Le mot de passe actuel est incorrect.';
                } elseif (strlen($new_password) < 6) {
                    $error = 'Le nouveau mot de passe doit contenir au moins 6 caractères.';
                } elseif ($new_password !== $confirm_password) {
                    $error = 'Les nouveaux mots de passe ne correspondent pas.';
                } else {
                    $update_fields[] = "mot_de_passe = ?";
                    $params[] = password_hash($new_password, PASSWORD_DEFAULT);
                }
            }
            
            // Si pas d'erreur, mettre à jour
            if (empty($error)) {
                $params[] = $user_id;
                $sql = "UPDATE users SET " . implode(", ", $update_fields) . " WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                
                if ($stmt->execute($params)) {
                    $success = 'Profil mis à jour avec succès !';
                    // Mettre à jour la session
                    $_SESSION['user_name'] = $nom;
                    // Recharger les données
                    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
                    $stmt->execute([$user_id]);
                    $user = $stmt->fetch();
                } else {
                    $error = 'Erreur lors de la mise à jour du profil.';
                }
            }
        }
    }
}

// Statistiques de l'utilisateur
$stmt = $pdo->prepare("SELECT COUNT(*) as total_ads FROM annonces WHERE user_id = ?");
$stmt->execute([$user_id]);
$total_ads = $stmt->fetch()['total_ads'];

$stmt = $pdo->prepare("SELECT COUNT(*) as total_views FROM annonces WHERE user_id = ?");
$stmt->execute([$user_id]);
$total_views = $stmt->fetch()['total_views'];

$stmt = $pdo->prepare("SELECT COUNT(*) as total_reservations FROM locations WHERE user_id = ?");
$stmt->execute([$user_id]);
$total_reservations = $stmt->fetch()['total_reservations'];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon profil - SenAutoMarket</title>
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
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: #ffc107;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 2.5rem;
            font-weight: bold;
            color: #0a2b3e;
        }
        
        .user-avatar img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
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
        
        .profile-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .profile-card .form-label {
            font-weight: 600;
        }
        
        .profile-card .form-control, .profile-card .form-select {
            border-radius: 10px;
            padding: 10px 15px;
            border: 1px solid #e0e0e0;
            transition: all 0.3s;
        }
        
        .profile-card .form-control:focus, .profile-card .form-select:focus {
            border-color: #ffc107;
            box-shadow: 0 0 0 0.2rem rgba(255,193,7,0.25);
        }
        
        .stat-box {
            background: white;
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: transform 0.3s;
        }
        
        .stat-box:hover {
            transform: translateY(-5px);
        }
        
        .stat-box .number {
            font-size: 2rem;
            font-weight: bold;
            color: #ffc107;
        }
        
        .stat-box .label {
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        .btn-save {
            background: linear-gradient(135deg, #0a2b3e 0%, #1a4a6f 100%);
            color: white;
            padding: 12px 30px;
            border-radius: 10px;
            font-weight: bold;
            transition: all 0.3s;
        }
        
        .btn-save:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            color: white;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                min-height: auto;
                position: relative;
            }
            .main-content {
                padding: 20px;
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
                        <span class="badge bg-info">Membre</span>
                    </div>
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
                    <a class="nav-link" href="my_reservations.php">
                        <i class="fas fa-calendar-check"></i> Mes réservations
                    </a>
                    <a class="nav-link" href="favoris.php">
                        <i class="fas fa-heart"></i> Mes favoris
                    </a>
                    <a class="nav-link" href="messages.php">
                        <i class="fas fa-envelope"></i> Messages
                    </a>
                    <a class="nav-link active" href="profile.php">
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
                        <h2 class="mb-1"><i class="fas fa-user"></i> Mon profil</h2>
                        <p class="text-muted">Gérez vos informations personnelles</p>
                    </div>
                    <div>
                        <a href="settings.php" class="btn btn-outline-secondary">
                            <i class="fas fa-cog"></i> Paramètres
                        </a>
                    </div>
                </div>
                
                <!-- Statistiques -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="stat-box">
                            <div class="number"><?= $total_ads ?></div>
                            <div class="label"><i class="fas fa-car"></i> Annonces publiées</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-box">
                            <div class="number"><?= number_format($total_views, 0, ',', ' ') ?></div>
                            <div class="label"><i class="fas fa-eye"></i> Vues totales</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-box">
                            <div class="number"><?= $total_reservations ?></div>
                            <div class="label"><i class="fas fa-calendar-check"></i> Réservations</div>
                        </div>
                    </div>
                </div>
                
                <!-- Messages -->
                <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle"></i> <?= $success ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle"></i> <?= $error ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <!-- Formulaire -->
                <div class="profile-card">
                    <form method="POST" action="" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-6">
                                <h5 class="mb-3"><i class="fas fa-user-circle"></i> Informations personnelles</h5>
                                
                                <div class="mb-3">
                                    <label class="form-label">Nom complet *</label>
                                    <input type="text" class="form-control" name="nom" 
                                           value="<?= htmlspecialchars($user['nom']) ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Email *</label>
                                    <input type="email" class="form-control" name="email" 
                                           value="<?= htmlspecialchars($user['email']) ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Téléphone</label>
                                    <input type="tel" class="form-control" name="telephone" 
                                           value="<?= htmlspecialchars($user['telephone'] ?? '') ?>" 
                                           placeholder="+221 77 123 45 67">
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Ville</label>
                                    <input type="text" class="form-control" name="ville" 
                                           value="<?= htmlspecialchars($user['ville'] ?? '') ?>" 
                                           placeholder="Dakar, Thiès...">
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <h5 class="mb-3"><i class="fas fa-lock"></i> Changer le mot de passe</h5>
                                <p class="text-muted small">Laissez vide si vous ne voulez pas modifier</p>
                                
                                <div class="mb-3">
                                    <label class="form-label">Mot de passe actuel</label>
                                    <input type="password" class="form-control" name="current_password" 
                                           placeholder="Entrez votre mot de passe actuel">
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Nouveau mot de passe</label>
                                    <input type="password" class="form-control" name="new_password" 
                                           placeholder="Minimum 6 caractères">
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Confirmer le nouveau mot de passe</label>
                                    <input type="password" class="form-control" name="confirm_password" 
                                           placeholder="Confirmez le nouveau mot de passe">
                                </div>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <div class="d-flex justify-content-end gap-2">
                            <a href="dashboard.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Annuler
                            </a>
                            <button type="submit" class="btn btn-save">
                                <i class="fas fa-save"></i> Enregistrer les modifications
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- Informations du compte -->
                <div class="profile-card mt-4">
                    <h5><i class="fas fa-info-circle"></i> Informations du compte</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>ID du compte :</strong> #<?= $user['id'] ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Rôle :</strong> 
                                <span class="badge <?= $user['role'] == 'admin' ? 'bg-danger' : 'bg-secondary' ?>">
                                    <?= $user['role'] == 'admin' ? 'Administrateur' : 'Utilisateur' ?>
                                </span>
                            </p>
                            <p><strong>Statut :</strong> 
                                <span class="badge bg-success">Actif</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>