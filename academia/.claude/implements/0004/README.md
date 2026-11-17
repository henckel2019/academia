# [0004] Reformulação visual do painel e portal

**Data:** 2026-08-31  
**Status:** Concluído  
**Modo:** direto  
**Spec:** `.claude/specs/0001/`

## Solicitação

"Precisa fazer todo o front melhor dessa parte login tela do admin etc usando ícones e nunca emojis." 

## Contexto

As telas autenticadas tinham um visual básico e inconsistente, com pouca hierarquia, navegação sem estado ativo e baixa adaptação para telas pequenas. A landing já tinha identidade visual mais forte, então o painel precisava acompanhar o mesmo padrão.

## O que foi feito

- Criado um design system escuro com grafite, aço e vermelho da Garagem Aço.
- Criada biblioteca de ícones SVG inline, sem emojis e sem dependência externa.
- Reformados sidebar, navegação por grupos, estado ativo, header, drawer mobile, usuário e logout.
- Reformados login, tela de erro, dashboard, financeiro, check-in, biometria e portal do aluno.
- Padronizados cadastros, tabelas, badges, estados vazios, ações, formulários e responsividade.
- Preservados rotas, variáveis PHP, CSRF, permissões e regras existentes.

## Arquivos modificados

- `app/helpers.php` — helper de ícones SVG.
- `app/views/layouts/main.php` — shell administrativo e navegação responsiva.
- `app/views/auth/login.php` — login profissional com mostrar/ocultar senha.
- `app/views/errors/error.php` — tela de erro visual.
- `app/views/dashboard/index.php` — indicadores e resumo financeiro.
- `app/views/portal/dashboard.php` — portal do aluno e ações PIX.
- `app/views/financeiro/index.php`, `app/views/checkin/index.php`, `app/views/biometria/index.php` — telas operacionais.
- `app/views/alunos/`, `app/views/modalidades/`, `app/views/turmas/`, `app/views/graduacoes/` — listas e formulários.
- `app/views/professores/`, `app/views/promocoes/`, `app/views/frequencia/`, `app/views/usuarios/`, `app/views/configuracoes/` — listas e formulários.
- `public/css/app.css` — novo design system, componentes e responsividade.

## Como validar

1. Abrir `/login` e testar o botão de mostrar senha.
2. Entrar com uma conta da equipe e verificar o menu lateral, dashboard e telas de cadastro.
3. Reduzir a janela para validar o menu drawer e tabelas com rolagem horizontal.
4. Entrar com um aluno e verificar o portal e o botão de copiar PIX.

## Resultado da validação

- Todas as views PHP: sem erros no `php -l`.
- `git diff --check`: sem erros de whitespace.
- Frontend sem emojis no PHP/CSS.
- CSS servido pelo servidor local com HTTP 200.
- Login administrativo validado com CSRF e redirecionamento para `/dashboard` com HTTP 200.
- `/alunos` validado com sessão administrativa com HTTP 200.
