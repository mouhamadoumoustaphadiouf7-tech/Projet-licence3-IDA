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

// Récupérer les préférences de l'utilisateur
$preferences = [];
try {
    $stmt = $pdo->prepare("SELECT * FROM user_preferences WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $preferences = $stmt->fetch() ?: [];
} catch (PDOException $e) {
    $preferences = [];
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_preferences') {
        // Préférences de l'utilisateur
        $notifications = isset($_POST['notifications']) ? 1 : 0;
        $email_alerts = isset($_POST['email_alerts']) ? 1 : 0;
        $sms_alerts = isset($_POST['sms_alerts']) ? 1 : 0;
        $language = $_POST['language'] ?? 'fr';
        $theme = $_POST['theme'] ?? 'light';
        $currency = $_POST['currency'] ?? 'FCFA';
        
        try {
            // Insérer ou mettre à jour les préférences
            $sql = "INSERT INTO user_preferences (user_id, notifications, email_alerts, sms_alerts, language, theme, currency) 
                    VALUES (?, ?, ?, ?, ?, ?, ?) 
                    ON DUPLICATE KEY UPDATE 
                    notifications = VALUES(notifications),
                    email_alerts = VALUES(email_alerts),
                    sms_alerts = VALUES(sms_alerts),
                    language = VALUES(language),
                    theme = VALUES(theme),
                    currency = VALUES(currency)";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$user_id, $notifications, $email_alerts, $sms_alerts, $language, $theme, $currency]);
            
            $success = '✅ Préférences sauvegardées avec succès !';
            
            // Mettre à jour les préférences locales
            $preferences = [
                'notifications' => $notifications,
                'email_alerts' => $email_alerts,
                'sms_alerts' => $sms_alerts,
                'language' => $language,
                'theme' => $theme,
                'currency' => $currency
            ];
            
        } catch (PDOException $e) {
            $error = '❌ Erreur lors de la sauvegarde : ' . $e->getMessage();
        }
        
    } elseif ($action === 'delete_account') {
        // Demande de suppression de compte
        $password = $_POST['password'] ?? '';
        $confirm_delete = isset($_POST['confirm_delete']) ? 1 : 0;
        
        if (empty($password)) {
            $error = 'Veuillez entrer votre mot de passe pour confirmer.';
        } elseif (!password_verify($password, $user['mot_de_passe'])) {
            $error = 'Mot de passe incorrect.';
        } elseif (!$confirm_delete) {
            $error = 'Veuillez confirmer la suppression de votre compte.';
        } else {
            // Supprimer le compte
            try {
                // Démarrer une transaction
                $pdo->beginTransaction();
                
                // Supprimer les annonces de l'utilisateur
                $stmt = $pdo->prepare("DELETE FROM annonces WHERE user_id = ?");
                $stmt->execute([$user_id]);
                
                // Supprimer les réservations
                $stmt = $pdo->prepare("DELETE FROM locations WHERE user_id = ?");
                $stmt->execute([$user_id]);
                
                // Supprimer les favoris
                $stmt = $pdo->prepare("DELETE FROM favoris WHERE user_id = ?");
                $stmt->execute([$user_id]);
                
                // Supprimer les avis
                $stmt = $pdo->prepare("DELETE FROM avis WHERE user_id = ?");
                $stmt->execute([$user_id]);
                
                // Supprimer les messages
                $stmt = $pdo->prepare("DELETE FROM messages WHERE expediteur_id = ? OR destinataire_id = ?");
                $stmt->execute([$user_id, $user_id]);
                
                // Supprimer les préférences
                $stmt = $pdo->prepare("DELETE FROM user_preferences WHERE user_id = ?");
                $stmt->execute([$user_id]);
                
                // Supprimer l'utilisateur
                $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                $stmt->execute([$user_id]);
                
                // Valider la transaction
                $pdo->commit();
                
                // Déconnecter l'utilisateur
                session_destroy();
                header('Location: index.php?account_deleted=1');
                exit;
                
            } catch (PDOException $e) {
                $pdo->rollBack();
                $error = '❌ Erreur lors de la suppression du compte : ' . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paramètres - SenAutoMarket</title>
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
        
        .settings-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .settings-card .card-title {
            font-weight: 600;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .settings-card .card-title i {
            color: #ffc107;
            margin-right: 10px;
        }
        
        .settings-card .form-label {
            font-weight: 500;
        }
        
        .settings-card .form-control, .settings-card .form-select {
            border-radius: 10px;
            padding: 10px 15px;
            border: 1px solid #e0e0e0;
            transition: all 0.3s;
        }
        
        .settings-card .form-control:focus, .settings-card .form-select:focus {
            border-color: #ffc107;
            box-shadow: 0 0 0 0.2rem rgba(255,193,7,0.25);
        }
        
        .settings-card .form-check-input:checked {
            background-color: #ffc107;
            border-color: #ffc107;
        }
        
        .btn-save {
            background: linear-gradient(135deg, #0a2b3e 0%, #1a4a6f 100%);
            color: white;
            padding: 10px 30px;
            border-radius: 10px;
            font-weight: bold;
            transition: all 0.3s;
            border: none;
        }
        
        .btn-save:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            color: white;
        }
        
        .btn-danger {
            border-radius: 10px;
            padding: 10px 30px;
        }
        
        .danger-zone {
            border: 2px solid #dc3545;
            background: #fff5f5;
        }
        
        .danger-zone .card-title {
            color: #dc3545;
        }
        
        .danger-zone .card-title i {
            color: #dc3545;
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
                    <a class="nav-link" href="profile.php">
                        <i class="fas fa-user"></i> Mon profil
                    </a>
                    <a class="nav-link active" href="settings.php">
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
                        <h2 class="mb-1"><i class="fas fa-cog"></i> Paramètres</h2>
                        <p class="text-muted">Gérez vos préférences et paramètres de compte</p>
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
                
                <!-- Préférences -->
                <div class="settings-card">
                    <h5 class="card-title"><i class="fas fa-sliders-h"></i> Préférences</h5>
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="update_preferences">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Langue</label>
                                    <select name="language" class="form-select">
                                        <option value="fr" <?= ($preferences['language'] ?? 'fr') == 'fr' ? 'selected' : '' ?>>Français</option>
                                        <option value="en" <?= ($preferences['language'] ?? 'fr') == 'en' ? 'selected' : '' ?>>English</option>
                                        <option value="ar" <?= ($preferences['language'] ?? 'fr') == 'ar' ? 'selected' : '' ?>>العربية</option>
                                        <option value="wo" <?= ($preferences['language'] ?? 'fr') == 'wo' ? 'selected' : '' ?>>Wolof</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Thème</label>
                                    <select name="theme" class="form-select">
                                        <option value="light" <?= ($preferences['theme'] ?? 'light') == 'light' ? 'selected' : '' ?>>Clair</option>
                                        <option value="dark" <?= ($preferences['theme'] ?? 'light') == 'dark' ? 'selected' : '' ?>>Sombre</option>
                                        <option value="auto" <?= ($preferences['theme'] ?? 'light') == 'auto' ? 'selected' : '' ?>>Auto</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Devise</label>
                                    <select name="currency" class="form-select">
                                        <option value="FCFA" <?= ($preferences['currency'] ?? 'FCFA') == 'FCFA' ? 'selected' : '' ?>>FCFA (CFA)</option>
                                        <option value="EUR" <?= ($preferences['currency'] ?? 'FCFA') == 'EUR' ? 'selected' : '' ?>>Euro (€)</option>
                                        <option value="USD" <?= ($preferences['currency'] ?? 'FCFA') == 'USD' ? 'selected' : '' ?>>Dollar ($)</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label d-block">Notifications</label>
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" name="notifications" id="notifications" 
                                               <?= isset($preferences['notifications']) ? ($preferences['notifications'] ? 'checked' : '') : 'checked' ?>>
                                        <label class="form-check-label" for="notifications">
                                            Activer les notifications
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" name="email_alerts" id="email_alerts"
                                               <?= isset($preferences['email_alerts']) ? ($preferences['email_alerts'] ? 'checked' : '') : 'checked' ?>>
                                        <label class="form-check-label" for="email_alerts">
                                            Alertes par email
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" name="sms_alerts" id="sms_alerts"
                                               <?= isset($preferences['sms_alerts']) ? ($preferences['sms_alerts'] ? 'checked' : '') : '' ?>>
                                        <label class="form-check-label" for="sms_alerts">
                                            Alertes par SMS
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="text-end">
                            <button type="submit" class="btn btn-save">
                                <i class="fas fa-save"></i> Sauvegarder les préférences
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- Zone de danger - Suppression du compte -->
                <div class="settings-card danger-zone">
                    <h5 class="card-title"><i class="fas fa-exclamation-triangle"></i> Zone de danger</h5>
                    <p class="text-muted">La suppression de votre compte est irréversible. Toutes vos données seront supprimées.</p>
                    
                    <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteAccountModal">
                        <i class="fas fa-trash"></i> Supprimer mon compte
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal de confirmation de suppression -->
    <div class="modal fade" id="deleteAccountModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="fas fa-exclamation-triangle"></i> Supprimer le compte</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="" onsubmit="return confirm('Êtes-vous vraiment sûr de vouloir supprimer votre compte ? Cette action est irréversible.')">
                    <input type="hidden" name="action" value="delete_account">
                    <div class="modal-body">
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle"></i>
                            <strong>Attention :</strong> Cette action est irréversible. Toutes vos données seront supprimées.
                        </div>
                        <p>Veuillez confirmer la suppression de votre compte :</p>
                        
                        <div class="mb-3">
                            <label class="form-label">Mot de passe *</label>
                            <input type="password" class="form-control" name="password" required placeholder="Entrez votre mot de passe">
                        </div>
                        
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="confirm_delete" name="confirm_delete" value="1" required>
                            <label class="form-check-label text-danger" for="confirm_delete">
                                Je comprends que cette action est irréversible
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash"></i> Supprimer définitivement
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>