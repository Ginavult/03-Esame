<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
require_login();

$users = (int)$pdo->query("SELECT COUNT(*) AS c FROM users")->fetch()['c'];
$cats  = (int)$pdo->query("SELECT COUNT(*) AS c FROM categories")->fetch()['c'];
$works = (int)$pdo->query("SELECT COUNT(*) AS c FROM works")->fetch()['c'];

include 'header.php';
?>
<section class="contact" style="padding-top:2.5rem;padding-bottom:2.5rem;">
  <div class="container">
    <h2>Dashboard</h2>
    <p class="success-message" style="margin-bottom:1.5rem;">Ciao, <?= e($_SESSION['user_email'] ?? '') ?></p>

    <div class="portfolio-preview" style="background:#fff;border-radius:12px;padding:1.25rem;box-shadow:0 8px 20px rgba(0,0,0,.06);">
      <div class="container" style="padding:0">
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:14px;">
          <div class="card-image" style="background:#f9f9fa;border-radius:10px;padding:14px;">
            <strong>Utenti</strong><div style="font-size:28px;font-weight:800;"><?= $users ?></div>
          </div>
          <div class="card-image" style="background:#f9f9fa;border-radius:10px;padding:14px;">
            <strong>Categorie</strong><div style="font-size:28px;font-weight:800;"><?= $cats ?></div>
          </div>
          <div class="card-image" style="background:#f9f9fa;border-radius:10px;padding:14px;">
            <strong>Lavori</strong><div style="font-size:28px;font-weight:800;"><?= $works ?></div>
          </div>
        </div> <div style="display:flex;gap:.75rem;flex-wrap:wrap;margin-top:1rem;">
          <a href="works.php" class="cta-button" style="display:inline-block;text-decoration:none;">Gestisci lavori</a>
          <a href="categories.php" class="cta-button" style="display:inline-block;text-decoration:none;background:#222;color:#fff;">Gestisci categorie</a>
          <a href="users.php" class="cta-button" style="display:inline-block;text-decoration:none;background:#555;color:#fff;">Gestisci utenti</a>
          <a href="logout.php" class="cta-button" style="display:inline-block;text-decoration:none;background:#ff4f81;color:#fff;">Logout</a>
        </div>
      </div>
    </div>
  </div>
</section>

<?php include 'footer.php'; ?>