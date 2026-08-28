<?php
// app/views/dashboard/index.php
$content = ob_get_clean();
ob_start();
?>
<div class="page-header">
    <h1>Dashboard</h1>
</div>

<div class="cards-grid">
    <div class="card">
        <div class="card-title">Alunos Ativos</div>
        <div class="card-number"><?php echo $stats['alunos_ativos']; ?></div>
    </div>
    <div class="card">
        <div class="card-title">Turmas Ativas</div>
        <div class="card-number"><?php echo $stats['turmas_ativas']; ?></div>
    </div>
    <div class="card">
        <div class="card-title">Mensalidades Abertas</div>
        <div class="card-number"><?php echo $stats['mensalidades_abertas']; ?></div>
    </div>
    <div class="card">
        <div class="card-title">Mensalidades Vencidas</div>
        <div class="card-number"><?php echo $stats['mensalidades_vencidas']; ?></div>
    </div>
</div>

<div class="page-header">
    <h2>Financeiro</h2>
</div>
<div class="panel">
    <p><strong>Faturamento em aberto:</strong> R$ <?php echo number_format((float)($financeiro['faturamento'] ?? 0), 2, ',', '.'); ?></p>
    <p><strong>Recebido:</strong> R$ <?php echo number_format((float)($financeiro['recebido'] ?? 0), 2, ',', '.'); ?></p>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
