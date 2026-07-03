<?php
// database.php - Configuration de la base de données

// Paramètres de connexion
$host = 'localhost';           
$dbname = 'projetsen';     
$username = 'root';           
$password = '';                

try {
    // Créer la connexion PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    
    // Configurer PDO pour qu'il affiche les erreurs
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Configurer PDO pour retourner les résultats en tableau associatif
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // Définir l'encodage UTF-8
    $pdo->exec("SET NAMES utf8mb4");
    
} catch(PDOException $e) {
    // En cas d'erreur, afficher un message (à adapter selon l'environnement)
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}
?>