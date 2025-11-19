<?php
require_once __DIR__ . '/config/db.php';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare("
  SELECT id,
         title       AS titolo,
         description AS descrizione,
         image_url   AS img
  FROM works
  WHERE id = ? AND published = 1
  LIMIT 1
");
$stmt->execute([$id]);
$job = $stmt->fetch();

include("header.php");
?>

<?php if ($job): ?>

<section class="job-detail">
  <div class="container">
    <h2><?= htmlspecialchars($job['titolo']) ?></h2>
    <img src="<?= $job['img'] ?>" alt="<?= htmlspecialchars($job['titolo']) ?>">
    <p><?= htmlspecialchars($job['descrizione']) ?></p>
    <a href="lavori.php" class="back-button" title="Torna all\'elenco dei lavori">← Torna ai lavori</a>
  </div>
</section>

<?php else: ?>

<section class="job-detail">
  <div class="container">
    <h2>Progetto non trovato</h2>
    <a href="lavori.php" class="back-button" title="Torna all\'elenco dei lavori">← Torna ai lavori</a>
  </div>
</section>

<?php endif; ?>

<?php include("footer.php"); ?>
