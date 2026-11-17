<?php

declare(strict_types=1);

use Academia\Core\Csrf;

$formatDate = static function (mixed $date): string {
    if (!$date) {
        return '—';
    }

    return (new DateTimeImmutable((string) $date))->format('d/m/Y');
};

$statusLabels = [
    'ABERTA' => 'Em aberto',
    'VENCIDA' => 'Vencida',
    'PAGA' => 'Paga',
    'CANCELADA' => 'Cancelada',
];
$studentStatusLabels = ['ATIVO' => 'Ativo', 'SUSPENSO' => 'Suspenso', 'CANCELADO' => 'Cancelado'];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#090a0c">
    <title>Portal do aluno — <?php echo e(app_config('app_name')); ?></title>
    <link rel="icon" href="<?= e(asset('/assets/logo-garagem-aco.png')) ?>">
    <link rel="stylesheet" href="<?= e(asset('/css/fonts.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('/css/tokens.css')) ?>">
    <link rel="stylesheet" href="<?php echo e(asset('/css/app.css')); ?>">
</head>
<body class="portal-page">
    <a class="skip-link" href="#portal-content">Pular para o portal</a>
    <main class="portal-shell" id="portal-content" tabindex="-1">
        <header class="portal-header">
            <div>
                <a class="portal-brand" href="<?php echo e(url('/')); ?>"><img src="<?= e(asset('/assets/logo-garagem-aco.png')) ?>" width="50" height="50" alt="">GARAGEM <strong>AÇO</strong></a>
                <span class="portal-kicker">Portal do aluno</span>
            </div>
            <div class="portal-header__actions">
                <a href="<?php echo e(url('/')); ?>">Voltar ao site</a>
                <form method="post" action="<?php echo e(url('/logout')); ?>">
                    <input type="hidden" name="_csrf" value="<?php echo e(Csrf::token()); ?>">
                    <button type="submit" class="link-button">Sair</button>
                </form>
            </div>
        </header>

        <section class="portal-welcome">
            <span class="portal-eyebrow">Área segura</span>
            <h1>Olá, <?php echo e((string) ($user['name'] ?? 'aluno')); ?>.</h1>
            <p>Acompanhe suas modalidades, vencimentos, pagamentos, atrasos e próximas cobranças em um só lugar.</p>
        </section>

        <?php if ($student === null): ?>
            <section class="portal-card access-blocked" role="alert">
                <div class="portal-card__heading">
                    <div>
                        <span class="portal-eyebrow">Cadastro</span>
                        <h2>Acesso ainda não vinculado</h2>
                    </div>
                    <?php echo icon('alert'); ?>
                </div>
                <p>Seu usuário foi criado, mas ainda não está ligado a um cadastro de aluno. Fale com a administração para concluir o vínculo.</p>
            </section>
        <?php else: ?>
            <section class="portal-summary-grid" aria-label="Resumo financeiro do aluno">
                <article class="portal-summary-card">
                    <span>Aluno</span>
                    <strong><?php echo e((string) $student['nome']); ?></strong>
                </article>
                <article class="portal-summary-card">
                    <span>Mensalidades atrasadas</span>
                    <strong class="portal-status portal-status--vencida"><?php echo (int) ($portalSummary['atrasadas'] ?? 0); ?></strong>
                </article>
                <article class="portal-summary-card">
                    <span>Próximas cobranças</span>
                    <strong><?php echo (int) ($portalSummary['futuras'] ?? 0); ?></strong>
                </article>
                <article class="portal-summary-card">
                    <span>Total em aberto</span>
                    <strong>R$ <?php echo e(format_money($portalSummary['total_em_aberto'] ?? '0.00')); ?></strong>
                </article>
            </section>

            <section class="portal-card">
                <div class="portal-card__heading">
                    <div>
                        <span class="portal-eyebrow">Minha matrícula</span>
                        <h2>Modalidades e vencimento</h2>
                    </div>
                    <span class="portal-status portal-status--<?php echo e(strtolower((string) $student['situacao'])); ?>"><?php echo e($studentStatusLabels[$student['situacao']] ?? $student['situacao']); ?></span>
                </div>
                <?php if ($enrollments === []): ?>
                    <div class="empty-state">
                        <?php echo icon('dumbbell'); ?>
                        <div><strong>Nenhuma modalidade ativa</strong><p>Fale com a academia para atualizar sua matrícula.</p></div>
                    </div>
                <?php else: ?>
                    <div class="portal-enrollment-grid">
                        <?php foreach ($enrollments as $enrollment): ?>
                            <article class="portal-enrollment">
                                <div>
                                    <span class="portal-eyebrow">Modalidade</span>
                                    <h3><?php echo e((string) $enrollment['modalidade_nome']); ?></h3>
                                </div>
                                <span class="portal-status portal-status--<?php echo e(strtolower((string) $enrollment['situacao'])); ?>"><?php echo e((string) $enrollment['situacao']); ?></span>
                                <div class="portal-enrollment__details">
                                    <span>Mensalidade<strong>R$ <?php echo e(format_money($enrollment['valor_plano'] ?? '0.00')); ?></strong></span>
                                    <span>Vencimento<strong>Todo dia <?php echo (int) $enrollment['dia_vencimento']; ?></strong></span>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>

            <section class="portal-card">
                <div class="portal-card__heading">
                    <div>
                        <span class="portal-eyebrow">Financeiro</span>
                        <h2>Minhas cobranças</h2>
                    </div>
                    <?php echo icon('wallet'); ?>
                </div>
                <p>Consulte o histórico, identifique atrasos e copie o PIX das cobranças em aberto.</p>

                <?php if ($invoices === []): ?>
                    <div class="empty-state">
                        <?php echo icon('calendar'); ?>
                        <div><strong>Nenhuma cobrança lançada</strong><p>As cobranças aparecerão aqui quando forem geradas pela academia.</p></div>
                    </div>
                <?php else: ?>
                    <div class="portal-invoices">
                        <?php foreach ($invoices as $invoice): ?>
                            <?php
                            $status = (string) ($invoice['status'] ?? 'ABERTA');
                            $invoiceType = (string) ($invoice['tipo'] ?? 'MENSALIDADE');
                            $invoiceLabel = $invoiceType === 'GRADUACAO' ? 'Cobrança de graduação' : 'Competência ' . (string) ($invoice['competencia'] ?? '');
                            ?>
                            <article class="portal-invoice portal-invoice--<?php echo e(strtolower($status)); ?>">
                                <div class="portal-invoice__main">
                                    <span><?php echo e($invoiceLabel); ?></span>
                                    <h3><?php echo e((string) $invoice['modalidade_nome']); ?></h3>
                                    <?php if (!empty($invoice['descricao'])): ?><small><?php echo e((string) $invoice['descricao']); ?></small><?php endif; ?>
                                    <small>Vencimento: <?php echo e($formatDate($invoice['data_vencimento'] ?? null)); ?></small>
                                    <?php if ($status === 'PAGA' && !empty($invoice['data_pagamento'])): ?>
                                        <small>Pago em: <?php echo e($formatDate($invoice['data_pagamento'])); ?></small>
                                    <?php endif; ?>
                                </div>
                                <div class="portal-invoice__value">
                                    <span class="portal-status portal-status--<?php echo e(strtolower($status)); ?>"><?php echo e($statusLabels[$status] ?? $status); ?></span>
                                    <strong>R$ <?php echo e(format_money($invoice['valor_final'] ?? '0.00')); ?></strong>
                                </div>
                                <?php if ($status !== 'PAGA' && $status !== 'CANCELADA' && !empty($invoice['pix_copia_e_cola'])): ?>
                                    <div class="portal-pix">
                                        <span>PIX copia e cola</span>
                                        <code><?php echo e((string) $invoice['pix_copia_e_cola']); ?></code>
                                        <button type="button" class="btn btn-secondary portal-copy-button" data-copy-pix="<?php echo e((string) $invoice['pix_copia_e_cola']); ?>">
                                            <?php echo icon('clipboard'); ?>
                                            <span data-copy-label>Copiar código PIX</span>
                                        </button>
                                    </div>
                                <?php elseif ($status !== 'PAGA' && $status !== 'CANCELADA'): ?>
                                    <p class="portal-invoice__hint">O PIX desta cobrança ainda não foi cadastrado. Fale com a recepção.</p>
                                <?php endif; ?>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
        <?php endif; ?>
    </main>

    <script>
        document.querySelectorAll('[data-copy-pix]').forEach((button) => {
            button.addEventListener('click', async () => {
                const label = button.querySelector('[data-copy-label]');
                const originalLabel = label?.textContent || 'Copiar código PIX';
                try {
                    await navigator.clipboard.writeText(button.dataset.copyPix);
                    if (label) label.textContent = 'PIX copiado';
                } catch (error) {
                    if (label) label.textContent = 'Copie o código acima';
                }
                window.setTimeout(() => { if (label) label.textContent = originalLabel; }, 2200);
            });
        });
    </script>
</body>
</html>
