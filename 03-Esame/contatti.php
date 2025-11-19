<?php
  // Stato invio
  $success = false;
  $errors = ["nome" => "", "email" => "", "messaggio" => ""];
  $values = ["nome" => "", "email" => "", "messaggio" => ""];

  if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Sanitize + trim
    $values["nome"] = isset($_POST["nome"]) ? trim($_POST["nome"]) : "";
    $values["email"] = isset($_POST["email"]) ? trim($_POST["email"]) : "";
    $values["messaggio"] = isset($_POST["messaggio"]) ? trim($_POST["messaggio"]) : "";

    // Validazione server-side (senza cambiare il layout)
    if ($values["nome"] === "") {
      $errors["nome"] = "Il nome è obbligatorio.";
    } elseif (mb_strlen($values["nome"]) < 2) {
      $errors["nome"] = "Il nome deve avere almeno 2 caratteri.";
    }

    if ($values["email"] === "") {
      $errors["email"] = "L'email è obbligatoria.";
    } elseif (!filter_var($values["email"], FILTER_VALIDATE_EMAIL)) {
      $errors["email"] = "Inserisci un'email valida.";
    }

    if ($values["messaggio"] === "") {
      $errors["messaggio"] = "Il messaggio è obbligatorio.";
    } elseif (mb_strlen($values["messaggio"]) < 10) {
      $errors["messaggio"] = "Almeno 10 caratteri.";
    }

    $hasErrors = implode("", $errors) !== "";
    if (!$hasErrors) {
      if (!is_dir("data")) { mkdir("data"); }
      $riga = $values["nome"] . " | " . $values["email"] . " | " . $values["messaggio"] . PHP_EOL;
      file_put_contents("data/contatti.txt", $riga, FILE_APPEND);
      $success = true;
      $values = ["nome" => "", "email" => "", "messaggio" => ""];
    }
  }

  include("header.php");
?>


<section class="contact">
  <div class="container">
    <h2>Contattami</h2>

    <?php if ($success): ?>
      <p class="success-message">Messaggio inviato con successo! ✅</p>
    <?php endif; ?>

    <?php if (!$success && ($_SERVER["REQUEST_METHOD"] === "POST")): ?>
      <p class="form-error-summary" role="alert">Per favore correggi i campi evidenziati.</p>
    <?php endif; ?>

    <form method="POST" action="contatti.php" novalidate>
      <input type="text" name="nome" placeholder="Il tuo nome"
        value="<?php echo htmlspecialchars($values['nome'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
        <?php echo $errors['nome'] ? 'class="invalid" aria-invalid="true"' : ''; ?>
        required>

      <?php if ($errors['nome']): ?>
        <small class="error-text" role="alert"><?php echo htmlspecialchars($errors['nome'], ENT_QUOTES, 'UTF-8'); ?></small>
      <?php endif; ?>

      <input type="email" name="email" placeholder="La tua email"
        value="<?php echo htmlspecialchars($values['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
        <?php echo $errors['email'] ? 'class="invalid" aria-invalid="true"' : ''; ?>
        required>

      <?php if ($errors['email']): ?>
        <small class="error-text" role="alert"><?php echo htmlspecialchars($errors['email'], ENT_QUOTES, 'UTF-8'); ?></small>
      <?php endif; ?>

      <textarea name="messaggio" placeholder="Scrivi il tuo messaggio..." required
        <?php echo $errors['messaggio'] ? 'class="invalid" aria-invalid="true"' : ''; ?>
      ><?php echo htmlspecialchars($values['messaggio'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>

      <?php if ($errors['messaggio']): ?>
        <small class="error-text" role="alert"><?php echo htmlspecialchars($errors['messaggio'], ENT_QUOTES, 'UTF-8'); ?></small>
      <?php endif; ?>

      <input type="submit" value="Invia messaggio" title="Invia il messaggio e rimani su questa pagina">
    </form>
  </div>
</section>

<?php include("footer.php"); ?>
