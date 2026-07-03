<?php
session_start();
require_once 'database.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telephone = trim($_POST['telephone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validation
    if (empty($nom) || empty($email) || empty($password)) {
        $error = 'Veuillez remplir tous les champs obligatoires.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Veuillez entrer une adresse email valide.';
    } elseif (strlen($password) < 6) {
        $error = 'Le mot de passe doit contenir au moins 6 caractères.';
    } elseif ($password !== $confirm_password) {
        $error = 'Les mots de passe ne correspondent pas.';
    } else {
        // Vérifier si l'email existe déjà
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->fetch()) {
            $error = 'Cet email est déjà utilisé.';
        } else {
            // Hashage du mot de passe
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insertion de l'utilisateur - SANS date_inscription
            $sql = "INSERT INTO users (nom, email, telephone, mot_de_passe, role) 
                    VALUES (?, ?, ?, ?, 'user')";
            $stmt = $pdo->prepare($sql);
            
            if ($stmt->execute([$nom, $email, $telephone, $hashed_password])) {
                $success = 'Inscription réussie ! Vous pouvez maintenant vous connecter.';
                header('refresh:2;url=login.php');
            } else {
                $error = 'Une erreur est survenue. Veuillez réessayer.';
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
    <title>Inscription - SenAutoMarket</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Poppins', 'Segoe UI', sans-serif;
        }
        .register-container {
            max-width: 500px;
            margin: 50px auto;
            animation: fadeInUp 0.6s ease;
        }
        .card {
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            border: none;
        }
        .card-header {
            background: linear-gradient(135deg, #0a2b3e 0%, #1a4a6f 100%);
            color: white;
            text-align: center;
            border-radius: 20px 20px 0 0 !important;
            padding: 30px;
        }
        .card-header h3 {
            margin-bottom: 10px;
            font-weight: bold;
        }
        .card-body {
            padding: 30px;
        }
        .form-control, .form-select {
            border-radius: 10px;
            padding: 12px 15px;
            border: 1px solid #e0e0e0;
            transition: all 0.3s;
        }
        .form-control:focus, .form-select:focus {
            border-color: #ffc107;
            box-shadow: 0 0 0 0.2rem rgba(255,193,7,0.25);
        }
        .btn-register {
            background: linear-gradient(135deg, #0a2b3e 0%, #1a4a6f 100%);
            color: white;
            padding: 12px;
            border-radius: 10px;
            font-weight: bold;
            font-size: 1.1rem;
            transition: all 0.3s;
        }
        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            color: white;
        }
        .login-link {
            text-align: center;
            margin-top: 20px;
        }
        .login-link a {
            color: #ffc107;
            text-decoration: none;
            font-weight: bold;
        }
        .login-link a:hover {
            text-decoration: underline;
        }
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .password-requirements {
            font-size: 0.85rem;
            color: #6c757d;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="register-container">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-car fa-3x mb-3"></i>
                    <h3>Créer un compte</h3>
                    <p class="mb-0">Rejoignez SenAutoMarket</p>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle"></i> <?= $error ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle"></i> <?= $success ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="" id="registerForm">
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-user"></i> Nom complet *
                            </label>
                            <input type="text" class="form-control" name="nom" 
                                   value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>" 
                                   required placeholder="Jean Dupont">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-envelope"></i> Email *
                            </label>
                            <input type="email" class="form-control" name="email" 
                                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" 
                                   required placeholder="jean@example.com">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-phone"></i> Téléphone
                            </label>
                            <input type="tel" class="form-control" name="telephone" 
                                   value="<?= htmlspecialchars($_POST['telephone'] ?? '') ?>" 
                                   placeholder="+221 77 123 45 67">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                <i class="fas fa-lock"></i> Mot de passe *
                            </label>
                            <input type="password" class="form-control" name="password" 
                                   id="password" required placeholder="••••••">
                            <div class="password-requirements">
                                <small>Le mot de passe doit contenir au moins 6 caractères</small>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label fw-bold">
                                <i class="fas fa-lock"></i> Confirmer le mot de passe *
                            </label>
                            <input type="password" class="form-control" name="confirm_password" 
                                   id="confirm_password" required placeholder="••••••">
                            <div id="passwordMatch" class="password-requirements"></div>
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="terms" required>
                            <label class="form-check-label" for="terms">
                                J'accepte les <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">conditions d'utilisation</a>
                            </label>
                        </div>
                        
                        <button type="submit" class="btn btn-register w-100" id="submitBtn">
                            <i class="fas fa-user-plus"></i> S'inscrire
                        </button>
                    </form>
                    
                    <div class="login-link">
                        <p>Déjà inscrit ? <a href="login.php">Connectez-vous ici</a></p>
                    </div>
                    
                    <hr>
                    <div class="text-center">
                        <p class="text-muted small mb-0">
                            <i class="fas fa-shield-alt"></i> Vos données sont sécurisées
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal Conditions d'utilisation -->
    <div class="modal fade" id="termsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title">Conditions d'utilisation de SenAutoMarket</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <h6>1. Acceptation des conditions</h6>
                    <p>En vous inscrivant sur SenAutoMarket, vous acceptez de respecter ces conditions d'utilisation.</p>
                    
                    <h6>2. Annonces</h6>
                    <p>Vous êtes seul responsable du contenu de vos annonces. SenAutoMarket se réserve le droit de modérer ou supprimer toute annonce non conforme.</p>
                    
                    <h6>3. Transactions</h6>
                    <p>SenAutoMarket est une plateforme de mise en relation. Les transactions se font directement entre acheteurs et vendeurs.</p>
                    
                    <h6>4. Sécurité</h6>
                    <p>Nous vous recommandons de vérifier les véhicules avant tout achat et de privilégier les rendez-vous en personne.</p>
                    
                    <h6>5. Protection des données</h6>
                    <p>Vos données personnelles sont protégées conformément à la réglementation en vigueur.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">J'ai compris</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Validation du mot de passe en temps réel
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirm_password');
        const passwordMatch = document.getElementById('passwordMatch');
        const submitBtn = document.getElementById('submitBtn');
        const termsCheckbox = document.getElementById('terms');
        
        function validatePassword() {
            if (password.value.length > 0 && confirmPassword.value.length > 0) {
                if (password.value === confirmPassword.value) {
                    passwordMatch.innerHTML = '<i class="fas fa-check-circle text-success"></i> Les mots de passe correspondent';
                    passwordMatch.className = 'password-requirements text-success';
                    return true;
                } else {
                    passwordMatch.innerHTML = '<i class="fas fa-times-circle text-danger"></i> Les mots de passe ne correspondent pas';
                    passwordMatch.className = 'password-requirements text-danger';
                    return false;
                }
            } else {
                passwordMatch.innerHTML = '';
                return false;
            }
        }
        
        function validateForm() {
            const passwordValid = password.value.length >= 6;
            const passwordsMatch = password.value === confirmPassword.value && password.value.length > 0;
            const termsAccepted = termsCheckbox.checked;
            
            if (passwordValid && passwordsMatch && termsAccepted) {
                submitBtn.disabled = false;
                submitBtn.style.opacity = '1';
            } else {
                submitBtn.disabled = true;
                submitBtn.style.opacity = '0.6';
            }
        }
        
        password.addEventListener('keyup', () => {
            validatePassword();
            validateForm();
        });
        
        confirmPassword.addEventListener('keyup', () => {
            validatePassword();
            validateForm();
        });
        
        termsCheckbox.addEventListener('change', validateForm);
        
        // Validation initiale
        validateForm();
        
        // Animation au chargement
        document.querySelector('.register-container').style.opacity = '0';
        setTimeout(() => {
            document.querySelector('.register-container').style.opacity = '1';
        }, 100);
        
        // Empêcher la soumission si le mot de passe ne correspond pas
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            if (password.value !== confirmPassword.value) {
                e.preventDefault();
                alert('Les mots de passe ne correspondent pas !');
            }
            if (!termsCheckbox.checked) {
                e.preventDefault();
                alert('Veuillez accepter les conditions d\'utilisation');
            }
            if (password.value.length < 6) {
                e.preventDefault();
                alert('Le mot de passe doit contenir au moins 6 caractères');
            }
        });
    </script>
</body>
</html>