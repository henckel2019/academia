# [0007] Modalidades e grade semanal publicada

**Data:** 2026-08-31  
**Status:** Concluído  
**Modo:** TDD

## Solicitação

Permitir que o administrador cadastre modalidades, informe os dias e horários semanais e publique automaticamente a grade na landing page.

## Regra aplicada

- Uma modalidade pode ter vários horários semanais.
- O dia da semana usa `1` para segunda-feira até `7` para domingo.
- O horário final precisa ser posterior ao inicial.
- O mesmo intervalo não pode ser repetido na mesma modalidade e no mesmo dia.
- Modalidades inativas não aparecem na landing page.
- Uma modalidade sem horário aparece como `A definir` até a grade ser cadastrada.

## Implementação

- Criada a tabela `modalidade_horarios` com validações e índices para a grade pública.
- Criados repositório e serviço de gestão de modalidades.
- Tela administrativa refeita com cards responsivos, ativação/inativação e editor dinâmico de horários.
- Landing page passou a carregar modalidades e horários ativos diretamente do banco.
- Mantido fallback visual da landing quando o banco não estiver disponível.

## Arquivos afetados

- `database/migrations/20260831_000003_modality_schedules.php`
- `database/schema.sql`
- `app/Repositories/ModalityManagementRepository.php`
- `app/Repositories/ModalityManagementRepositoryInterface.php`
- `app/Services/ModalityManagementService.php`
- `app/controllers/ModalidadeController.php`
- `app/routes/web.php`
- `app/views/modalidades/index.php`
- `app/views/modalidades/form.php`
- `app/views/landing/index.php`
- `public/css/app.css`
- `public/css/landing.css`
- `tests/Unit/ModalityManagementServiceTest.php`

## Validação

- Migration aplicada no Supabase.
- PHP lint dos arquivos novos e alterados sem erros de sintaxe.
- Landing local retornou HTTP 200 e carregou o CSS corretamente.
- A tela `/modalidades` continua protegida pelo login administrativo.
