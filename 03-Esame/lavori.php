<?php
require_once __DIR__ . '/config/db.php';
$jobs = $pdo->query("
  SELECT id,
         title       AS titolo,
         description AS descrizione,
         image_url   AS img
  FROM works
  WHERE published = 1
  ORDER BY created_at DESC
")->fetchAll();
include("header.php");
?>
<section class="all-works">
  <div class="container">
    <h2>I miei lavori</h2>
    <div class="projects-grid">
      <?php foreach ($jobs as $job): ?>
        <a href="lavoro.php?id=<?= $job['id'] ?>" class="project-card">
          <div class="card-image">
            <img src="<?= $job['img'] ?>" alt="<?= htmlspecialchars($job['titolo']) ?>">
          </div>
          <div class="card-content">
            <h3><?= htmlspecialchars($job['titolo']) ?></h3>
            <p><?= htmlspecialchars($job['descrizione']) ?></p>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php include("footer.php"); ?>
