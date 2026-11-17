# [0003] Suporte a PostgreSQL remoto do Supabase

**Data:** 2026-08-31  
**Status:** Concluído  
**Modo:** direto

## Solicitação

Preparar o banco para ficar no Supabase, mantendo o frontend e a aplicação acessíveis pela infraestrutura do Cloudflare quando necessário.

## Contexto

O projeto atual é PHP com PDO e PostgreSQL, não Next.js. Portanto, os helpers `@supabase/ssr` não são necessários para esta aplicação. O Supabase pode hospedar o PostgreSQL remoto, enquanto o PHP continua precisando de um servidor com runtime PHP.

## O que foi feito

- Adicionado suporte à opção `DB_SSLMODE` no DSN do PDO.
- Mantido `prefer` como padrão para não quebrar o PostgreSQL local.
- Documentada a variável no `.env.example`.

## Arquivos modificados

- `app/config/database.php` — lê `DB_SSLMODE`.
- `app/bootstrap.php` — inclui `sslmode` na conexão PDO.
- `.env.example` — adiciona a configuração de SSL.

## Decisões técnicas

O frontend server-rendered e o backend ainda estão acoplados nas views PHP. Uma migração para Cloudflare Pages com frontend separado exigiria uma nova API e uma revisão de autenticação/RLS; por isso não foi feita como parte deste ajuste pontual.

## Como validar

No `.env` de produção, preencher os valores exatos da conexão exibida em Supabase > Connect e usar `DB_SSLMODE=require`. Depois executar `php bin/migrate.php` e testar o login.

## Resultado da validação

- `php -l app/bootstrap.php` — sem erros de sintaxe.
- `php -l app/config/database.php` — sem erros de sintaxe.
- `git diff --check` — sem erros de whitespace.
- Landing local — HTTP 200.
- Conexão real com Supabase — sucesso.
- Migration inicial — aplicada com sucesso.
- Login administrativo — POST com CSRF validado e redirecionamento para `/dashboard` com HTTP 200.
