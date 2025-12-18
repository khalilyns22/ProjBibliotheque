<?php
require_once 'auth.php';
requireLogin();
require_once 'config.php';

$message = '';
$messageType = '';
$emprunts = [];

$conn = getConnection();

// Handle return
if (isset($_GET['return_id'])) {
    $return_id = $_GET['return_id'];
    $date_retour = date('Y-m-d');
    
    $stmt = $conn->prepare("UPDATE emprunts SET date_retour_effective = ?, statut = 'retourné' WHERE id = ? AND statut = 'emprunté'");
    $stmt->bind_param("si", $date_retour, $return_id);
    
    if ($stmt->execute()) {
        $message = "Livre retourné avec succès!";
        $messageType = "success";
    } else {
        $message = "Erreur lors du retour du livre.";
        $messageType = "error";
    }
    
    $stmt->close();
}

// Get all active borrowings
$result = $conn->query("
    SELECT e.*, l.titre, l.auteur 
    FROM emprunts e 
    INNER JOIN livres l ON e.livre_id = l.id 
    WHERE e.statut = 'emprunté' 
    ORDER BY e.date_emprunt DESC
");
$emprunts = $result->fetch_all(MYSQLI_ASSOC);
$conn->close();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Retourner un livre - Gestion de Bibliothèque</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>↩️ Retourner un livre</h1>
            <a href="index.php" class="btn btn-secondary">← Retour à la liste</a>
        </header>

        <?php if ($message): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if (empty($emprunts)): ?>
            <div class="no-books">
                <p>✅ Aucun livre emprunté actuellement</p>
                <a href="index.php" class="btn btn-primary">Voir tous les livres</a>
            </div>
        <?php else: ?>
            <div class="emprunts-list">
                <?php foreach ($emprunts as $emprunt): 
                    $date_prevue = new DateTime($emprunt['date_retour_prevue']);
                    $today = new DateTime();
                    $is_overdue = $date_prevue < $today;
                ?>
                    <div class="emprunt-card <?php echo $is_overdue ? 'overdue' : ''; ?>">
                        <div class="emprunt-header">
                            <h3><?php echo htmlspecialchars($emprunt['titre']); ?></h3>
                            <?php if ($is_overdue): ?>
                                <span class="overdue-badge">⚠️ En retard</span>
                            <?php endif; ?>
                        </div>
                        <div class="emprunt-info">
                            <p><strong>Auteur:</strong> <?php echo htmlspecialchars($emprunt['auteur']); ?></p>
                            <p><strong>Emprunteur:</strong> <?php echo htmlspecialchars($emprunt['emprunteur']); ?></p>
                            <p><strong>Date d'emprunt:</strong> <?php echo date('d/m/Y', strtotime($emprunt['date_emprunt'])); ?></p>
                            <p><strong>Date de retour prévue:</strong> 
                                <span class="<?php echo $is_overdue ? 'overdue-text' : ''; ?>">
                                    <?php echo date('d/m/Y', strtotime($emprunt['date_retour_prevue'])); ?>
                                </span>
                            </p>
                        </div>
                        <div class="emprunt-actions">
                            <a href="retourner.php?return_id=<?php echo $emprunt['id']; ?>" 
                               class="btn btn-return" 
                               onclick="return confirm('Confirmer le retour de ce livre?');">
                                ✅ Retourner
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="stats">
                <p>Total de livres empruntés: <strong><?php echo count($emprunts); ?></strong></p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>

