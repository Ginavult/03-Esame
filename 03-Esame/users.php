<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
require_login();

$ok=null; $err=null;

// CREATE
if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action']??'')==='create') {
  check_csrf();
  $name = trim($_POST['name'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';
  $role = ($_POST['role'] ?? 'admin') === 'editor' ? 'editor' : 'admin';

  if (!validate_required($name,2,100)) $err='Nome 2-100.';
  if (!$err && !validate_email($email)) $err='Email non valida.';
  if (!$err && !validate_required($password,6,200)) $err='Password min 6.';
  if (!$err) {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    try {
      $pdo->prepare("INSERT INTO users(name,email,password_hash,role) VALUES (?,?,?,?)")->execute([$name,$email,$hash,$role]);
      $ok='Utente creato.';
    } catch (PDOException $e) {
      $err='Email già usata?';
    }
  }
}

// DELETE
if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action']??'')==='delete') {
  check_csrf();
  $id=(int)($_POST['id']??0);
  if ($id === (int)($_SESSION['user_id'] ?? 0)) {
    $err = 'Non puoi eliminare te stessa.';
  } else {
    $pdo->prepare("DELETE FROM users WHERE id=?")->execute([$id]);
    $ok='Utente eliminato.';
  }
}

// EDIT prefill
$edit=null;
if (isset($_GET['edit'])) {
  $id=(int)$_GET['edit'];
  $st=$pdo->prepare("SELECT id,name,email,role FROM users WHERE id=?");
  $st->execute([$id]);
  $edit=$st->fetch();
}

// UPDATE
if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action']??'')==='update') {
  check_csrf();
  $id=(int)($_POST['id']??0);
  $name=trim($_POST['name']??'');
  $email=trim($_POST['email']??'');
  $role = ($_POST['role'] ?? 'admin') === 'editor' ? 'editor' : 'admin';
  $password = $_POST['password'] ?? '';

  if (!validate_required($name,2,100)) $err='Nome 2-100.';
  if (!$err && !validate_email($email)) $err='Email non valida.';

  if (!$err) {
    // se password compilata, aggiorna anche hash
    if ($password !== '') {
      if (!validate_required($password,6,200)) $err='Password min 6.';
      if (!$err) {
        $hash=password_hash($password,PASSWORD_DEFAULT);
        $pdo->prepare("UPDATE users SET name=?, email=?, role=?, password_hash=? WHERE id=?")->execute([$name,$email,$role,$hash,$id]);
      }
    } else {
      $pdo->prepare("UPDATE users SET name=?, email=?, role=? WHERE id=?")->execute([$name,$email,$role,$id]);
    }
    if (!$err) { $ok='Utente aggiornato.'; $edit=null; }
  }
}

$users=$pdo->query("SELECT id,name,email,role,created_at FROM users ORDER BY created_at DESC")->fetchAll();
include 'header.php';
?>
<section class="contact" style="padding-top:2.5rem;padding-bottom:2.5rem;">
  <div class="container">
    <h2>Utenti</h2>
    <?php if($ok): ?><p class="success-message"><?= e($ok) ?></p><?php endif; ?>
    <?php if($err): ?><p class="form-error-summary" role="alert"><?= e($err) ?></p><?php endif; ?>

    <div class="portfolio-preview" style="background:#fff;border-radius:12px;padding:1.25rem;box-shadow:0 8px 20px rgba(0,0,0,.06);margin-bottom:1rem;">
      <form method="post" class="contact" style="background:transparent;padding:0;max-width:none">
        <?php csrf_field(); ?>
        <input type="hidden" name="action" value="<?= $edit ? 'update' : 'create' ?>">
        <?php if($edit): ?><input type="hidden" name="id" value="<?= (int)$edit['id'] ?>"><?php endif; ?>

        <input type="text" name="name" placeholder="Nome *" required minlength="2" maxlength="100" value="<?= e($edit['name'] ?? '') ?>">
        <input type="email" name="email" placeholder="Email *" required value="<?= e($edit['email'] ?? '') ?>">
        <input type="password" name="password" placeholder="<?= $edit ? 'Nuova password (opzionale)' : 'Password *' ?>" <?= $edit ? '' : 'required minlength="6"' ?>>

        <select name="role">
          <option value="admin"  <?= (($edit['role'] ?? '')==='admin')?'selected':'' ?>>Admin</option>
          <option value="editor" <?= (($edit['role'] ?? '')==='editor')?'selected':'' ?>>Editor</option>
        </select>

        <input type="submit" value="<?= $edit ? 'Aggiorna' : 'Crea' ?>">
        <?php if($edit): ?><a class="cta-button" style="background:#555;color:#fff;text-decoration:none" href="users.php">Annulla</a><?php endif; ?>
      </form>
    </div>

    <div class="portfolio-preview" style="background:#fff;border-radius:12px;padding:1.25rem;box-shadow:0 8px 20px rgba(0,0,0,.06);">
      <div class="container" style="padding:0">
        <table class="table">
          <thead><tr><th>ID</th><th>Nome</th><th>Email</th><th>Ruolo</th><th>Creato</th><th>Azioni</th></tr></thead>
          <tbody>
            <?php foreach($users as $u): ?>
              <tr>
                <td><?= (int)$u['id'] ?></td>
                <td><?= e($u['name']) ?></td>
                <td><?= e($u['email']) ?></td>
                <td><?= e($u['role']) ?></td>
                <td><?= e($u['created_at']) ?></td>
                <td>
                  <a class="cta-button" style="text-decoration:none" href="users.php?edit=<?= (int)$u['id'] ?>">Modifica</a>
                  <?php if((int)$u['id'] !== (int)($_SESSION['user_id'] ?? 0)): ?>
                    <form method="post" style="display:inline" onsubmit="return confirm('Eliminare questo utente?');">
                      <?php csrf_field(); ?>
                      <input type="hidden" name="action" value="delete">
                      <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
                      <input type="submit" value="Elimina" style="background:#ff4f81;color:#fff">
                    </form>
                  <?php endif; ?>
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
