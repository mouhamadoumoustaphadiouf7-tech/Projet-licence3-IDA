<?php
session_start();
require_once 'database.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$success = '';
$error = '';

// Vérifier que l'ID est valide
if ($id <= 0) {
    header('Location: my_ads.php?error=1');
    exit;
}

// Récupérer l'annonce
$stmt = $pdo->prepare("SELECT * FROM annonces WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $user_id]);
$annonce = $stmt->fetch();

if (!$annonce) {
    header('Location: my_ads.php?error=2');
    exit;
}

// Récupérer les images de l'annonce
$stmt = $pdo->prepare("SELECT * FROM annonce_images WHERE annonce_id = ? ORDER BY ordre");
$stmt->execute([$id]);
$images = $stmt->fetchAll();

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre = trim($_POST['titre']);
    $marque = $_POST['marque'];
    $modele = trim($_POST['modele']);
    $annee = (int)$_POST['annee'];
    $prix = isset($_POST['prix']) && !empty($_POST['prix']) ? (int)$_POST['prix'] : NULL;
    $prix_location_journalier = isset($_POST['prix_location_journalier']) && !empty($_POST['prix_location_journalier']) ? (int)$_POST['prix_location_journalier'] : NULL;
    $caution = isset($_POST['caution']) && !empty($_POST['caution']) ? (int)$_POST['caution'] : NULL;
    $kilometrage = isset($_POST['kilometrage']) && !empty($_POST['kilometrage']) ? (int)$_POST['kilometrage'] : NULL;
    $carburant = $_POST['carburant'];
    $boite = $_POST['boite'];
    $couleur = $_POST['couleur'] ?? '';
    $ville = $_POST['ville'];
    $description = trim($_POST['description']);
    $type_annonce = $_POST['type_annonce'];
    $disponibilite = $_POST['disponibilite'] ?? 'disponible';
    
    // Validation
    $errors = [];
    if (empty($titre)) $errors[] = "Le titre est obligatoire.";
    if (empty($marque)) $errors[] = "La marque est obligatoire.";
    if (empty($modele)) $errors[] = "Le modèle est obligatoire.";
    if ($annee < 1990 || $annee > 2025) $errors[] = "L'année doit être entre 1990 et 2025.";
    
    if ($type_annonce == 'vente') {
        if (empty($prix)) $errors[] = "Le prix de vente est obligatoire.";
    } else {
        if (empty($prix_location_journalier)) $errors[] = "Le prix de location journalier est obligatoire.";
        if (empty($caution)) $errors[] = "La caution est obligatoire.";
    }
    
    if (empty($errors)) {
        try {
            // Mettre à jour l'annonce
            $sql = "UPDATE annonces SET 
                    titre = ?, 
                    marque = ?, 
                    modele = ?, 
                    annee = ?, 
                    prix = ?, 
                    prix_location_journalier = ?, 
                    caution = ?, 
                    kilometrage = ?, 
                    carburant = ?, 
                    boite = ?, 
                    couleur = ?, 
                    ville = ?, 
                    description = ?, 
                    disponibilite = ?,
                    type_annonce = ?
                    WHERE id = ? AND user_id = ?";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $titre, $marque, $modele, $annee, $prix, 
                $prix_location_journalier, $caution, $kilometrage, 
                $carburant, $boite, $couleur, $ville, $description, 
                $disponibilite, $type_annonce, $id, $user_id
            ]);
            
            // Gestion des nouvelles images
            if (!empty($_FILES['images']['name'][0])) {
                $upload_dir = 'uploads/vehicles/';
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
                
                // Compter les images existantes
                $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM annonce_images WHERE annonce_id = ?");
                $stmt->execute([$id]);
                $count = $stmt->fetch()['count'];
                
                foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                    if (!empty($tmp_name)) {
                        $file_name = time() . '_' . $key . '_' . basename($_FILES['images']['name'][$key]);
                        $target_path = $upload_dir . $file_name;
                        if (move_uploaded_file($tmp_name, $target_path)) {
                            $ordre = $count + $key + 1;
                            $stmt = $pdo->prepare("INSERT INTO annonce_images (annonce_id, image_url, ordre) VALUES (?, ?, ?)");
                            $stmt->execute([$id, $target_path, $ordre]);
                        }
                    }
                }
            }
            
            $success = "✅ L'annonce a été modifiée avec succès !";
            
            // Recharger les données
            $stmt = $pdo->prepare("SELECT * FROM annonces WHERE id = ? AND user_id = ?");
            $stmt->execute([$id, $user_id]);
            $annonce = $stmt->fetch();
            
            $stmt = $pdo->prepare("SELECT * FROM annonce_images WHERE annonce_id = ? ORDER BY ordre");
            $stmt->execute([$id]);
            $images = $stmt->fetchAll();
            
        } catch (PDOException $e) {
            $error = "❌ Erreur lors de la modification : " . $e->getMessage();
        }
    } else {
        $error = "❌ " . implode("<br>", $errors);
    }
}

