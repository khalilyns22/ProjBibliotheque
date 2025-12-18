<?php
require_once 'auth.php';
requireLogin();
require_once 'config.php';

// Handle search
$search = isset($_GET['search']) ? $_GET['search'] : '';
$conn = getConnection();

// Get all books or search results
if (!empty($search)) {
    $stmt = $conn->prepare("SELECT * FROM livres WHERE titre LIKE ? OR auteur LIKE ? OR categorie LIKE ? ORDER BY date_ajout DESC");
    $searchTerm = "%$search%";
    $stmt->bind_param("sss", $searchTerm, $searchTerm, $searchTerm);
} else {
    $stmt = $conn->prepare("SELECT * FROM livres ORDER BY date_ajout DESC");
}

$stmt->execute();
$result = $stmt->get_result();
$books = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get borrowing counts for each book
$bookIds = array_column($books, 'id');
if (!empty($bookIds)) {
    $placeholders = str_repeat('?,', count($bookIds) - 1) . '?';
    $stmt = $conn->prepare("SELECT livre_id, COUNT(*) as count FROM emprunts WHERE livre_id IN ($placeholders) AND statut = 'emprunté' GROUP BY livre_id");
    $stmt->bind_param(str_repeat('i', count($bookIds)), ...$bookIds);
    $stmt->execute();
    $result = $stmt->get_result();
    $borrowedCounts = [];
    while ($row = $result->fetch_assoc()) {
        $borrowedCounts[$row['livre_id']] = $row['count'];
    }
    $stmt->close();
    
    // Add available count to each book
    foreach ($books as &$book) {
        $borrowed = $borrowedCounts[$book['id']] ?? 0;
        $book['disponibles'] = $book['nombre_exemplaires'] - $borrowed;
        $book['empruntes'] = $borrowed;
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion de Bibliothèque</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <div class="user-info">
                <span>👤 <?php echo htmlspecialchars($_SESSION['username']); ?> (<?php echo $_SESSION['role'] === 'admin' ? 'Admin' : 'Utilisateur'; ?>)</span>
                <a href="logout.php" class="btn btn-logout">🚪 Déconnexion</a>
            </div>
            <h1>📚 Gestion de Bibliothèque</h1>
            <p class="subtitle">Système de gestion de livres</p>
        </header>

        <div class="actions-bar">
            <div class="actions-left">
                <?php if (isAdmin()): ?>
                    <a href="add_book.php" class="btn btn-primary">+ Ajouter un livre</a>
                <?php endif; ?>
                <a href="liste_emprunts.php" class="btn btn-info">📋 Liste des emprunts</a>
            </div>
            <form method="GET" action="index.php" class="search-form">
                <input type="text" name="search" placeholder="Rechercher un livre..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="btn btn-search">🔍 Rechercher</button>
                <?php if (!empty($search)): ?>
                    <a href="index.php" class="btn btn-secondary">Effacer</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="books-grid">
            <?php if (empty($books)): ?>
                <div class="no-books">
                    <p>📖 Aucun livre trouvé</p>
                    <?php if (isAdmin()): ?>
                        <a href="add_book.php" class="btn btn-primary">Ajouter le premier livre</a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <?php foreach ($books as $book): ?>
                    <div class="book-card">
                        <div class="book-header">
                            <h3><?php echo htmlspecialchars($book['titre']); ?></h3>
                            <span class="category-badge"><?php echo htmlspecialchars($book['categorie']); ?></span>
                        </div>
                        <div class="book-info">
                            <p><strong>Auteur:</strong> <?php echo htmlspecialchars($book['auteur']); ?></p>
                            <p><strong>ISBN:</strong> <?php echo htmlspecialchars($book['isbn'] ?: 'N/A'); ?></p>
                            <p><strong>Année:</strong> <?php echo htmlspecialchars($book['annee_publication'] ?: 'N/A'); ?></p>
                            <p><strong>Exemplaires:</strong> 
                                <span class="availability <?php echo $book['disponibles'] > 0 ? 'available' : 'unavailable'; ?>">
                                    <?php echo $book['disponibles']; ?> disponible(s) / <?php echo htmlspecialchars($book['nombre_exemplaires']); ?> total
                                </span>
                            </p>
                        </div>
                        <div class="book-actions">
                            <?php if ($book['disponibles'] > 0): ?>
                                <a href="emprunter.php?id=<?php echo $book['id']; ?>" class="btn btn-borrow">📖 Emprunter</a>
                            <?php else: ?>
                                <span class="btn btn-disabled">❌ Indisponible</span>
                            <?php endif; ?>
                            <?php if (isAdmin()): ?>
                                <a href="edit_book.php?id=<?php echo $book['id']; ?>" class="btn btn-edit">✏️ Modifier</a>
                                <a href="delete_book.php?id=<?php echo $book['id']; ?>" class="btn btn-delete" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce livre?');">🗑️ Supprimer</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <?php if (!empty($books)): ?>
            <div class="stats">
                <p>Total de livres: <strong><?php echo count($books); ?></strong></p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>

