<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex,nofollow">
  <title><?= atenea_e((string) $status) ?> · <?= atenea_e($datos['title']) ?> | Atenea</title>
  <link rel="stylesheet" href="<?= atenea_url('src/shared/assets/css/error-pages.css') ?>">
</head>
<body>
  <main class="error-shell" aria-labelledby="error-title">
    <section class="error-card">
      <a class="error-brand" href="<?= atenea_e($inicio) ?>" aria-label="Ir al inicio de Atenea">
        <img src="<?= atenea_e($logo) ?>" alt="Atenea Escuela de Naturopatía Holística">
      </a>
      <div class="error-icon" aria-hidden="true"><?= $status === 404 ? '?' : '!' ?></div>
      <p class="error-code">Error <?= atenea_e((string) $status) ?></p>
      <h1 id="error-title"><?= atenea_e($datos['title']) ?></h1>
      <p class="error-message"><?= atenea_e($datos['message']) ?></p>
      <p class="error-tracking">Código de seguimiento: <strong><?= atenea_e($trackingId) ?></strong></p>
      <div class="error-actions">
        <a class="button button-primary" href="<?= atenea_e($inicio) ?>">Volver al inicio</a>
        <button class="button button-secondary" type="button" onclick="if(history.length>1){history.back()}else{location.href='<?=atenea_e($inicio)?>'}">Regresar</button>
        <?php if ($panel !== null): ?><a class="button button-secondary" href="<?= atenea_e($panel) ?>">Ir a mi panel</a><?php endif; ?>
      </div>
    </section>
  </main>
</body>
</html>
