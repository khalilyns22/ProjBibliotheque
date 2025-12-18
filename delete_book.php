<?php
require_once 'auth.php';
requireAdmin();
require_once 'config.php';

$id = $_GET['id'] ?? null;

if ($id) {
    $conn = getConnection();
    $stmt = $conn->prepare("DELETE FROM livres WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    $conn->close();
}

header('Location: index.php');
exit;
?>

