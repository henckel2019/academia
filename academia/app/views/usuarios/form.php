<?php
// app/views/usuarios/form.php
$content = ob_get_clean();
ob_start();
?>
<div class="page-header">
    <h1>Novo Usuário</h1>
</div>

<form method="POST">
    <label>Nome *
        <input type="text" name="nome" required>
    </label>
    <label>Email *
        <input type="email" name="email" required>
    </label>
    <label>Senha inicial
        <input type="text" name="senha" value="senha@123">
    </label>
    <label>Perfil
        <select name="perfil_id" required>
            <option value="">Selecione</option>
            <?php foreach ($perfis as $p): ?>
                <option value="<?php echo (int)$p['id']; ?>"><?php echo htmlspecialchars($p['nome']); ?></option>
            <?php endforeach; ?>
        </select>
    </label>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary">Salvar</button>
        <a href="/academia/public/usuarios" class="btn btn-secondary">Voltar</a>
    </div>
</form>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/main.php';
