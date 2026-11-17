# [0001] Reformulação visual do painel Garagem Aço

**Data:** 2026-08-31  
**Status:** Concluída  
**Solicitação original:** "precisa fazer todo o front melhor dessa parte login tela do admin etc usando icones e nunca emojis".

## Objetivo

Entregar um frontend administrativo e um portal do aluno visualmente profissionais, responsivos e consistentes com a landing da Garagem Aço, usando ícones SVG acessíveis no lugar de emojis e preservando as funções, rotas, permissões e formulários existentes.

## Contexto técnico

- O projeto usa PHP com views renderizadas no servidor e `public/css/app.css` como folha principal do painel.
- `app/views/layouts/main.php` é o shell compartilhado por quase todas as telas autenticadas.
- O login e os erros têm layouts próprios; o portal do aluno usa estilos próprios dentro de `app.css`.
- Não há necessidade de trocar PHP por Next.js: a demanda é visual e deve preservar o backend já validado.

## Tasks

### Task 01 — Shell administrativo, navegação e ícones

**Objetivo:** Criar a base visual do painel com sidebar responsiva, cabeçalho móvel, identidade da marca, estados ativos, navegação por teclado e biblioteca de ícones SVG inline.
**Camadas:** Views, CSS, Helpers
**Modo:** direto
**Depende de:** —
**Instrução para o subagente:**
> Spec 0001 — Task 01: modernize `app/views/layouts/main.php`, `app/helpers.php` e `public/css/app.css`. Use tema grafite/preto, vermelho da marca, tipografia clara, sidebar que vira drawer no mobile, botão de abertura/fechamento acessível e estado ativo baseado na rota atual. Crie um helper de ícones SVG inline, sem emojis, biblioteca externa ou chamadas remotas; inclua ícones para dashboard, alunos, modalidades, turmas, graduações, professores, frequência, check-in, biometria, financeiro, promoções, usuários, configurações, sair, menu, fechar, editar, excluir e adicionar. Preserve todos os links, permissões, CSRF e logout. Toque apenas nas camadas Views, CSS e Helpers; não modifique controllers, services, repositories ou banco.

### Task 02 — Login, erros e portal do aluno

**Objetivo:** Aplicar o novo padrão visual às entradas de autenticação e ao portal do aluno, com estados vazios, ações e responsividade.
**Camadas:** Views, CSS
**Modo:** direto
**Depende de:** Task 01
**Instrução para o subagente:**
> Spec 0001 — Task 02: modernize `app/views/auth/login.php`, `app/views/errors/error.php` e `app/views/portal/dashboard.php`, usando o helper de ícones criado na Task 01 e as classes do novo design system. Faça o login parecer uma tela de produto real, com marca, campos acessíveis, foco visível, mensagem de erro, botão com ícone e link de retorno; deixe o erro HTTP elegante e o portal com cabeçalho, resumo financeiro, cards e ações sem emojis. Preserve nomes dos campos, CSRF, URLs, textos de erro e logout. Toque apenas nas camadas Views e CSS; não modifique autenticação, controllers ou banco.

### Task 03 — Dashboard e operação diária

**Objetivo:** Melhorar a leitura do dashboard e das telas de financeiro, check-in e biometria.
**Camadas:** Views, CSS
**Modo:** direto
**Depende de:** Task 01
**Instrução para o subagente:**
> Spec 0001 — Task 03: modernize `app/views/dashboard/index.php`, `app/views/financeiro/index.php`, `app/views/checkin/index.php` e `app/views/biometria/index.php`. Transforme números em cards com ícones SVG, organize cabeçalhos e ações, dê destaque visual aos estados de acesso e deixe formulários e mensagens fáceis de ler em desktop e mobile. Preserve todas as variáveis PHP, links, métodos POST e CSRF. Toque apenas nas camadas Views e CSS; não modifique controllers, services ou repositories.

### Task 04 — Cadastros principais

