<?php
session_start();
require_once 'database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Non connecté']);
    exit;
}

$user_id = $_SESSION['user_id'];
$annonce_id = $_POST['annonce_id'] ?? 0;
$action = $_POST['action'] ?? '';

if ($action == 'add') {
    $stmt = $pdo->prepare("INSERT IGNORE INTO favoris (user_id, annonce_id) VALUES (?, ?)");
    $stmt->execute([$user_id, $annonce_id]);
    echo json_encode(['success' => true, 'action' => 'added']);
} elseif ($action == 'remove') {
    $stmt = $pdo->prepare("DELETE FROM favoris WHERE user_id = ? AND annonce_id = ?");
    $stmt->execute([$user_id, $annonce_id]);
    echo json_encode(['success' => true, 'action' => 'removed']);
} else {
    // Vérifier si déjà favori
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM favoris WHERE user_id = ? AND annonce_id = ?");
    $stmt->execute([$user_id, $annonce_id]);
    $is_favorite = $stmt->fetchColumn() > 0;
    echo json_encode(['success' => true, 'is_favorite' => $is_favorite]);
}
?>