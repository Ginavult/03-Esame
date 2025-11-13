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
<section class="hero">
  <div class="overlay">
    <div class="container">
    <h1><span>Siti web moderni</span><br>per professionisti e brand</h1>
    <p>Strategia, estetica e codice per il tuo successo online.</p>
      <a href="lavori.php" class="cta-button" title="Visualizza i miei lavori">Scopri di più</a>
    </div>
  </div>
</section>
<div class="wave-separator">
  <svg viewBox="0 0 1440 320" preserveAspectRatio="none">
    <path fill="#f9f9fa" fill-opacity="1" d="M0,192L60,197.3C120,203,240,213,360,197.3C480,181,600,139,720,144C840,149,960,203,1080,208C1200,213,1320,171,1380,149.3L1440,128L1440,320L1380,320C1320,320,1200,320,1080,320C960,320,840,320,720,320C600,320,480,320,360,320C240,320,120,320,60,320L0,320Z"></path>
  </svg>
</div>
</section>
<section class="portfolio-preview">
  <div class="container">
    <h2>Ultimi Lavori</h2>
    <div class="projects-grid">
      <?php foreach (array_slice($jobs, 0, 3) as $job): ?>
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
