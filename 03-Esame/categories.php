<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
require_login();

$ok=null; $err=null;

// CREATE
if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action']??'')==='create') {
  check_csrf();
  $name = trim($_POST['name'] ?? '');
  if (!validate_required($name,2,100)) $err='Nome categoria 2-100 caratteri.';
  if (!$err) {
    $slug = slugify($name);
    $i=0; $base=$slug;
    while (true) { $q=$pdo->prepare("SELECT 1 FROM categories WHERE slug=?"); $q->execute([$slug]); if(!$q->fetch()) break; $i++; $slug=$base.'-'.$i; }
    $pdo->prepare("INSERT INTO categories(name,slug) VALUES(?,?)")->execute([$name,$slug]);
    $ok='Categoria creata.';
  }
}

// DELETE
if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action']??'')==='delete') {
  check_csrf();
  $id=(int)($_POST['id']??0);
  $pdo->prepare("DELETE FROM categories WHERE id=?")->execute([$id]);
  $ok='Categoria eliminata.';
}

// EDIT: prefill
$edit=null;
if (isset($_GET['edit'])) {
  $id=(int)$_GET['edit'];
  $st=$pdo->prepare("SELECT * FROM categories WHERE id=?");
  $st->execute([$id]);
  $edit=$st->fetch();
}

// UPDATE
if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action']??'')==='update') {
  check_csrf();
  $id=(int)($_POST['id']??0);
  $name=trim($_POST['name']??'');
  if (!validate_required($name,2,100)) $err='Nome categoria 2-100 caratteri.';
  if (!$err) {
    $slug=slugify($name);
    $i=0; $base=$slug;
    while (true) { $q=$pdo->prepare("SELECT 1 FROM categories WHERE slug=? AND id<>?"); $q->execute([$slug,$id]); if(!$q->fetch()) break; $i++; $slug=$base.'-'.$i; }
    $pdo->prepare("UPDATE categories SET name=?, slug=? WHERE id=?")->execute([$name,$slug,$id]);
    $ok='Categoria aggiornata.'; $edit=null;
  }
}

$cats=$pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
include 'header.php';
?>
<section class="contact" style="padding-top:2.5rem;padding-bottom:2.5rem;">
  <div class="container">
    <h2>Categorie</h2>
    <?php if($ok): ?><p class="success-message"><?= e($ok) ?></p><?php endif; ?>
    <?php if($err): ?><p class="form-error-summary" role="alert"><?= e($err) ?></p><?php endif; ?>

    <div class="portfolio-preview" style="background:#fff;border-radius:12px;padding:1.25rem;box-shadow:0 8px 20px rgba(0,0,0,.06);margin-bottom:1rem;">
      <form method="post" class="contact" style="background:transparent;padding:0;max-width:none">
        <?php csrf_field(); ?>
        <input type="hidden" name="action" value="<?= $edit ? 'update' : 'create' ?>">
        <?php if($edit): ?><input type="hidden" name="id" value="<?= (int)$edit['id'] ?>"><?php endif; ?>
        <input type="text" name="name" placeholder="Nome categoria *" required minlength="2" maxlength="100" value="<?= e($edit['name'] ?? '') ?>">
        <input type="submit" value="<?= $edit ? 'Aggiorna' : 'Crea' ?>">
        <?php if($edit): ?><a class="cta-button" style="background:#555;color:#fff;text-decoration:none" href="categories.php">Annulla</a><?php endif; ?>
      </form>
    </div>

    <div class="portfolio-preview" style="background:#fff;border-radius:12px;padding:1.25rem;box-shadow:0 8px 20px rgba(0,0,0,.06);">
      <div class="container" style="padding:0">
        <table class="table">
          <thead><tr><th>ID</th><th>Nome</th><th>Slug</th><th>Azioni</th></tr></thead>
          <tbody>
            <?php foreach($cats as $c): ?>
              <tr>
                <td><?= (int)$c['id'] ?></td>
                <td><?= e($c['name']) ?></td>
                <td><?= e($c['slug']) ?></td>
                <td>
                  <a class="cta-button" style="text-decoration:none" href="categories.php?edit=<?= (int)$c['id'] ?>">Modifica</a>
                  <form method="post" style="display:inline" onsubmit="return confirm('Eliminare questa categoria?');">
                    <?php csrf_field(); ?>
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
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
