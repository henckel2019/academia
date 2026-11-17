# [0008] Turmas reservadas para aulas particulares

**Data:** 2026-08-31  
**Status:** Concluído  
**Modo:** direto

## Solicitação

Usar a área de turmas somente para aulas particulares.

## Contexto

A grade geral e pública da academia agora pertence às modalidades. A área de turmas não deve duplicar essa grade nem ser confundida com os horários publicados na landing page.

## O que foi feito

- Menu e dashboard passaram a exibir “Aulas particulares”.
- Lista e formulário foram ajustados para atendimento individual.
- Capacidade foi fixada em uma vaga.
- Formulário informa que o cadastro não altera os horários públicos das modalidades.
- O fluxo de modalidades continua sendo o responsável pela grade exibida na landing page.

## Arquivos modificados

- `app/views/layouts/main.php` — rótulo do menu.
- `app/views/dashboard/index.php` — indicador de aulas particulares.
- `app/views/turmas/index.php` — textos da lista.
- `app/views/turmas/form.php` — textos e orientação do cadastro.
- `app/controllers/TurmaController.php` — capacidade individual fixa em uma vaga.

## Como validar

Entrar como administrador, abrir “Aulas particulares” e conferir que o cadastro identifica um atendimento individual. Em “Modalidades”, os horários públicos continuam sendo cadastrados separadamente.

## Resultado da validação

- PHP lint dos arquivos alterados sem erros de sintaxe.
- Landing local retornou HTTP 200.
