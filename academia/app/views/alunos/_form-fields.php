<?php

declare(strict_types=1);

$studentData = is_array($aluno ?? null) ? $aluno : [];
$enrollmentData = is_array($matriculas ?? null) ? $matriculas : [];
$isEdit = (bool) ($isEdit ?? false);
$portalPassword = (string) ($portalPassword ?? '');
?>
<div class="form-grid student-form-grid">
    <label>Nome *
        <input type="text" name="nome" value="<?php echo e($studentData['nome'] ?? ''); ?>" required>
    </label>
    <label>CPF
        <input type="text" name="cpf" value="<?php echo e($studentData['cpf'] ?? ''); ?>" placeholder="000.000.000-00">
    </label>
    <label>E-mail de acesso<?php echo $isEdit ? '' : ' *'; ?>
        <input type="email" name="email" value="<?php echo e($studentData['email'] ?? ''); ?>"<?php echo $isEdit ? '' : ' required'; ?> placeholder="aluno@email.com" autocomplete="email">
        <span class="form-help">O aluno usa este e-mail para entrar no portal.</span>
    </label>
    <label>Telefone / WhatsApp
        <input type="tel" inputmode="tel" autocomplete="tel" name="telefone" value="<?php echo e($studentData['telefone'] ?? ''); ?>" placeholder="(41) 99999-0000">
    </label>
    <label>Data de nascimento
        <input type="date" name="data_nascimento" value="<?php echo e($studentData['data_nascimento'] ?? ''); ?>">
    </label>
    <label>Data de matrícula
        <input type="date" name="data_matricula" value="<?php echo e($studentData['data_matricula'] ?? date('Y-m-d')); ?>" required>
    </label>
    <label class="field-full">Código de matrícula
        <input class="readonly-input" type="text" name="matricula_codigo" value="<?php echo e($studentData['matricula_codigo'] ?? $registrationCode ?? ''); ?>" readonly required>
        <span class="form-help">Gerado automaticamente e preenchido pelo sistema.</span>
    </label>
    <label class="field-full">Senha inicial do portal<?php echo $isEdit ? '' : ' *'; ?>
        <input type="text" name="senha_portal" value="<?php echo e($portalPassword); ?>" minlength="8"<?php echo $isEdit ? '' : ' required'; ?> autocomplete="new-password">
        <span class="form-help"><?php echo $isEdit ? 'Deixe em branco para manter a senha atual. Preencha para redefinir o acesso.' : 'Entregue esta senha ao aluno. Ele poderá usar o e-mail e esta senha para entrar.'; ?></span>
    </label>
    <?php if ($isEdit && !empty($studentData['usuario_id'])): ?>
        <div class="field-full form-help form-help--success">Acesso do portal ativo para este aluno.</div>
    <?php endif; ?>
    <div class="field-full"><input type="hidden" name="whatsapp_cobranca" value="0"><label class="consent-label"><input type="checkbox" name="whatsapp_cobranca" value="1" <?= !empty($studentData['whatsapp_cobranca']) ? 'checked' : '' ?>> Enviar lembrete mensal de pagamento pelo WhatsApp deste aluno</label><p class="form-help">Use um telefone com DDD. O lembrete segue o dia de vencimento definido abaixo e só é enviado após ativar a automação em WhatsApp e cobranças.</p></div>
</div>

