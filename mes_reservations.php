<?php
session_start();
require_once 'database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$stmt = $pdo->prepare("SELECT l.*, a.titre, a.marque, a.modele, a.prix_location_journalier 
                       FROM locations l 
                       JOIN annonces a ON l.annonce_id = a.id 
                       WHERE l.user_id = ? 
                       ORDER BY l.date_reservation DESC");
$stmt->execute([$_SESSION['user_id']]);
$reservations = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mes réservations - SenAutoMarket</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .statut {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: bold;
        }
        .statut-en_attente { background: #ffc107; color: #000; }
        .statut-confirmee { background: #28a745; color: #fff; }
        .statut-terminee { background: #17a2b8; color: #fff; }
        .statut-annulee { background: #dc3545; color: #fff; }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-car"></i> SenAutoMarket
            </a>
            <div>
                <a href="index.php" class="btn btn-outline-light me-2">Accueil</a>
                <a href="dashboard.php" class="btn btn-outline-light">Dashboard</a>
            </div>
        </div>
    </nav>
    
    <div class="container my-5">
        <h2 class="mb-4"><i class="fas fa-calendar-check"></i> Mes réservations</h2>
        
        <?php if (empty($reservations)): ?>
            <div class="alert alert-info text-center">
                <i class="fas fa-info-circle"></i> Vous n'avez aucune réservation.
                <br><a href="index.php?type_annonce=location" class="btn btn-warning mt-3">Parcourir les véhicules à louer</a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Véhicule</th>
                            <th>Période</th>
                            <th>Nombre de jours</th>
                            <th>Prix total</th>
                            <th>Statut</th>
                            <th>Date réservation</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reservations as $reservation): 
                            $debut = new DateTime($reservation['date_debut']);
                            $fin = new DateTime($reservation['date_fin']);
                            $jours = $debut->diff($fin)->days;
                        ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($reservation['marque'] . ' ' . $reservation['modele']) ?></strong><br>
                                    <small><?= htmlspecialchars($reservation['titre']) ?></small>
                                </td>
                                <td>
                                    <?= date('d/m/Y', strtotime($reservation['date_debut'])) ?><br>
                                    → <?= date('d/m/Y', strtotime($reservation['date_fin'])) ?>
                                </td>
                                <td><?= $jours ?> jour(s)</td>
                                <td><?= number_format($reservation['prix_total'], 0, ',', ' ') ?> FCFA</td>
                                <td>
                                    <span class="statut statut-<?= $reservation['statut'] ?>">
                                        <?php 
                                            $statuts = [
                                                'en_attente' => 'En attente',
                                                'confirmee' => 'Confirmée',
                                                'annulee' => 'Annulée',
                                                'terminee' => 'Terminée'
                                            ];
                                            echo $statuts[$reservation['statut']];
                                        ?>
                                    </span>
                                </td>
                                <td><?= date('d/m/Y H:i', strtotime($reservation['date_reservation'])) ?></td>
                                <td>
                                    <?php if ($reservation['statut'] == 'en_attente'): ?>
                                        <a href="annuler_reservation.php?id=<?= $reservation['id'] ?>" 
                                           class="btn btn-danger btn-sm"
                                           onclick="return confirm('Annuler cette réservation ?')">
                                            <i class="fas fa-times"></i> Annuler
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>