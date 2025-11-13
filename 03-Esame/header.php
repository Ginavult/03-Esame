<!DOCTYPE html>
<html lang="it">
<head>
<meta charset="UTF-8">
  <title>GINA | Sito personale</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css?v=hamburgerfix1">
  <style>
    @media (min-width:769px){
      .menu-toggle { display: none !important; }
      #site-nav { display: block !important; }
    }
  </style>
</head>
<body>
  <header>
    <div class="container">
    <div class="logo">
  <a href="index.php" title="Vai alla home">
    <img src="img/image.png" alt="Logo Gina" height="60">
  </a>
</div>
<button class="menu-toggle"
              aria-controls="site-nav"
              aria-expanded="false"
              aria-label="Apri o chiudi il menu di navigazione"
              title="Apri/chiudi il menu">☰</button>
<nav id="site-nav">
        <ul>
          <li><a href="index.php" title="Vai alla home">Home</a></li>
          <li><a href="chi_sono.php" title="Scopri chi sono">Chi Sono</a></li>
          <li><a href="lavori.php" title="Visualizza i miei lavori">Lavori</a></li>
          <li><a href="contatti.php" title="Vai ai contatti">Contatti</a></li>
        </ul>
      </nav>
    </div>
  <script>
    (function menuToggleInit(){
      var btn = document.querySelector('.menu-toggle');
      var nav = document.getElementById('site-nav');
      if(!btn || !nav) return;
      btn.addEventListener('click', function(){
        var open = nav.classList.toggle('open');
        btn.setAttribute('aria-expanded', open ? 'true' : 'false');
      });
    })();
  </script>

</header>
