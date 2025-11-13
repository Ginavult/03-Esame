<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

$err = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  check_csrf();
  $email = trim($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';

  if (!validate_email($email) || !validate_required($password,6,200)) {
    $err = 'Credenziali non valide';
  } else {
    $stmt = $pdo->prepare("SELECT id, email, password_hash FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
      $_SESSION['user_id'] = $user['id'];
      $_SESSION['user_email'] = $user['email'];
      header('Location: /03-Esame/dashboard.php');
      exit;
    } else {
      $err = 'Email o password errate';
    }
  }
}
?>
<?php include 'header.php'; ?>
<section class="contact">
  <div class="container">
    <h2>Accedi</h2>

    <?php if($err): ?>
      <p class="form-error-summary" role="alert">Password e/o Username sbagliati.</p>
    <?php endif; ?>

    <form method="post" novalidate>
      <?php csrf_field(); ?>

      <input type="email" name="email" placeholder="Email"
        value=""
        <?php echo $err ? 'class="invalid" aria-invalid="true"' : ''; ?>
        required>

      <input type="password" name="password" placeholder="Password"
        minlength="6"
        <?php echo $err ? 'class="invalid" aria-invalid="true"' : ''; ?>
        required>

      <?php if($err): ?>
        <small class="error-text" role="alert"><?= e($err) ?></small>
      <?php endif; ?>

      <input type="submit" value="Entra" title="Accedi all'area riservata">
    </form>
  </div>
</section>

<?php include 'footer.php'; ?>