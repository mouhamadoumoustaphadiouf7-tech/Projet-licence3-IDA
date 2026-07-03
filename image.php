<?php
// check_images.php - À placer à la racine du projet
echo "<h2>Vérification des images</h2>";

// Dossier où se trouvent les images
$images_folder = 'assets/uploads/';

// Liste des images à vérifier
$images = [
    'assets/uploads/toyota.webp',
    'assets/uploads/HONDA-CIVIC.jpg',
    'assets/uploads/suzukiSwift.jpg',
    'assets/uploads/hyundai.jpg',
    'assets/uploads/kia.jpg',
    'assets/uploads/jimny.png',
    //'assets/images/default.jpg'
];

echo "<h3>Vérification des fichiers :</h3>";
echo "<ul>";

foreach ($images as $image) {
    if (file_exists($image)) {
        echo "<li style='color: green'>✓ $image existe</li>";
    } else {
        echo "<li style='color: red'>✗ $image n'existe PAS</li>";
    }
}

echo "</ul>";

// Afficher le chemin absolu pour debug
echo "<h3>Informations de debug :</h3>";
echo "Dossier courant : " . __DIR__ . "<br>";
echo "Chemin complet du dossier images : " . realpath('assets/uploads') . "<br>";

// Vérifier si le dossier images existe
if (is_dir('assets/uploads')) {
    echo "<h3>Contenu du dossier assets/uploads :</h3>";
    $files = scandir('assets/uploads');
    echo "<ul>";
    foreach ($files as $file) {
        if ($file != '.' && $file != '..') {
            echo "<li>$file</li>";
        }
    }
    echo "</ul>";
} else {
    echo "<p style='color: red'>Le dossier assets/uploads n'existe pas !</p>";
    echo "<p>Créez-le avec : mkdir -p assets/uploads</p>";
}
?>