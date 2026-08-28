<?php
// app/views/financeiro/index.php
$content = ob_get_clean();
ob_start();
?>
<div class="page-header">
    <h1>Financeiro</h1>
    <a href="/academia/public/financeiro/mensalidades" class="btn btn-secondary">Mensalidades</a>
    <a href="/academia/public/financeiro/pagamentos" class="btn btn-secondary">Pagamentos</a>
    <a href="/academia/public/financeiro/pix" class="btn btn-secondary">Integração PIX</a>
</div>

<div class="panel">
    <p>Fluxos financeiros do sistema:</p>
    <ul>
        <li><strong>Mensalidades:</strong> geração, listagem e fechamento.</li>
        <li><strong>Pagamentos:</strong> registro manual quando necessário.</li>
        <li><strong>PIX:</strong> geração de cobrança e baixa automática.</li>
    </ul>
    <p class="hint">A integração bancária e PIX será implementada posteriormente com os códigos/credenciais.</p>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