**Objetivo:** Padronizar listas e formulários de alunos, modalidades, turmas e graduações.
**Camadas:** Views, CSS
**Modo:** direto
**Depende de:** Task 01
**Instrução para o subagente:**
> Spec 0001 — Task 04: modernize somente estas views: `app/views/alunos/index.php`, `app/views/alunos/form.php`, `app/views/modalidades/index.php`, `app/views/modalidades/form.php`, `app/views/turmas/index.php`, `app/views/turmas/form.php`, `app/views/graduacoes/index.php` e `app/views/graduacoes/form.php`. Use cabeçalhos com contexto, botões com ícones SVG, tabelas responsivas, badges para status e formulários agrupados em cards. Remova qualquer emoji ou símbolo decorativo usado como ação, preservando nomes de campos, variáveis, rotas, confirmações e CSRF. Toque apenas nas camadas Views e CSS; não modifique controllers, services ou banco.

### Task 05 — Equipe, promoções e frequência

**Objetivo:** Padronizar as telas de professores, promoções e frequência.
**Camadas:** Views, CSS
**Modo:** direto
**Depende de:** Task 01
**Instrução para o subagente:**
> Spec 0001 — Task 05: modernize somente `app/views/professores/index.php`, `app/views/professores/form.php`, `app/views/promocoes/index.php`, `app/views/promocoes/form.php`, `app/views/frequencia/index.php` e `app/views/frequencia/form.php`. Use o design system do shell, ações com ícones SVG, tabelas e formulários responsivos, estados de sucesso/erro bem destacados e nenhum emoji. Preserve todos os dados PHP, links, confirmações, métodos POST e CSRF. Toque apenas nas camadas Views e CSS; não modifique controllers, services ou banco.

### Task 06 — Usuários e configurações

**Objetivo:** Melhorar as áreas sensíveis de usuários e configurações, com apresentação clara de permissões e ações.
**Camadas:** Views, CSS
**Modo:** direto
**Depende de:** Task 01
**Instrução para o subagente:**
> Spec 0001 — Task 06: modernize `app/views/usuarios/index.php`, `app/views/usuarios/form.php` e `app/views/configuracoes/index.php`. Use cards de configuração, badges de perfil, ações com ícones SVG, campos agrupados e feedback visual consistente. Preserve os nomes dos campos, a proteção CSRF, os métodos HTTP, os links e as variáveis existentes; não adicione senha padrão nem exponha credenciais. Toque apenas nas camadas Views e CSS; não modifique controllers, services ou banco.

### Task 07 — Revisão final visual e acessibilidade

**Objetivo:** Verificar todas as telas, remover emojis/símbolos de ação, corrigir responsividade e documentar a entrega.
**Camadas:** Views, CSS, documentação
**Modo:** direto
**Depende de:** Tasks 01–06
**Instrução para o subagente:**
> Spec 0001 — Task 07: revise todas as views em `app/views/` e `public/css/app.css`. Procure emojis e símbolos usados como ícones e substitua-os por SVG ou texto semântico; valide foco de teclado, contraste, labels, overflow horizontal, drawer mobile, tabelas e formulários. Faça somente correções visuais/acessíveis sem alterar regras, rotas ou contratos PHP. Registre o resultado em `.claude/implements/` e atualize o checklist da spec. Toque apenas nas camadas Views, CSS e documentação; não modifique controllers, services, repositories ou banco.

## Como executar

Executar as tasks na ordem indicada. Tasks 03, 04, 05 e 06 dependem somente da Task 01 e podem ser executadas em paralelo quando houver agentes disponíveis. A Task 07 deve ser a última.

## Checklist de conclusão

- [x] Task 01 — Shell administrativo, navegação e ícones → implements/0004
- [x] Task 02 — Login, erros e portal do aluno → implements/0004
- [x] Task 03 — Dashboard e operação diária → implements/0004
- [x] Task 04 — Cadastros principais → implements/0004
- [x] Task 05 — Equipe, promoções e frequência → implements/0004
- [x] Task 06 — Usuários e configurações → implements/0004
- [x] Task 07 — Revisão final visual e acessibilidade → implements/0004
