<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
require_login();

// messaggi
$ok = null; $err = null;

// CREA
if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action'] ?? '')==='create') {
  check_csrf();
  $title = trim($_POST['title'] ?? '');
  $description = trim($_POST['description'] ?? '');
  $image_url = trim($_POST['image_url'] ?? '');
  $link_url  = trim($_POST['link_url'] ?? '');
  $category_id = isset($_POST['category_id']) && $_POST['category_id']!=='' ? (int)$_POST['category_id'] : null;
  $published = isset($_POST['published']) ? 1 : 0;

  // validazione server
  if (!validate_required($title, 2, 150)) $err = 'Titolo obbligatorio (2-150).';
  if (!$err && $image_url && !filter_var($image_url, FILTER_VALIDATE_URL)) $err = 'URL immagine non valida.';
  if (!$err && $link_url && !filter_var($link_url, FILTER_VALIDATE_URL)) $err = 'URL progetto non valida.';

  if (!$err) {
    $slug = slugify($title);
    // assicurati slug unico
    $i=0; $base=$slug;
    while (true) {
      $q=$pdo->prepare("SELECT 1 FROM works WHERE slug=?");
      $q->execute([$slug]);
      if (!$q->fetch()) break;
      $i++; $slug = $base.'-'.$i;
    }

    $stmt = $pdo->prepare("INSERT INTO works (title, slug, description, image_url, link_url, category_id, published) VALUES (?,?,?,?,?,?,?)");
    $stmt->execute([$title,$slug,$description,$image_url,$link_url,$category_id,$published]);
    $ok = 'Lavoro creato.';
  }
}

// DELETE (POST)
if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action'] ?? '')==='delete') {
  check_csrf();
  $id = (int)($_POST['id'] ?? 0);
  $pdo->prepare("DELETE FROM works WHERE id=?")->execute([$id]);
  $ok = 'Lavoro eliminato.';
}

// EDIT (GET form + POST save)
$edit = null;
if (isset($_GET['edit'])) {
  $sid = (int)$_GET['edit'];
  $st = $pdo->prepare("SELECT * FROM works WHERE id=?");
  $st->execute([$sid]);
  $edit = $st->fetch();
}

if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action'] ?? '')==='update') {
  check_csrf();
  $id = (int)($_POST['id'] ?? 0);
  $title = trim($_POST['title'] ?? '');
  $description = trim($_POST['description'] ?? '');
  $image_url = trim($_POST['image_url'] ?? '');
  $link_url  = trim($_POST['link_url'] ?? '');
  $category_id = isset($_POST['category_id']) && $_POST['category_id']!=='' ? (int)$_POST['category_id'] : null;
  $published = isset($_POST['published']) ? 1 : 0;

  if (!validate_required($title, 2, 150)) $err = 'Titolo obbligatorio (2-150).';
  if (!$err && $image_url && !filter_var($image_url, FILTER_VALIDATE_URL)) $err = 'URL immagine non valida.';
  if (!$err && $link_url && !filter_var($link_url, FILTER_VALIDATE_URL)) $err = 'URL progetto non valida.';

  if (!$err) {
    $slug = slugify($title);
    $i=0; $base=$slug;
    while (true) {
      $q=$pdo->prepare("SELECT 1 FROM works WHERE slug=? AND id<>?");
      $q->execute([$slug,$id]);
      if (!$q->fetch()) break;
      $i++; $slug = $base.'-'.$i;
    }
    $stmt = $pdo->prepare("UPDATE works SET title=?, slug=?, description=?, image_url=?, link_url=?, category_id=?, published=? WHERE id=?");
    $stmt->execute([$title,$slug,$description,$image_url,$link_url,$category_id,$published,$id]);
    $ok = 'Lavoro aggiornato.';
    $edit = null; // esci da modalità edit
  }
}

// elenco + categorie
$works = $pdo->query("SELECT w.*, c.name AS category FROM works w LEFT JOIN categories c ON c.id=w.category_id ORDER BY w.created_at DESC")->fetchAll();
$cats  = $pdo->query("SELECT id,name FROM categories ORDER BY name")->fetchAll();

include 'header.php';
?>

<section class="contact" style="padding-top:2.5rem;padding-bottom:2.5rem;">
  <div class="container">
    <h2>Gestisci lavori</h2>

    <?php if($ok): ?><p class="success-message"><?= e($ok) ?></p><?php endif; ?>
    <?php if($err): ?><p class="form-error-summary" role="alert"><?= e($err) ?></p><?php endif; ?>

    <!-- FORM: Create / Update -->
    <div class="portfolio-preview" style="background:#fff;border-radius:12px;padding:1.25rem;box-shadow:0 8px 20px rgba(0,0,0,.06);margin-bottom:1rem;">
      <form method="post" class="contact" style="background:transparent;padding:0;max-width:none">
        <?php csrf_field(); ?>
        <input type="hidden" name="action" value="<?= $edit ? 'update' : 'create' ?>">
        <?php if($edit): ?><input type="hidden" name="id" value="<?= (int)$edit['id'] ?>"><?php endif; ?>

        <input type="text" name="title" placeholder="Titolo *" required minlength="2" maxlength="150"
               value="<?= e($edit['title'] ?? '') ?>">

        <input type="url" name="image_url" placeholder="URL immagine (es. https://...)"
               value="<?= e($edit['image_url'] ?? '') ?>">

        <input type="url" name="link_url" placeholder="URL progetto (opzionale)"
               value="<?= e($edit['link_url'] ?? '') ?>">

        <select name="category_id">
          <option value="">Senza categoria</option>
          <?php foreach($cats as $c): ?>
            <option value="<?= (int)$c['id'] ?>" <?= (!empty($edit['category_id']) && $edit['category_id']==$c['id'])?'selected':'' ?>>
              <?= e($c['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>

        <textarea name="description" placeholder="Descrizione" rows="4"><?= e($edit['description'] ?? '') ?></textarea>

        <label style="text-align:left">
          <input type="checkbox" name="published" <?= (isset($edit['published']) ? ( $edit['published'] ? 'checked' : '') : 'checked') ?>>
          Pubblicato
        </label>

        <input type="submit" value="<?= $edit ? 'Aggiorna' : 'Crea' ?>">
        <?php if($edit): ?>
          <a href="works.php" class="cta-button" style="background:#555;color:#fff;text-decoration:none">Annulla</a>
        <?php endif; ?>
      </form>
    </div>

    <!-- TABELLA -->
    <div class="portfolio-preview" style="background:#fff;border-radius:12px;padding:1.25rem;box-shadow:0 8px 20px rgba(0,0,0,.06);">
      <div class="container" style="padding:0">
        <table class="table">
          <thead>
            <tr>
              <th>ID</th><th>Titolo</th><th>Categoria</th><th>Pubblicato</th><th>Azioni</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach($works as $w): ?>
            <tr>
              <td><?= (int)$w['id'] ?></td>
              <td><?= e($w['title']) ?></td>
              <td><?= e($w['category'] ?? '—') ?></td>
              <td><?= $w['published'] ? '✅' : '—' ?></td>
              <td>
                <a class="cta-button" style="text-decoration:none" href="works.php?edit=<?= (int)$w['id'] ?>">Modifica</a>
                <form method="post" style="display:inline" onsubmit="return confirm('Eliminare questo lavoro?');">
                  <?php csrf_field(); ?>
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="id" value="<?= (int)$w['id'] ?>">
                  <input type="submit" value="Elimina" style="background:#ff4f81;color:#fff">
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

  </div>
</section>

<?php include 'footer.php'; ?>