// Suppression d'une image
if (isset($_GET['delete_image'])) {
    $image_id = (int)$_GET['delete_image'];
    
    // Récupérer le chemin de l'image
    $stmt = $pdo->prepare("SELECT image_url FROM annonce_images WHERE id = ? AND annonce_id = ?");
    $stmt->execute([$image_id, $id]);
    $image = $stmt->fetch();
    
    if ($image) {
        // Supprimer le fichier physique
        if (file_exists($image['image_url'])) {
            unlink($image['image_url']);
        }
        // Supprimer de la base de données
        $stmt = $pdo->prepare("DELETE FROM annonce_images WHERE id = ? AND annonce_id = ?");
        $stmt->execute([$image_id, $id]);
        
        header('Location: edit_ad.php?id=' . $id . '&image_deleted=1');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier l'annonce - SenAutoMarket</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: #f5f7fb;
            font-family: 'Poppins', 'Segoe UI', sans-serif;
        }
        
        .navbar {
            background: linear-gradient(135deg, #0a2b3e 0%, #1a4a6f 100%);
            padding: 15px 0;
            box-shadow: 0 2px 20px rgba(0,0,0,0.1);
        }
        
        .navbar-brand {
            font-size: 1.8rem;
            font-weight: bold;
        }
        
        .card {
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            border: none;
        }
        
        .card-header {
            border-radius: 20px 20px 0 0 !important;
            padding: 20px;
        }
        
        .card-header.bg-warning {
            background: linear-gradient(135deg, #ffc107, #ffb300) !important;
        }
        
        .card-header.bg-success {
            background: linear-gradient(135deg, #28a745, #20c997) !important;
        }
        
        .form-label {
            font-weight: 600;
        }
        
        .form-control, .form-select {
            border-radius: 10px;
            padding: 10px 15px;
            border: 1px solid #e0e0e0;
            transition: all 0.3s;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: #ffc107;
            box-shadow: 0 0 0 0.2rem rgba(255,193,7,0.25);
        }
        
        .btn-save {
            background: linear-gradient(135deg, #0a2b3e 0%, #1a4a6f 100%);
            color: white;
            padding: 12px;
            border-radius: 10px;
            font-weight: bold;
            font-size: 1.1rem;
            transition: all 0.3s;
            border: none;
        }
        
        .btn-save:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            color: white;
        }
        
        .image-preview {
            width: 150px;
            height: 120px;
            object-fit: cover;
            border-radius: 10px;
            border: 2px solid #e0e0e0;
            transition: all 0.3s;
        }
        
        .image-preview:hover {
            border-color: #ffc107;
        }
        
        .image-container {
            position: relative;
            display: inline-block;
            margin: 5px;
        }
        
        .image-container .btn-remove {
            position: absolute;
            top: -8px;
            right: -8px;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            padding: 0;
            font-size: 12px;
            border: 2px solid white;
        }
        
        @media (max-width: 768px) {
            .image-preview {
                width: 100px;
                height: 80px;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-car"></i> SenAutoMarket
            </a>
            <div>
                <a href="my_ads.php" class="btn btn-outline-light me-2">
                    <i class="fas fa-arrow-left"></i> Mes annonces
                </a>
                <a href="detail.php?id=<?= $id ?>" target="_blank" class="btn btn-outline-light">
                    <i class="fas fa-eye"></i> Voir
                </a>
            </div>
        </div>
    </nav>
    
    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card">
                    <div class="card-header <?= $annonce['type_annonce'] == 'vente' ? 'bg-warning' : 'bg-success text-white' ?>">
                        <h3 class="mb-0">
                            <i class="fas fa-edit"></i> 
                            Modifier l'annonce : <?= htmlspecialchars($annonce['titre']) ?>
                        </h3>
                    </div>
                    <div class="card-body">
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
                        
                        <?php if (isset($_GET['image_deleted'])): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle"></i> Image supprimée avec succès !
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Formulaire -->
                        <form method="POST" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Titre *</label>
                                        <input type="text" name="titre" class="form-control" 
                                               value="<?= htmlspecialchars($annonce['titre']) ?>" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Type d'annonce *</label>
                                        <select name="type_annonce" class="form-select" required>
                                            <option value="vente" <?= $annonce['type_annonce'] == 'vente' ? 'selected' : '' ?>>Vente</option>
                                            <option value="location" <?= $annonce['type_annonce'] == 'location' ? 'selected' : '' ?>>Location</option>
                                        </select>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Marque *</label>
                                        <select name="marque" class="form-select" required>
                                            <option value="">Sélectionnez</option>
                                            <option value="Toyota" <?= $annonce['marque'] == 'Toyota' ? 'selected' : '' ?>>Toyota</option>
                                            <option value="Honda" <?= $annonce['marque'] == 'Honda' ? 'selected' : '' ?>>Honda</option>
                                            <option value="Suzuki" <?= $annonce['marque'] == 'Suzuki' ? 'selected' : '' ?>>Suzuki</option>
                                            <option value="Hyundai" <?= $annonce['marque'] == 'Hyundai' ? 'selected' : '' ?>>Hyundai</option>
                                            <option value="Kia" <?= $annonce['marque'] == 'Kia' ? 'selected' : '' ?>>Kia</option>
                                            <option value="Nissan" <?= $annonce['marque'] == 'Nissan' ? 'selected' : '' ?>>Nissan</option>
                                        </select>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Modèle *</label>
                                        <input type="text" name="modele" class="form-control" 
                                               value="<?= htmlspecialchars($annonce['modele']) ?>" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Année *</label>
                                        <input type="number" name="annee" class="form-control" 
                                               value="<?= $annonce['annee'] ?>" min="1990" max="2025" required>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <!-- Prix selon le type -->
                                    <?php if ($annonce['type_annonce'] == 'vente'): ?>
                                        <div class="mb-3">
                                            <label class="form-label">Prix de vente (FCFA) *</label>
                                            <input type="number" name="prix" class="form-control" 
                                                   value="<?= $annonce['prix'] ?>" required>
                                        </div>
                                    <?php else: ?>
                                        <div class="mb-3">
                                            <label class="form-label">Prix location journalier (FCFA) *</label>
                                            <input type="number" name="prix_location_journalier" class="form-control" 
                                                   value="<?= $annonce['prix_location_journalier'] ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Caution (FCFA) *</label>
                                            <input type="number" name="caution" class="form-control" 
                                                   value="<?= $annonce['caution'] ?>" required>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Kilométrage (km)</label>
                                        <input type="number" name="kilometrage" class="form-control" 
                                               value="<?= $annonce['kilometrage'] ?>">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Carburant</label>
                                        <select name="carburant" class="form-select">
                                            <option value="Essence" <?= $annonce['carburant'] == 'Essence' ? 'selected' : '' ?>>Essence</option>
                                            <option value="Diesel" <?= $annonce['carburant'] == 'Diesel' ? 'selected' : '' ?>>Diesel</option>
                                            <option value="Électrique" <?= $annonce['carburant'] == 'Électrique' ? 'selected' : '' ?>>Électrique</option>
                                            <option value="Hybride" <?= $annonce['carburant'] == 'Hybride' ? 'selected' : '' ?>>Hybride</option>
                                        </select>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Boîte de vitesse</label>
                                        <select name="boite" class="form-select">
                                            <option value="Manuelle" <?= $annonce['boite'] == 'Manuelle' ? 'selected' : '' ?>>Manuelle</option>
                                            <option value="Automatique" <?= $annonce['boite'] == 'Automatique' ? 'selected' : '' ?>>Automatique</option>
                                        </select>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Couleur</label>
                                        <input type="text" name="couleur" class="form-control" 
                                               value="<?= htmlspecialchars($annonce['couleur'] ?? '') ?>">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Ville</label>
                                        <input type="text" name="ville" class="form-control" 
                                               value="<?= htmlspecialchars($annonce['ville']) ?>">
                                    </div>
                                    
                                    <?php if ($annonce['type_annonce'] == 'location'): ?>
                                        <div class="mb-3">
                                            <label class="form-label">Disponibilité</label>
                                            <select name="disponibilite" class="form-select">
                                                <option value="disponible" <?= $annonce['disponibilite'] == 'disponible' ? 'selected' : '' ?>>Disponible</option>
                                                <option value="loue" <?= $annonce['disponibilite'] == 'loue' ? 'selected' : '' ?>>Loué</option>
                                                <option value="maintenance" <?= $annonce['disponibilite'] == 'maintenance' ? 'selected' : '' ?>>En maintenance</option>
                                            </select>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="4"><?= htmlspecialchars($annonce['description']) ?></textarea>
                            </div>
                            
                            <!-- Images existantes -->
                            <?php if (!empty($images)): ?>
                                <div class="mb-4">
                                    <label class="form-label">Images actuelles</label>
                                    <div class="d-flex flex-wrap gap-2">
                                        <?php foreach ($images as $img): ?>
                                            <div class="image-container">
                                                <img src="<?= $img['image_url'] ?>" class="image-preview" alt="Image">
                                                <a href="edit_ad.php?id=<?= $id ?>&delete_image=<?= $img['id'] ?>" 
                                                   class="btn btn-danger btn-remove"
                                                   onclick="return confirm('Supprimer cette image ?')">
                                                    <i class="fas fa-times"></i>
                                                </a>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Ajout de nouvelles images -->
                            <div class="mb-4">
                                <label class="form-label">Ajouter des photos</label>
                                <input type="file" name="images[]" class="form-control" multiple accept="image/*">
                                <small class="text-muted">Vous pouvez sélectionner plusieurs photos</small>
                            </div>
                            
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-save flex-grow-1">
                                    <i class="fas fa-save"></i> Enregistrer les modifications
                                </button>
                                <a href="delete_ad.php?id=<?= $id ?>" class="btn btn-danger" 
                                   onclick="return confirm('Supprimer définitivement cette annonce ?')">
                                    <i class="fas fa-trash"></i> Supprimer
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Aperçu des nouvelles images
        document.querySelector('input[name="images[]"]').addEventListener('change', function() {
            const previewContainer = document.createElement('div');
            previewContainer.className = 'd-flex flex-wrap gap-2 mt-2';
            previewContainer.id = 'new-images-preview';
            
            // Supprimer l'ancien aperçu
            const oldPreview = document.getElementById('new-images-preview');
            if (oldPreview) oldPreview.remove();
            
            for (const file of this.files) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.className = 'image-preview';
                    img.style.border = '2px solid #28a745';
                    previewContainer.appendChild(img);
                };
                reader.readAsDataURL(file);
            }
            
            if (this.files.length > 0) {
                this.parentNode.appendChild(previewContainer);
            }
        });
        
        // Changement de type d'annonce
        document.querySelector('select[name="type_annonce"]').addEventListener('change', function() {
            // Recharger la page avec le nouveau type
            // Vous pouvez aussi masquer/afficher les champs avec JavaScript
        });
    </script>
</body>
</html>