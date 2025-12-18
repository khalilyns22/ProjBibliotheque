<?php
// Script temporaire pour générer le hash du mot de passe
// Exécutez: http://localhost/gestion_bibliotheque/generate_password.php

$password = 'password';
$hash = password_hash($password, PASSWORD_DEFAULT);

echo "Password: $password\n";
echo "Hash: $hash\n\n";

// Test du hash
if (password_verify($password, $hash)) {
    echo "✓ Hash valide!\n";
} else {
    echo "✗ Hash invalide!\n";
}

// Hash pour la base de données
echo "\n--- Pour database.sql ---\n";
echo "INSERT INTO utilisateurs (username, password, role, nom_complet) VALUES\n";
echo "('admin', '$hash', 'admin', 'Administrateur'),\n";
echo "('user', '$hash', 'user', 'Utilisateur Test');\n";
?>

