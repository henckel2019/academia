# [0006] Dashboard operacional e financeiro mensal

**Data:** 2026-08-31  
**Status:** Concluído  
**Modo:** TDD

## Solicitação

Exibir na dashboard os alunos ativos, turmas ativas, mensalidades pagas e vencidas, os alunos em cada situação, o faturamento do mês e a previsão para o próximo mês.

## Regra aplicada

- A competência atual é o mês do calendário no formato `AAAA-MM`.
- Faturamento do mês soma `valor_final` das mensalidades da competência, exceto canceladas.
- Recebido do mês soma as mensalidades pagas da competência.
- A previsão do próximo mês soma os valores das matrículas ativas de alunos ativos.
- As listas de pagos e vencidos mostram aluno, modalidade, valor e data da competência atual.

## Implementação

- Criada a interface `DashboardRepositoryInterface` e o `DashboardRepository` com consultas PDO parametrizadas.
- Criado `DashboardService` para calcular competência, rótulos de mês e indicadores normalizados.
- `DashboardController` passou a delegar a consulta ao serviço, sem SQL na camada HTTP.
- Dashboard atualizada com cards, resumo financeiro e tabelas responsivas.
- Adicionado `format_money()` para exibir valores decimais sem converter dinheiro para `float`.

## Arquivos afetados

- `app/Repositories/DashboardRepository.php`
- `app/Repositories/DashboardRepositoryInterface.php`
- `app/Services/DashboardService.php`
- `app/controllers/DashboardController.php`
- `app/views/dashboard/index.php`
- `app/helpers.php`
- `public/css/app.css`
- `tests/Unit/DashboardServiceTest.php`

## Validação

- Teste novo: 1 teste, 14 assertions, aprovado.
- Suíte completa: 10 testes, 32 assertions, aprovada.
- PHP lint: todos os arquivos modificados sem erros de sintaxe.
- Supabase: consultas reais executadas com sucesso; banco atualmente sem alunos, turmas ou mensalidades, portanto os indicadores aparecem zerados até os cadastros serem feitos.
- HTTP local: login e `/dashboard` retornaram HTTP 200; CSS retornou HTTP 200.
