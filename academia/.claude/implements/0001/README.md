# [0001] Landing page pública Garagem Aço

**Data:** 2026-08-31
**Status:** Concluído
**Modo:** direto

## Solicitação

Criar a landing page da academia Garagem Aço adaptando a identidade industrial da marca e a logo PNG fornecida à arquitetura PHP existente, sem quebrar rotas, formulários ou integrações.

## Contexto

O projeto tinha apenas o painel administrativo protegido. A rota pública `/` redirecionava diretamente para `/dashboard`, e não havia uma apresentação institucional para visitantes. Também não existiam fotografias reais, link de WhatsApp ou endereço completo configurados no código.

## O que foi feito

- A raiz pública agora renderiza uma landing page sem autenticação; as rotas administrativas continuam protegidas pelo fluxo existente.
- A inicialização permite que somente a landing pública seja exibida sem PostgreSQL; login e rotas administrativas continuam exigindo a conexão.
- Criado hero editorial com logo, mensagem principal, composição lateral de lutador, tipografia de fundo e detalhes industriais discretos.
- Criadas seções de apresentação, princípios, modalidades, benefícios, professor, horários, galeria, depoimentos, CTA, contato e footer.
- Criado menu mobile nativo com `<details>`, header sticky, foco visível, skip link e suporte a `prefers-reduced-motion`.
- Adicionado botão flutuante de WhatsApp com fallback para a área de contato até que o link real seja configurado.
- Abaixo da dobra, imagens usam `loading="lazy"` e `decoding="async"`.
- Logo fornecida foi copiada para um caminho público sem alterar o arquivo original.
- Placeholders SVG locais foram criados para as fotos ausentes, mantendo a substituição concentrada no mapa `$media` da view.

## Arquivos modificados

- `public/index.php` — removeu o redirecionamento automático da raiz para o dashboard.
- `app/bootstrap.php` — permite fallback sem PDO somente para a requisição pública da landing.
- `app/routes/web.php` — adicionou a rota pública da landing antes da autorização das rotas protegidas.

## Arquivos criados

- `app/views/landing/index.php` — view completa da landing pública.
- `public/css/landing.css` — identidade visual e responsividade isoladas da interface do painel.
- `public/assets/logo-garagem-aco.png` — cópia pública da logo PNG fornecida.
- `public/assets/landing/*.svg` — placeholders de hero, academia, professor, modalidades e galeria.

## Decisões técnicas

- A landing ficou em uma view própria porque o projeto atual usa views PHP server-side e não possui biblioteca de componentes de front-end.
- A conexão com banco só é tolerada como indisponível na raiz pública, que não consulta dados; qualquer outra rota mantém falha explícita para não mascarar problemas do painel.
- Os estilos foram isolados em `landing.css` para preservar o layout e os componentes do painel administrativo.
- O menu mobile usa HTML nativo e CSS, evitando JavaScript e dependências novas para uma interação simples.
- O WhatsApp lê `app_config('whatsapp_url')` quando esse valor existir; sem o link real, o fallback leva ao bloco de contato e não inventa um número.
- O mapa usa uma busca regional do Google Maps porque o endereço completo ainda não foi informado.

## Como validar

1. Configure PHP 8.2+, Composer e PostgreSQL conforme o `CLAUDE.md`.
2. Execute `composer test`.
3. Sirva `public/` pelo servidor web configurado ou execute o servidor PHP embutido com `public/router.php`.
4. Confira `/`, `/login` e uma rota administrativa como `/dashboard`.
5. Verifique a landing em 360px, 390px, 430px, tablet, notebook e desktop; teste também o menu mobile, links de âncora, foco pelo teclado, hover dos cards e botão de WhatsApp.

## Resultado da validação

- Passou na checagem estática de balanceamento de chaves/parênteses do CSS.
- Todos os placeholders SVG foram validados como XML.
- Todos os assets referenciados pela view existem no diretório público.
- Todas as âncoras internas referenciadas existem na página.
- A logo pública tem o mesmo SHA-256 do arquivo original fornecido.
- Foi confirmada a presença de lazy loading nas imagens abaixo da dobra e de fallback para redução de movimento.
- O lint PHP foi executado com o PHP 8.2 instalado localmente em todos os arquivos de `app/` e `public/`, sem erros de sintaxe.
- O PHPUnit foi executado diretamente com o binário local: `9 testes / 18 asserções`, todos aprovados.
- O servidor PHP ativo em `127.0.0.1:8000` foi testado com HTTP: `/` retornou `200`, `landing.css` retornou `200`, a logo retornou `200` e `/dashboard` continuou exigindo a conexão do banco.
- `composer test` não pôde ser executado pelo comando Composer porque `composer` não está disponível no PATH; o mesmo PHPUnit foi executado diretamente e passou.
