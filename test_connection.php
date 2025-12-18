<?php
/**
 * Database Connection Test Script
 * Run this file to test if your database connection is working
 * Access: http://localhost/gestion_bibliotheque/test_connection.php
 */

require_once 'config.php';

echo "<!DOCTYPE html>
<html lang='fr'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Test de Connexion</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .test-box {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .success {
            color: #28a745;
            font-weight: bold;
        }
        .error {
            color: #dc3545;
            font-weight: bold;
        }
        h1 {
            color: #667eea;
        }
        .info {
            background: #e7f3ff;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class='test-box'>
        <h1>🔍 Test de Connexion à la Base de Données</h1>";

// Test 1: Database Connection
echo "<h2>Test 1: Connexion à MySQL</h2>";
try {
    $conn = getConnection();
    echo "<p class='success'>✅ Connexion réussie!</p>";
    echo "<div class='info'>";
    echo "<strong>Serveur:</strong> " . DB_HOST . "<br>";
    echo "<strong>Utilisateur:</strong> " . DB_USER . "<br>";
    echo "<strong>Base de données:</strong> " . DB_NAME . "<br>";
    echo "</div>";
} catch (Exception $e) {
    echo "<p class='error'>❌ Erreur de connexion: " . $e->getMessage() . "</p>";
    echo "<p>Vérifiez vos paramètres dans config.php</p>";
    echo "</div></body></html>";
    exit;
}

// Test 2: Check if database exists
echo "<h2>Test 2: Vérification de la base de données</h2>";
$result = $conn->query("SHOW DATABASES LIKE '" . DB_NAME . "'");
if ($result->num_rows > 0) {
    echo "<p class='success'>✅ La base de données '" . DB_NAME . "' existe!</p>";
} else {
    echo "<p class='error'>❌ La base de données '" . DB_NAME . "' n'existe pas!</p>";
    echo "<p>➡️ Importez le fichier database.sql dans phpMyAdmin</p>";
}

// Test 3: Check if table exists
echo "<h2>Test 3: Vérification de la table 'livres'</h2>";
$result = $conn->query("SHOW TABLES LIKE 'livres'");
if ($result->num_rows > 0) {
    echo "<p class='success'>✅ La table 'livres' existe!</p>";
    
    // Test 4: Count books
    echo "<h2>Test 4: Nombre de livres</h2>";
    $result = $conn->query("SELECT COUNT(*) as total FROM livres");
    $row = $result->fetch_assoc();
    echo "<p class='success'>✅ Nombre de livres dans la base: <strong>" . $row['total'] . "</strong></p>";
    
    if ($row['total'] > 0) {
        echo "<h2>Test 5: Aperçu des livres</h2>";
        $result = $conn->query("SELECT id, titre, auteur FROM livres LIMIT 5");
        echo "<ul>";
        while ($row = $result->fetch_assoc()) {
            echo "<li><strong>" . htmlspecialchars($row['titre']) . "</strong> par " . htmlspecialchars($row['auteur']) . "</li>";
        }
        echo "</ul>";
    }
} else {
    echo "<p class='error'>❌ La table 'livres' n'existe pas!</p>";
    echo "<p>➡️ Importez le fichier database.sql dans phpMyAdmin</p>";
}

$conn->close();

echo "<hr>
        <h2>✅ Résumé</h2>
        <p>Si tous les tests sont verts (✅), votre application est prête!</p>
        <p><a href='index.php' style='display: inline-block; padding: 10px 20px; background: #667eea; color: white; text-decoration: none; border-radius: 5px;'>Aller à l'application →</a></p>
    </div>
</body>
</html>";
?>

