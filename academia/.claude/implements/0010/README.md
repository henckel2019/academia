# [0010] Cobrança automática de graduação

**Data:** 2026-08-31  
**Status:** Concluído  
**Modo:** direto

## Solicitação

Ao lançar uma graduação, perguntar o valor e gerar uma cobrança para o aluno.

## O que foi feito

- O cadastro de graduação passou a exigir valor e vencimento da cobrança.
- A graduação salva o valor no histórico técnico.
- Ao salvar, o sistema localiza a matrícula ativa do aluno na modalidade escolhida.
- Uma cobrança avulsa do tipo `GRADUACAO` é criada na mesma transação.
- A cobrança fica identificada no financeiro e aparece no portal do aluno com descrição, vencimento e status.
- A unicidade das mensalidades normais foi preservada, permitindo cobrança de graduação no mesmo mês.
- Corrigido o vínculo do professor para usar `professor_id`, conforme o schema.

## Arquivos modificados

- `database/migrations/20260831_000004_graduation_charges.php`
- `database/schema.sql`
- `app/Repositories/GraduationManagementRepository.php`
- `app/Repositories/GraduationManagementRepositoryInterface.php`
- `app/Services/GraduationManagementService.php`
- `app/controllers/GraduacaoController.php`
- `app/views/graduacoes/index.php`
- `app/views/graduacoes/form.php`
- `app/Repositories/StudentPortalRepository.php`
- `app/views/portal/dashboard.php`
- `public/css/app.css`

## Como validar

Cadastre o aluno em uma modalidade ativa, abra `Graduações > Nova graduação`, informe faixa, valor e vencimento e salve. A lista exibirá a cobrança e o aluno verá o lançamento no portal.

## Resultado da validação

- Migration aplicada no Supabase.
- PHP lint dos arquivos alterados sem erros de sintaxe.
- Repositório real consultado sem graduações pendentes e sem alunos de teste criados.