<div class="student-enrollments" data-enrollment-editor>
    <div class="student-enrollments__heading">
        <div>
            <span class="eyebrow">Matrículas</span>
            <h2>Modalidades e cobrança</h2>
            <p>Defina o dia de vencimento e o valor mensal de cada modalidade do aluno.</p>
        </div>
        <button class="btn btn-secondary" type="button" data-enrollment-add>
            <?php echo icon('plus'); ?>
            <span>Adicionar modalidade</span>
        </button>
    </div>

    <div class="student-enrollment-list" data-enrollment-list data-next-index="<?php echo count($enrollmentData); ?>">
        <?php foreach ($enrollmentData as $index => $enrollment): ?>
            <div class="student-enrollment-row" data-enrollment-row>
                <div class="student-enrollment-row__top">
                    <strong>Modalidade <?php echo (int) $index + 1; ?></strong>
                    <button class="table-action table-action--danger" type="button" data-enrollment-remove title="Remover modalidade" aria-label="Remover modalidade">
                        <?php echo icon('trash'); ?>
                    </button>
                </div>
                <div class="form-grid">
                    <label>Modalidade
                        <select name="matriculas[<?php echo (int) $index; ?>][modalidade_id]" data-enrollment-modality>
                            <option value="">Selecione uma modalidade</option>
                            <?php foreach ($modalidades as $modalidade): ?>
                                <option value="<?php echo (int) $modalidade['id']; ?>" <?php echo((int) ($enrollment['modalidade_id'] ?? 0) === (int) $modalidade['id'] ? 'selected' : ''); ?>><?php echo e($modalidade['nome']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label>Dia do vencimento
                        <input type="number" name="matriculas[<?php echo (int) $index; ?>][dia_vencimento]" value="<?php echo e($enrollment['dia_vencimento'] ?? ''); ?>" min="1" max="31" placeholder="10" data-enrollment-field>
                    </label>
                    <label>Valor mensal (R$)
                        <input type="number" name="matriculas[<?php echo (int) $index; ?>][valor_plano]" value="<?php echo e($enrollment['valor_plano'] ?? ''); ?>" min="0" step="0.01" placeholder="149,90" data-enrollment-field>
                    </label>
                    <label>Início da modalidade
                        <input type="date" name="matriculas[<?php echo (int) $index; ?>][data_inicio]" value="<?php echo e($enrollment['data_inicio'] ?? date('Y-m-d')); ?>" data-enrollment-field>
                    </label>
                </div>
                <?php if (!empty($enrollment['id'])): ?>
                    <input type="hidden" name="matriculas[<?php echo (int) $index; ?>][id]" value="<?php echo (int) $enrollment['id']; ?>">
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
        <?php if ($enrollmentData === []): ?>
            <div class="student-enrollment-row" data-enrollment-row>
                <div class="student-enrollment-row__top">
                    <strong>Modalidade 1</strong>
                    <button class="table-action table-action--danger" type="button" data-enrollment-remove title="Remover modalidade" aria-label="Remover modalidade">
                        <?php echo icon('trash'); ?>
                    </button>
                </div>
                <div class="form-grid">
                    <label>Modalidade
                        <select name="matriculas[0][modalidade_id]" data-enrollment-modality>
                            <option value="">Selecione uma modalidade</option>
                            <?php foreach ($modalidades as $modalidade): ?>
                                <option value="<?php echo (int) $modalidade['id']; ?>"><?php echo e($modalidade['nome']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label>Dia do vencimento
                        <input type="number" name="matriculas[0][dia_vencimento]" min="1" max="31" placeholder="10" data-enrollment-field>
                    </label>
                    <label>Valor mensal (R$)
                        <input type="number" name="matriculas[0][valor_plano]" min="0" step="0.01" placeholder="149,90" data-enrollment-field>
                    </label>
                    <label>Início da modalidade
                        <input type="date" name="matriculas[0][data_inicio]" value="<?php echo date('Y-m-d'); ?>" data-enrollment-field>
                    </label>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($modalidades === []): ?>
        <p class="form-help form-help--warning">Nenhuma modalidade foi cadastrada ainda. Você poderá salvar o aluno e adicionar a modalidade depois.</p>
    <?php endif; ?>
</div>

<template data-enrollment-template>
    <div class="student-enrollment-row" data-enrollment-row>
        <div class="student-enrollment-row__top">
            <strong>Nova modalidade</strong>
            <button class="table-action table-action--danger" type="button" data-enrollment-remove title="Remover modalidade" aria-label="Remover modalidade"><?php echo icon('trash'); ?></button>
        </div>
        <div class="form-grid">
            <label>Modalidade
                <select name="matriculas[__INDEX__][modalidade_id]" data-enrollment-modality>
                    <option value="">Selecione uma modalidade</option>
                    <?php foreach ($modalidades as $modalidade): ?>
                        <option value="<?php echo (int) $modalidade['id']; ?>"><?php echo e($modalidade['nome']); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Dia do vencimento
                <input type="number" name="matriculas[__INDEX__][dia_vencimento]" min="1" max="31" placeholder="10" data-enrollment-field>
            </label>
            <label>Valor mensal (R$)
                <input type="number" name="matriculas[__INDEX__][valor_plano]" min="0" step="0.01" placeholder="149,90" data-enrollment-field>
            </label>
            <label>Início da modalidade
                <input type="date" name="matriculas[__INDEX__][data_inicio]" value="<?php echo date('Y-m-d'); ?>" data-enrollment-field>
            </label>
        </div>
    </div>
</template>
