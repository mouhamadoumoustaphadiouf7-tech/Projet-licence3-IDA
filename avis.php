<?php
session_start();
require_once 'database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$annonce_id = $_GET['annonce_id'] ?? 0;

// Ajouter un avis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $note = $_POST['note'];
    $commentaire = $_POST['commentaire'];
    
    $stmt = $pdo->prepare("INSERT INTO avis (annonce_id, user_id, note, commentaire) VALUES (?, ?, ?, ?)");
    $stmt->execute([$annonce_id, $_SESSION['user_id'], $note, $commentaire]);
    
    header('Location: detail.php?id=' . $annonce_id . '&avis=success');
    exit;
}

// Supprimer un avis
if (isset($_GET['delete'])) {
    $avis_id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM avis WHERE id = ? AND user_id = ?");
    $stmt->execute([$avis_id, $_SESSION['user_id']]);
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
}

// Récupérer les avis pour une annonce
if (isset($_GET['get_avis'])) {
    $annonce_id = $_GET['get_avis'];
    $stmt = $pdo->prepare("SELECT a.*, u.nom as user_name 
                           FROM avis a 
                           JOIN users u ON a.user_id = u.id 
                           WHERE a.annonce_id = ? 
                           ORDER BY a.date_creation DESC");
    $stmt->execute([$annonce_id]);
    $avis = $stmt->fetchAll();
    
    // Calculer la note moyenne
    $stmt = $pdo->prepare("SELECT AVG(note) as moyenne, COUNT(*) as total FROM avis WHERE annonce_id = ?");
    $stmt->execute([$annonce_id]);
    $stats = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Avis et évaluations</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .rating-stars {
            color: #ffc107;
        }
        .user-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: #0a2b3e;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 1.2rem;
        }
        .avis-card {
            transition: transform 0.2s;
        }
        .avis-card:hover {
            transform: translateX(5px);
        }
    </style>
</head>
<body>
    <div class="container my-5">
        <div class="row">
            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-body">
                        <h3 class="display-4 text-warning"><?= number_format($stats['moyenne'] ?? 0, 1) ?></h3>
                        <div class="rating-stars mb-2">
                            <?php 
                            $moyenne = round($stats['moyenne'] ?? 0);
                            for($i = 1; $i <= 5; $i++): 
                            ?>
                                <i class="fas fa-star <?= $i <= $moyenne ? 'text-warning' : 'text-muted' ?>"></i>
                            <?php endfor; ?>
                        </div>
                        <p class="text-muted">Basé sur <?= $stats['total'] ?? 0 ?> avis</p>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Tous les avis</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($avis)): ?>
                            <p class="text-muted text-center">Aucun avis pour le moment</p>
                        <?php else: ?>
                            <?php foreach ($avis as $a): ?>
                                <div class="avis-card mb-3 p-3 border rounded">
                                    <div class="d-flex">
                                        <div class="user-avatar me-3">
                                            <?= strtoupper(substr($a['user_name'], 0, 1)) ?>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-between">
                                                <h6 class="mb-1"><?= htmlspecialchars($a['user_name']) ?></h6>
                                                <small class="text-muted"><?= date('d/m/Y', strtotime($a['date_creation'])) ?></small>
                                            </div>
                                            <div class="rating-stars mb-2">
                                                <?php for($i = 1; $i <= 5; $i++): ?>
                                                    <i class="fas fa-star <?= $i <= $a['note'] ? 'text-warning' : 'text-muted' ?>"></i>
                                                <?php endfor; ?>
                                            </div>
                                            <p class="mb-0"><?= nl2br(htmlspecialchars($a['commentaire'])) ?></p>
                                            <?php if ($a['user_id'] == $_SESSION['user_id']): ?>
                                                <div class="mt-2">
                                                    <a href="avis.php?delete=<?= $a['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer cet avis ?')">
                                                        <i class="fas fa-trash"></i> Supprimer
                                                    </a>
                                                </div>
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
    </div>
</body>
</html>
<?php
    exit;
}
?>