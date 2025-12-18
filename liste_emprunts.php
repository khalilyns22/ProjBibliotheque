<?php
require_once 'auth.php';
requireLogin();
require_once 'config.php';

$conn = getConnection();

// Get all borrowings (active and returned)
$result = $conn->query("
    SELECT e.*, l.titre, l.auteur 
    FROM emprunts e 
    INNER JOIN livres l ON e.livre_id = l.id 
    ORDER BY e.date_emprunt DESC
");
$emprunts = $result->fetch_all(MYSQLI_ASSOC);
$conn->close();

// Filter active and returned
$empruntes = array_filter($emprunts, function($e) { return $e['statut'] == 'emprunté'; });
$retournes = array_filter($emprunts, function($e) { return $e['statut'] == 'retourné'; });
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des emprunts - Gestion de Bibliothèque</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>📋 Liste des emprunts</h1>
            <a href="index.php" class="btn btn-secondary">← Retour à la liste</a>
        </header>

        <div class="actions-bar">
            <a href="retourner.php" class="btn btn-primary">↩️ Retourner un livre</a>
        </div>

        <h2 class="section-title">Livres empruntés (<?php echo count($empruntes); ?>)</h2>
        <?php if (empty($empruntes)): ?>
            <div class="no-books">
                <p>✅ Aucun livre emprunté actuellement</p>
            </div>
        <?php else: ?>
            <div class="emprunts-list">
                <?php foreach ($empruntes as $emprunt): 
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
        <?php endif; ?>

        <h2 class="section-title">Historique des retours (<?php echo count($retournes); ?>)</h2>
        <?php if (empty($retournes)): ?>
            <div class="no-books">
                <p>📚 Aucun livre retourné</p>
            </div>
        <?php else: ?>
            <div class="emprunts-list">
                <?php foreach ($retournes as $emprunt): ?>
                    <div class="emprunt-card returned">
                        <div class="emprunt-header">
                            <h3><?php echo htmlspecialchars($emprunt['titre']); ?></h3>
                            <span class="returned-badge">✅ Retourné</span>
                        </div>
                        <div class="emprunt-info">
                            <p><strong>Auteur:</strong> <?php echo htmlspecialchars($emprunt['auteur']); ?></p>
                            <p><strong>Emprunteur:</strong> <?php echo htmlspecialchars($emprunt['emprunteur']); ?></p>
                            <p><strong>Date d'emprunt:</strong> <?php echo date('d/m/Y', strtotime($emprunt['date_emprunt'])); ?></p>
                            <p><strong>Date de retour:</strong> <?php echo date('d/m/Y', strtotime($emprunt['date_retour_effective'])); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>

