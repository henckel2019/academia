<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($title); ?> — <?php echo e(app_config('app_name')); ?></title>
    <link rel="stylesheet" href="<?= e(asset('/css/fonts.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('/css/tokens.css')) ?>">
    <link rel="stylesheet" href="<?php echo e(asset('/css/app.css')); ?>">
</head>
<body class="auth-page">
    <div class="auth-layout error-layout">
        <section class="auth-showcase" aria-hidden="true">
            <div class="auth-brand">
                <img src="<?php echo e(asset('/assets/logo-garagem-aco.png')); ?>" alt="" width="45" height="45">
                <span><strong>GARAGEM</strong><b>AÇO</b></span>
            </div>
            <div class="auth-showcase-copy">
                <small>Garagem Aço</small>
                <h2>Volte ao<br><span>ritmo.</span></h2>
            </div>
        </section>
        <main class="auth-card" role="alert">
            <span class="error-code">
                <?php echo icon('alert'); ?>
                <span>Não foi possível continuar</span>
            </span>
            <h1><?php echo e($title); ?></h1>
            <p class="subtitle"><?php echo e($message); ?></p>
            <?php if ($details !== null): ?>
                <pre><?php echo e($details); ?></pre>
            <?php endif; ?>
            <a class="btn btn-primary" href="<?php echo e(url('/')); ?>">
                <?php echo icon('home'); ?>
                <span>Voltar ao início</span>
            </a>
        </main>
    </div>
</body>
</html>
