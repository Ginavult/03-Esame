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

  // validazione server (BASE)
  if (!validate_required($title, 2, 150)) {
    $err = 'Titolo obbligatorio (2-150).';
  }
  if (!$err && $image_url && !filter_var($image_url, FILTER_VALIDATE_URL)) {
    $err = 'URL immagine non valida.';
  }
  if (!$err && $link_url && !filter_var($link_url, FILTER_VALIDATE_URL)) {
    $err = 'URL progetto non valida.';
  }

  // di base usiamo l'URL se compilato
  $imagePath = $image_url;

  // --- UPLOAD IMMAGINE (se è stato scelto un file) --- //
  if (!$err && !empty($_FILES['image']['name'])) {
    $upload_dir = __DIR__ . '/uploads/';
    if (!is_dir($upload_dir)) {
      mkdir($upload_dir, 0777, true);
    }

    $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg','jpeg','png','gif','webp'];

    if (!in_array($ext, $allowed)) {
      $err = 'Formato immagine non valido. Usa jpg, jpeg, png, gif o webp.';
    } else {
      $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/','', $_FILES['image']['name']);
      $target = $upload_dir . $filename;

      if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
        // percorso RELATIVO che salviamo nel DB
        $imagePath = 'uploads/' . $filename;
      } else {
        $err = 'Errore nel caricamento dell\'immagine.';
      }
    }
  }

  if (!$err) {
    // slug unico
    $slug = slugify($title);
    $i=0; $base=$slug;
    while (true) {
      $q=$pdo->prepare("SELECT 1 FROM works WHERE slug=?");
      $q->execute([$slug]);
      if (!$q->fetch()) break;
      $i++; $slug = $base.'-'.$i;
    }

    $stmt = $pdo->prepare("INSERT INTO works (title, slug, description, image_url, link_url, category_id, published) VALUES (?,?,?,?,?,?,?)");
    $stmt->execute([$title,$slug,$description,$imagePath,$link_url,$category_id,$published]);
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

// UPDATE
if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action'] ?? '')==='update') {
  check_csrf();
  $id = (int)($_POST['id'] ?? 0);
  $title = trim($_POST['title'] ?? '');
  $description = trim($_POST['description'] ?? '');
  $image_url = trim($_POST['image_url'] ?? '');
  $link_url  = trim($_POST['link_url'] ?? '');
  $category_id = isset($_POST['category_id']) && $_POST['category_id']!=='' ? (int)$_POST['category_id'] : null;
  $published = isset($_POST['published']) ? 1 : 0;

  // validazione server
  if (!validate_required($title, 2, 150)) {
    $err = 'Titolo obbligatorio (2-150).';
  }
  if (!$err && $image_url && !filter_var($image_url, FILTER_VALIDATE_URL)) {
    $err = 'URL immagine non valida.';
  }
  if (!$err && $link_url && !filter_var($link_url, FILTER_VALIDATE_URL)) {
    $err = 'URL progetto non valida.';
  }

  // partiamo dall'URL esistente (che può contenere già "uploads/...")
  $imagePath = $image_url;

  // --- UPLOAD IMMAGINE (se l'utente ha scelto un nuovo file) --- //
  if (!$err && !empty($_FILES['image']['name'])) {
    $upload_dir = __DIR__ . '/uploads/';
    if (!is_dir($upload_dir)) {
      mkdir($upload_dir, 0777, true);
    }

    $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg','jpeg','png','gif','webp'];

    if (!in_array($ext, $allowed)) {
      $err = 'Formato immagine non valido. Usa jpg, jpeg, png, gif o webp.';
    } else {
      $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/','', $_FILES['image']['name']);
      $target = $upload_dir . $filename;

      if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
        $imagePath = 'uploads/' . $filename;
      } else {
        $err = 'Errore nel caricamento dell\'immagine.';
      }
    }
  }

  if (!$err) {
    // slug unico: escludiamo il record stesso
    $slug = slugify($title);
    $i=0; $base=$slug;
    while (true) {
      $q=$pdo->prepare("SELECT 1 FROM works WHERE slug=? AND id<>?");
      $q->execute([$slug,$id]);
      if (!$q->fetch()) break;
      $i++; $slug = $base.'-'.$i;
    }

    $stmt = $pdo->prepare("UPDATE works SET title=?, slug=?, description=?, image_url=?, link_url=?, category_id=?, published=? WHERE id=?");
    $stmt->execute([$title,$slug,$description,$imagePath,$link_url,$category_id,$published,$id]);
    $ok = 'Lavoro aggiornato.';
    $edit = null;
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
    <form method="post" enctype="multipart/form-data" class="contact" style="background:transparent;padding:0;max-width:none">
    <?php csrf_field(); ?>
        <input type="hidden" name="action" value="<?= $edit ? 'update' : 'create' ?>">
        <?php if($edit): ?><input type="hidden" name="id" value="<?= (int)$edit['id'] ?>"><?php endif; ?>

        <input type="text" name="title" placeholder="Titolo *" required minlength="2" maxlength="150"
               value="<?= e($edit['title'] ?? '') ?>">

        <!-- URL immagine -->
<label style="text-align:left; font-size:.9rem; color:#555;">URL immagine</label>
<input type="url" name="image_url" id="image_url"
       placeholder="https://..."
       value="<?= e($edit['image_url'] ?? '') ?>">

<!-- Oppure upload -->
<label style="text-align:left; font-size:.9rem; color:#555; margin-top:10px;">Oppure carica un'immagine</label>
<input type="file" name="image" id="image_file">

<small id="image_hint" class="meta">
  Puoi inserire un URL oppure caricare un file immagine (scegline uno).
</small>
        <input type="url" name="link_url" placeholder="URL progetto (opzionale)"
               value="<?= e($edit['link_url'] ?? '') ?>">

               <select name="category_id" class="backend-select">
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
<!-- TABELLA GRANDI LAVORI -->
<div class="portfolio-preview" style="background:#fff;border-radius:12px;padding:1.25rem;box-shadow:0 8px 20px rgba(0,0,0,.06);">
  <div style="padding:0">
    <table class="admin-table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Titolo</th>
          <th>Categoria</th>
          <th>Pubblicato</th>
          <th>Azioni</th>
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
<script>
document.addEventListener('DOMContentLoaded', function() {
  const urlInput  = document.getElementById('image_url');
  const fileInput = document.getElementById('image_file');
  const hint      = document.getElementById('image_hint');

  if (!urlInput || !fileInput || !hint) return;

  function updateState() {
    const hasUrl  = urlInput.value.trim() !== '';
    const hasFile = fileInput.value !== '';

    if (hasUrl && !hasFile) {
      fileInput.disabled = true;
      hint.textContent = 'Hai scelto un URL. Per usare un file, svuota l’URL.';
    } else if (!hasUrl && hasFile) {
      urlInput.disabled = true;
      hint.textContent = 'Hai scelto un file. Per usare un URL, rimuovi il file.';
    } else if (!hasUrl && !hasFile) {
      urlInput.disabled = false;
      fileInput.disabled = false;
      hint.textContent = 'Puoi scegliere URL o file (uno solo).';
    } else {
      fileInput.disabled = true;
      hint.textContent = 'Stai usando l’URL. Per usare un file, svuota prima l’URL.';
    }
  }

  urlInput.addEventListener('input', updateState);
  fileInput.addEventListener('change', updateState);

  updateState();
});
</script>

<?php include 'footer.php'; ?>
