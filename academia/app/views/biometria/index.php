<?php
// app/views/biometria/index.php
$content = ob_get_clean();
ob_start();
?>
<div class="page-header">
    <h1>Biometria</h1>
</div>

<div class="panel">
    <p>Integração com leitor biométrico será realizada nesta tela após definição do SDK/API do fabricante.</p>
    <p>Ações previstas:</p>
    <ul>
        <li>Listar equipamentos cadastrados</li>
        <li>Registrar novo equipamento</li>
        <li>Visualizar log de eventos de entrada</li>
        <li>Bloqueio automático para inadimplentes/inativos</li>
    </ul>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
