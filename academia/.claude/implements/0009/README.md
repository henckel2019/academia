# [0009] Otimização de carregamento e cache

**Data:** 2026-08-31  
**Status:** Concluído  
**Modo:** direto

## Solicitação

Melhorar o carregamento do site, que estava lento principalmente pelo túnel e pelo banco remoto.

## O que foi feito

- Conexões PostgreSQL passaram a ser persistentes por padrão.
- A grade pública de modalidades passou a ser salva em cache local JSON.
- A landing não abre nova conexão remota quando o cache público existe.
- O cache da grade é invalidado quando uma modalidade é criada, editada, ativada ou inativada.
- O servidor PHP embutido e o `.htaccess` passaram a informar cache de sete dias para CSS, JS, imagens e fontes.
- A landing usa `content-visibility` nas seções para reduzir o trabalho inicial de renderização.
- O bootstrap continua conectando normalmente em migrations e comandos CLI.

## Arquivos modificados

- `app/helpers.php`
- `app/bootstrap.php`
- `app/config/database.php`
- `app/routes/web.php`
- `app/Repositories/ModalityManagementRepository.php`
- `public/router.php`
- `public/.htaccess`
- `public/css/landing.css`

## Resultado da validação

- Landing: aproximadamente 2,7 s na primeira leitura remota e 23–26 ms nas leituras seguintes com cache.
- Login: aproximadamente 0,6–0,7 s após conexão persistente.
- CSS: HTTP 200 com `Cache-Control: public, max-age=604800, immutable`.
- Migration via CLI continua funcionando.
