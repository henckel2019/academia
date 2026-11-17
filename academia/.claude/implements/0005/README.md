# [0005] Logo compacta e sidebar responsiva

**Data:** 2026-08-31  
**Status:** Concluído  
**Modo:** direto

## Solicitação

Remover o texto da Garagem Aço da barra lateral, deixar somente a logo e garantir uma experiência responsiva no celular.

## O que foi feito

- Removido o wordmark textual da sidebar.
- Mantida somente a logo com dimensões fixas no HTML e no CSS.
- Menu lateral convertido em drawer com backdrop em telas de até 900px.
- Adicionado versionamento automático aos assets para evitar cache antigo no túnel.

## Arquivos modificados

- `app/views/layouts/main.php`
- `app/views/auth/login.php`
- `app/views/errors/error.php`
- `app/helpers.php`
- `public/css/app.css`

## Resultado da validação

- Layout PHP sem erros de sintaxe.
- Login local HTTP 200.
- CSS local HTTP 200.
- Logo confirmada com tamanho fixo e CSS com parâmetro de versão.
