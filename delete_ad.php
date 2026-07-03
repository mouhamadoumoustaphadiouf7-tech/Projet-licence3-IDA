<?php
session_start();
require_once 'database.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$id = $_GET['id'] ?? 0;

// Vérifier que l'ID est valide
if ($id <= 0) {
    header('Location: my_ads.php?error=1');
    exit;
}

// Vérifier que l'annonce appartient bien à l'utilisateur
$stmt = $pdo->prepare("SELECT id, image_url FROM annonces WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $user_id]);
$annonce = $stmt->fetch();

if (!$annonce) {
    header('Location: my_ads.php?error=2');
    exit;
}

// Supprimer les images associées (fichiers physiques)
if (!empty($annonce['image_url'])) {
    $image_path = $annonce['image_url'];
    if (file_exists($image_path)) {
        unlink($image_path); // Supprimer le fichier
    }
}

// Supprimer les images de la table annonce_images
$stmt = $pdo->prepare("SELECT image_url FROM annonce_images WHERE annonce_id = ?");
$stmt->execute([$id]);
$images = $stmt->fetchAll();

foreach ($images as $image) {
    if (file_exists($image['image_url'])) {
        unlink($image['image_url']); // Supprimer chaque fichier
    }
}

// Supprimer les enregistrements de la table annonce_images
$stmt = $pdo->prepare("DELETE FROM annonce_images WHERE annonce_id = ?");
$stmt->execute([$id]);

// Supprimer les réservations associées
$stmt = $pdo->prepare("DELETE FROM locations WHERE annonce_id = ?");
$stmt->execute([$id]);

// Supprimer les favoris associés
$stmt = $pdo->prepare("DELETE FROM favoris WHERE annonce_id = ?");
$stmt->execute([$id]);

// Supprimer les avis associés
$stmt = $pdo->prepare("DELETE FROM avis WHERE annonce_id = ?");
$stmt->execute([$id]);

// Supprimer l'annonce
$stmt = $pdo->prepare("DELETE FROM annonces WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $user_id]);

// Redirection avec message de succès
header('Location: my_ads.php?deleted=1');
exit;
?>