<?php
session_start();
require_once 'database.php';

// Fonction pour obtenir une image valide
function getValidImage($image_url, $marque = '') {
    // Si l'URL est vide ou null
    if (empty($image_url)) {
        // Chercher par marque
        $marque_lower = strtolower($marque);
        $brand_images = [
            'toyota' => 'assets/uploads/toyota.webp',
            'honda' => 'assets/uploads/HONDA-CIVIC.jpg',
            'suzuki' => 'assets/uploads/suzukiSwift.jpg',
            'hyundai' => 'assets/uploads/hyundai.jpg',
            'kia' => 'assets/uploads/kia.jpg',
            'jimny' => 'assets/uploads/jimny.png'
        ];
        
        if (isset($brand_images[$marque_lower]) && file_exists($brand_images[$marque_lower])) {
            return $brand_images[$marque_lower];
        }
        
        // Image par défaut
        return 'assets/uploads/HONDA-CIVIC.jpg';
    }
    
    // Vérifier si le fichier existe
    if (file_exists($image_url)) {
        return $image_url;
    }
    
    // Vérifier dans assets/uploads/
    $basename = basename($image_url);
    $new_path = 'assets/uploads/' . $basename;
    if (file_exists($new_path)) {
        return $new_path;
    }
    
    // Image par défaut
    return 'assets/uploads/HONDA-CIVIC.jpg';
}

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$id = $_GET['id'] ?? 0;

$stmt = $pdo->prepare("SELECT * FROM annonces WHERE id = ? AND type_annonce = 'location'");
$stmt->execute([$id]);
$annonce = $stmt->fetch();

if (!$annonce) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date_debut = $_POST['date_debut'];
    $date_fin = $_POST['date_fin'];
    $jours = (strtotime($date_fin) - strtotime($date_debut)) / (60 * 60 * 24);
    $prix_total = $jours * $annonce['prix_location_journalier'];
    
    // Vérifier disponibilité
    $stmt = $pdo->prepare("SELECT * FROM locations WHERE annonce_id = ? AND statut IN ('en_attente', 'confirmee')
                           AND ((date_debut BETWEEN ? AND ?) OR (date_fin BETWEEN ? AND ?))");
    $stmt->execute([$id, $date_debut, $date_fin, $date_debut, $date_fin]);
    
    if ($stmt->rowCount() > 0) {
        $error = "Ce véhicule n'est pas disponible pour ces dates.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO locations (annonce_id, user_id, date_debut, date_fin, prix_total) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$id, $_SESSION['user_id'], $date_debut, $date_fin, $prix_total]);
        $success = "Réservation envoyée ! Le propriétaire vous contactera.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Réserver - <?= htmlspecialchars($annonce['titre']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <nav class="navbar navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php"><i class="fas fa-car"></i> SenAutoMarket</a>
            <a href="detail.php?id=<?= $id ?>" class="btn btn-outline-light">← Retour</a>
        </div>
    </nav>
    
    <div class="container my-5">
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <img src="<?= getValidImage($annonce['image_url'] ?? '', $annonce['marque'] ?? '') ?>" 
                         class="card-img-top" 
                         style="height: 300px; object-fit: cover;"
                         onerror="this.src='assets/uploads/HONDA-CIVIC.jpg'">
                    <div class="card-body">
                        <h4><?= htmlspecialchars($annonce['titre']) ?></h4>
                        <p><i class="fas fa-calendar"></i> <?= $annonce['annee'] ?> | 
                           <i class="fas fa-tachometer-alt"></i> <?= number_format($annonce['kilometrage'], 0, ',', ' ') ?> km</p>
                        <p><i class="fas fa-tag"></i> Marque: <?= htmlspecialchars($annonce['marque']) ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card bg-light">
                    <div class="card-body">
                        <h5>Tarifs</h5>
                        <p><strong><?= number_format($annonce['prix_location_journalier'], 0, ',', ' ') ?> FCFA / jour</strong></p>
                        <p>Caution: <?= number_format($annonce['caution'], 0, ',', ' ') ?> FCFA (remboursable)</p>
                        <hr>
                        
                        <?php if (isset($success)): ?>
                            <div class="alert alert-success"><?= $success ?></div>
                            <a href="index.php" class="btn btn-primary">Retour à l'accueil</a>
                        <?php elseif (isset($error)): ?>
                            <div class="alert alert-danger"><?= $error ?></div>
                        <?php else: ?>
                            <form method="POST">
                                <div class="mb-3">
                                    <label>Date de début *</label>
                                    <input type="date" name="date_debut" id="date_debut" class="form-control" required min="<?= date('Y-m-d') ?>">
                                </div>
                                <div class="mb-3">
                                    <label>Date de fin *</label>
                                    <input type="date" name="date_fin" id="date_fin" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label>Nombre de jours</label>
                                    <input type="text" id="nb_jours" class="form-control" readonly>
                                </div>
                                <div class="mb-3">
                                    <label>Prix total</label>
                                    <input type="text" id="prix_total" class="form-control" readonly>
                                </div>
                                <button type="submit" class="btn btn-success w-100">Confirmer la réservation</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        const prixJournalier = <?= $annonce['prix_location_journalier'] ?>;
        const dateDebut = document.getElementById('date_debut');
        const dateFin = document.getElementById('date_fin');
        
        function calculerPrix() {
            if (dateDebut.value && dateFin.value) {
                const debut = new Date(dateDebut.value);
                const fin = new Date(dateFin.value);
                const jours = Math.ceil((fin - debut) / (1000 * 60 * 60 * 24));
                if (jours > 0) {
                    document.getElementById('nb_jours').value = jours + ' jour(s)';
                    document.getElementById('prix_total').value = (jours * prixJournalier).toLocaleString() + ' FCFA';
                } else {
                    document.getElementById('nb_jours').value = '';
                    document.getElementById('prix_total').value = '';
                }
            }
        }
        
        dateDebut.addEventListener('change', function() {
            dateFin.min = this.value;
            calculerPrix();
        });
        dateFin.addEventListener('change', calculerPrix);
    </script>
</body>
</html>