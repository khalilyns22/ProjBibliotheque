<?php
require_once 'auth.php';
requireAdmin();
require_once 'config.php';

$message = '';
$messageType = '';
$book = null;

// Get book ID
$id = $_GET['id'] ?? null;

if (!$id) {
    header('Location: index.php');
    exit;
}

$conn = getConnection();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $titre = $_POST['titre'] ?? '';
    $auteur = $_POST['auteur'] ?? '';
    $isbn = $_POST['isbn'] ?? '';
    $annee = $_POST['annee_publication'] ?? null;
    $categorie = $_POST['categorie'] ?? '';
    $exemplaires = $_POST['nombre_exemplaires'] ?? 1;

    if (!empty($titre) && !empty($auteur)) {
        $stmt = $conn->prepare("UPDATE livres SET titre=?, auteur=?, isbn=?, annee_publication=?, categorie=?, nombre_exemplaires=? WHERE id=?");
        $stmt->bind_param("sssisii", $titre, $auteur, $isbn, $annee, $categorie, $exemplaires, $id);
        
        if ($stmt->execute()) {
            $message = "Livre modifié avec succès!";
            $messageType = "success";
        } else {
            $message = "Erreur lors de la modification du livre.";
            $messageType = "error";
        }
        
        $stmt->close();
    } else {
        $message = "Veuillez remplir tous les champs obligatoires.";
        $messageType = "error";
    }
}

// Get book data
$stmt = $conn->prepare("SELECT * FROM livres WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$book = $result->fetch_assoc();
$stmt->close();
$conn->close();

if (!$book) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier un livre - Gestion de Bibliothèque</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>✏️ Modifier un livre</h1>
            <a href="index.php" class="btn btn-secondary">← Retour à la liste</a>
        </header>

        <?php if ($message): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="form-container">
            <form method="POST" action="edit_book.php?id=<?php echo $id; ?>" class="book-form">
                <div class="form-group">
                    <label for="titre">Titre *</label>
                    <input type="text" id="titre" name="titre" required value="<?php echo htmlspecialchars($book['titre']); ?>">
                </div>

                <div class="form-group">
                    <label for="auteur">Auteur *</label>
                    <input type="text" id="auteur" name="auteur" required value="<?php echo htmlspecialchars($book['auteur']); ?>">
                </div>

                <div class="form-group">
                    <label for="isbn">ISBN</label>
                    <input type="text" id="isbn" name="isbn" value="<?php echo htmlspecialchars($book['isbn']); ?>">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="annee_publication">Année de publication</label>
                        <input type="number" id="annee_publication" name="annee_publication" min="1000" max="<?php echo date('Y'); ?>" value="<?php echo htmlspecialchars($book['annee_publication']); ?>">
                    </div>

                    <div class="form-group">
                        <label for="nombre_exemplaires">Nombre d'exemplaires</label>
                        <input type="number" id="nombre_exemplaires" name="nombre_exemplaires" min="1" value="<?php echo htmlspecialchars($book['nombre_exemplaires']); ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="categorie">Catégorie</label>
                    <input type="text" id="categorie" name="categorie" value="<?php echo htmlspecialchars($book['categorie']); ?>" placeholder="Ex: Fiction, Science-Fiction, etc.">
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
                    <a href="index.php" class="btn btn-secondary">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>

