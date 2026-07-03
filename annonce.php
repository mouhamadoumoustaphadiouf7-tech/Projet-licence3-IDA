<?php
session_start();
require_once 'database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$type_annonce = $_GET['type'] ?? 'vente';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre = $_POST['titre'];
    $type_annonce = $_POST['type_annonce'];
    $marque = $_POST['marque'];
    $modele = $_POST['modele'];
    $annee = $_POST['annee'];
    $prix = $type_annonce == 'vente' ? $_POST['prix'] : NULL;
    $prix_location_journalier = $type_annonce == 'location' ? $_POST['prix_location_journalier'] : NULL;
    $caution = $type_annonce == 'location' ? $_POST['caution'] : NULL;
    $kilometrage = $_POST['kilometrage'];
    $carburant = $_POST['carburant'];
    $boite = $_POST['boite'];
    $couleur = $_POST['couleur'];
    $ville = $_POST['ville'];
    $description = $_POST['description'];
    
    $sql = "INSERT INTO annonces (titre, type_annonce, marque, modele, annee, prix, prix_location_journalier, caution, kilometrage, carburant, boite, couleur, ville, description, user_id) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$titre, $type_annonce, $marque, $modele, $annee, $prix, $prix_location_journalier, $caution,
     $kilometrage, $carburant, $boite, $couleur, $ville, $description, $_SESSION['user_id']]);
    
    $annonce_id = $pdo->lastInsertId();
    
    // Gestion des images
    if (!empty($_FILES['images']['name'][0])) {
        $upload_dir = 'uploads/vehicles/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        
        foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
            $file_name = time() . '_' . $key . '_' . basename($_FILES['images']['name'][$key]);
            $target_path = $upload_dir . $file_name;
            if (move_uploaded_file($tmp_name, $target_path)) {
                $stmt = $pdo->prepare("INSERT INTO annonce_images (annonce_id, image_url, ordre) VALUES (?, ?, ?)");
                $stmt->execute([$annonce_id, $target_path, $key]);
            }
        }
    }
    
    header('Location: index.php?success=1');
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= $type_annonce == 'vente' ? 'Vendre' : 'Louer' ?> mon véhicule - SenAutoMarket</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <nav class="navbar navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php"><i class="fas fa-car"></i> SenAutoMarket</a>
            <a href="index.php" class="btn btn-outline-light">Accueil</a>
        </div>
    </nav>
    
    <div class="container my-5">
        <div class="card">
            <div class="card-header <?= $type_annonce == 'vente' ? 'bg-warning' : 'bg-success text-white' ?>">
                <h3 class="mb-0"><i class="fas <?= $type_annonce == 'vente' ? 'fa-tag' : 'fa-key' ?>"></i> 
                <?= $type_annonce == 'vente' ? 'Vendre mon véhicule' : 'Mettre en location' ?></h3>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="type_annonce" value="<?= $type_annonce ?>">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label>Titre *</label>
                                <input type="text" name="titre" class="form-control" required placeholder="Ex: Toyota Yaris 2022">
                            </div>
                            <div class="mb-3">
                                <label>Marque *</label>
                                <select name="marque" class="form-select" required>
                                    <option value="">Sélectionnez</option>
                                    <option>Toyota</option><option>Honda</option><option>Suzuki</option>
                                    <option>Hyundai</option><option>Kia</option><option>Nissan</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label>Modèle *</label>
                                <input type="text" name="modele" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label>Année *</label>
                                <input type="number" name="annee" class="form-control" min="1990" max="2025" required>
                            </div>
                            
                            <?php if ($type_annonce == 'vente'): ?>
                            <div class="mb-3">
                                <label>Prix de vente (FCFA) *</label>
                                <input type="number" name="prix" class="form-control" required>
                            </div>
                            <?php else: ?>
                            <div class="mb-3">
                                <label>Prix location journalier (FCFA) *</label>
                                <input type="number" name="prix_location_journalier" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label>Caution (FCFA) *</label>
                                <input type="number" name="caution" class="form-control" required>
                                <small class="text-muted">Montant remboursable après location</small>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label>Kilométrage (km)</label>
                                <input type="number" name="kilometrage" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label>Carburant</label>
                                <select name="carburant" class="form-select">
                                    <option>Essence</option><option>Diesel</option><option>Électrique</option><option>Hybride</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label>Boîte de vitesse</label>
                                <select name="boite" class="form-select">
                                    <option>Manuelle</option><option>Automatique</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label>Couleur</label>
                                <input type="text" name="couleur" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label>Ville</label>
                                <input type="text" name="ville" class="form-control" placeholder="Dakar, Thiès...">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label>Photos du véhicule</label>
                        <input type="file" name="images[]" class="form-control" multiple accept="image/*">
                        <small class="text-muted">Vous pouvez sélectionner plusieurs photos</small>
                    </div>
                    
                    <div class="mb-3">
                        <label>Description détaillée</label>
                        <textarea name="description" class="form-control" rows="4" 
                            placeholder="État du véhicule, équipements, historique..."></textarea>
                    </div>
                    
                    <button type="submit" class="btn <?= $type_annonce == 'vente' ? 'btn-warning' : 'btn-success' ?> btn-lg w-100">
                        <i class="fas fa-check-circle"></i> Publier l'annonce
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>