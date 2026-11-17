<?php

declare(strict_types=1);

use Academia\Core\Csrf;

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#090a0c">
    <title>Entrar — <?php echo e(app_config('app_name')); ?></title>
    <link rel="icon" href="<?= e(asset('/assets/logo-garagem-aco.png')) ?>">
    <link rel="stylesheet" href="<?= e(asset('/css/fonts.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('/css/tokens.css')) ?>">
    <link rel="stylesheet" href="<?php echo e(asset('/css/app.css')); ?>">
</head>
<body class="auth-page">
    <a class="skip-link" href="#login-content">Pular para o acesso</a>
    <div class="auth-layout">
        <section class="auth-showcase" aria-label="Garagem Aço">
            <a class="auth-brand" href="<?php echo e(url('/')); ?>" aria-label="Voltar para o site">
                <img src="<?php echo e(asset('/assets/logo-garagem-aco.png')); ?>" alt="" width="45" height="45">
                <span><strong>GARAGEM</strong><b>AÇO</b></span>
            </a>
            <div class="auth-showcase-copy">
                <small>Área exclusiva</small>
                <p class="auth-display-title">O treino<br>continua<br><span>por aqui.</span></p>
            </div>
            <div class="auth-showcase-foot">
                <strong>Garagem Aço · Artes marciais</strong>
                <span>Santa Felicidade, Curitiba.</span>
            </div>
        </section>

        <main class="auth-card" id="login-content" tabindex="-1">
            <span class="eyebrow">Acesso seguro</span>
            <h1>Bom ter você aqui.</h1>
            <p class="subtitle">Entre para acompanhar seus treinos ou cuidar da rotina da academia.</p>

            <?php if (!empty($error)): ?>
                <div class="alert alert-error" role="alert">
                    <?php echo icon('alert'); ?>
                    <span><?php echo e($error); ?></span>
                </div>
            <?php endif; ?>

            <form class="auth-form" method="post" action="<?php echo e(url('/login')); ?>" autocomplete="on">
                <input type="hidden" name="_csrf" value="<?php echo e(Csrf::token()); ?>">

                <div class="auth-field">
                    <label for="email">E-mail</label>
                    <div class="input-shell">
                        <?php echo icon('user-round'); ?>
                        <input id="email" type="email" name="email" autocomplete="email" inputmode="email" spellcheck="false" placeholder="seu@email.com" required value="<?= e($_POST['email'] ?? '') ?>">
                    </div>
                </div>

                <div class="auth-field">
                    <label for="senha">Senha</label>
                    <div class="input-shell">
                        <?php echo icon('settings'); ?>
                        <input id="senha" type="password" name="senha" autocomplete="current-password" placeholder="Digite sua senha" required>
                        <button class="password-toggle" type="button" data-password-toggle aria-label="Mostrar senha" aria-pressed="false">
                            <?php echo icon('eye'); ?>
                        </button>
                    </div>
                </div>

                <button class="btn btn-primary auth-submit" type="submit">
                    <span>Entrar na conta</span>
                    <?php echo icon('chevron-right'); ?>
                </button>
            </form>

            <div class="auth-meta">
                <?php echo icon('check-circle'); ?>
                <span>Ambiente protegido pela Garagem Aço</span>
            </div>
            <a class="auth-back" href="<?php echo e(url('/')); ?>">
                <?php echo icon('arrow-left'); ?>
                <span>Voltar para o site</span>
            </a>
        </main>
    </div>

    <script>
        (() => {
            const toggle = document.querySelector('[data-password-toggle]');
            const input = document.querySelector('#senha');
            if (!toggle || !input) return;

            toggle.addEventListener('click', () => {
                const showing = input.type === 'text';
                input.type = showing ? 'password' : 'text';
                toggle.setAttribute('aria-label', showing ? 'Mostrar senha' : 'Ocultar senha');
                toggle.setAttribute('aria-pressed', String(!showing));
            });
        })();
    </script>
    <script defer src="<?= e(asset('/js/interface.js')) ?>"></script>
</body>
</html>
