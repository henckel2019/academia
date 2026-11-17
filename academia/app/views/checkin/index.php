<?php declare(strict_types=1);
ob_start(); ?>
<div class="page-header">
    <div><span class="eyebrow">Entrada da academia</span>
        <h1>Presença no treino</h1>
        <p>Identifique o aluno. O sistema consulta as pendências antes de registrar a presença.</p>
    </div><a class="btn btn-secondary" href="<?= e(url('/biometria')) ?>">Cadastrar facial</a>
</div>
<div class="checkin-layout">
    <section class="panel" data-facial data-mode="checkin" data-models="<?= e(asset('/vendor/face-api/models')) ?>"
        data-checkin="<?= e(url('/checkin')) ?>" data-csrf="<?= e(\Academia\Core\Csrf::token()) ?>"
        data-scope="<?= e(url('/')) ?>">
        <h2>Entrada facial</h2>
        <p>Um aluno de cada vez, de frente para a câmera e em um local iluminado.</p>
        <div class="camera-frame"><video data-face-video autoplay muted playsinline
                aria-label="Câmera para identificação do aluno"></video></div>
        <p data-face-status role="status" aria-live="polite">Toque em iniciar para abrir a câmera.</p>
        <div class="face-result" data-face-result role="status" aria-live="assertive"></div>
        <div class="form-actions"><button class="btn btn-primary" type="button" data-face-start>Iniciar presença
                facial</button><button class="btn btn-secondary" type="button" data-face-stop disabled>Desligar
                câmera</button><button class="btn btn-secondary" type="button" data-face-fullscreen>Tela cheia</button>
        </div>
        <p class="hint" data-face-count></p>
        <p class="hint">Uso supervisionado pela recepção. Você também pode registrar a entrada por matrícula.</p>
    </section>
    <section class="panel"><span class="eyebrow">Outra forma de entrar</span>
        <h2>Matrícula ou CPF</h2>
        <p>Uma alternativa para quando o aluno preferir não usar a câmera.</p>
        <form method="post" class="form-card" autocomplete="off">
            <input type="hidden" name="_csrf" value="<?= e(\Academia\Core\Csrf::token()) ?>">
            <label for="identificador">Matrícula, CPF ou nome completo<input id="identificador" name="identificador"
                    required maxlength="150" placeholder="Ex.: GA-0001…"></label>
            <div class="form-actions"><button class="btn btn-primary" type="submit">Registrar presença</button></div>
        </form>
    </section>
</div>
<?php if ($result): ?><section class="panel <?= $result['allowed'] ? 'access-allowed' : 'access-blocked' ?>"
    role="status">
    <h2><?= e($result['title']) ?></h2><strong><?= e($result['student'] ?? '') ?></strong>
    <p><?= e($result['message']) ?></p>
</section><?php endif ?>
<script defer src="<?= e(asset('/vendor/face-api/face-api.min.js')) ?>"></script>
<script type="module" src="<?= e(asset('/js/facial.js')) ?>"></script>
<?php $content = ob_get_clean();
include __DIR__ . '/../layouts/main.php'; ?>
