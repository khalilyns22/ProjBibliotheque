<?php
require_once 'auth.php';
requireLogin();
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

// Get book data
$stmt = $conn->prepare("SELECT * FROM livres WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$book = $result->fetch_assoc();
$stmt->close();

if (!$book) {
    header('Location: index.php');
    exit;
}

// Count current borrowings for this book
$stmt = $conn->prepare("SELECT COUNT(*) as empruntes FROM emprunts WHERE livre_id = ? AND statut = 'emprunté'");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$borrowed = $result->fetch_assoc();
$available = $book['nombre_exemplaires'] - $borrowed['empruntes'];
$stmt->close();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $emprunteur = trim($_POST['emprunteur'] ?? '');
    $date_retour_prevue = $_POST['date_retour_prevue'] ?? '';

    if (!empty($emprunteur) && !empty($date_retour_prevue)) {
        if ($available > 0) {
            $date_emprunt = date('Y-m-d');
            $stmt = $conn->prepare("INSERT INTO emprunts (livre_id, emprunteur, date_emprunt, date_retour_prevue, statut) VALUES (?, ?, ?, ?, 'emprunté')");
            $stmt->bind_param("isss", $id, $emprunteur, $date_emprunt, $date_retour_prevue);
            
            if ($stmt->execute()) {
                $message = "Livre emprunté avec succès!";
                $messageType = "success";
                // Refresh available count
                $stmt2 = $conn->prepare("SELECT COUNT(*) as empruntes FROM emprunts WHERE livre_id = ? AND statut = 'emprunté'");
                $stmt2->bind_param("i", $id);
                $stmt2->execute();
                $result2 = $stmt2->get_result();
                $borrowed = $result2->fetch_assoc();
                $available = $book['nombre_exemplaires'] - $borrowed['empruntes'];
                $stmt2->close();
            } else {
                $message = "Erreur lors de l'emprunt du livre.";
                $messageType = "error";
            }
            
            $stmt->close();
        } else {
            $message = "Aucun exemplaire disponible pour l'emprunt.";
            $messageType = "error";
        }
    } else {
        $message = "Veuillez remplir tous les champs.";
        $messageType = "error";
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Emprunter un livre - Gestion de Bibliothèque</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>📖 Emprunter un livre</h1>
            <a href="index.php" class="btn btn-secondary">← Retour à la liste</a>
        </header>

        <div class="book-info-box">
            <h3><?php echo htmlspecialchars($book['titre']); ?></h3>
            <p><strong>Auteur:</strong> <?php echo htmlspecialchars($book['auteur']); ?></p>
            <p><strong>Exemplaires disponibles:</strong> <span class="available-count"><?php echo $available; ?></span> / <?php echo htmlspecialchars($book['nombre_exemplaires']); ?></p>
        </div>

        <?php if ($message): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if ($available > 0): ?>
            <div class="form-container">
                <form method="POST" action="emprunter.php?id=<?php echo $id; ?>" class="book-form">
                    <div class="form-group">
                        <label for="emprunteur">Nom de l'emprunteur *</label>
                        <input type="text" id="emprunteur" name="emprunteur" required placeholder="Ex: Jean Dupont" value="<?php echo htmlspecialchars($_POST['emprunteur'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="date_retour_prevue">Date de retour prévue *</label>
                        <input type="date" id="date_retour_prevue" name="date_retour_prevue" required min="<?php echo date('Y-m-d'); ?>" value="<?php echo htmlspecialchars($_POST['date_retour_prevue'] ?? ''); ?>">
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Emprunter le livre</button>
                        <a href="index.php" class="btn btn-secondary">Annuler</a>
                    </div>
                </form>
            </div>
        <?php else: ?>
            <div class="message error">
                <p>❌ Tous les exemplaires de ce livre sont actuellement empruntés.</p>
                <p><a href="liste_emprunts.php" class="btn btn-secondary" style="margin-top: 10px;">Voir les emprunts</a></p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>

