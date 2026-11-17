# [0002] Fotos realistas, animações e portal do aluno

**Data:** 2026-08-31  
**Status:** Concluído  
**Modo:** direto

## Solicitação

Substituir as imagens estranhas da landing page por imagens de verdade, podendo usar IA, adicionar animações semelhantes ao exemplo enviado e deixar definido o acesso de alunos e administradores.

## Contexto

A landing page ainda usava SVGs de placeholder e repetia uma única imagem nos cards de modalidades. Além disso, o login já reconhecia o perfil `STUDENT`, mas o destino `/portal/dashboard` não existia.

## O que foi feito

- Foram geradas oito fotos editoriais fotorealistas para hero, ambiente, modalidades, professor e galeria.
- Cada modalidade agora usa uma imagem própria e os textos alternativos deixaram de mencionar placeholder.
- A landing ganhou reveal por IntersectionObserver, estado de header ao rolar, parallax leve no hero, pulso sutil no elemento gráfico e fechamento do menu mobile ao navegar.
- O acesso `Entrar` foi adicionado ao header e ao menu mobile.
- A URL publica acompanha o host do tunel quando a URL configurada ainda e local (`localhost`/`127.0.0.1`).
- O `.env` local foi alinhado ao PostgreSQL que esta escutando em `127.0.0.1:5432`.
- O portal `/portal/dashboard` foi criado para alunos vinculados, mostrando situação da matrícula, mensalidades e o PIX copia-e-cola quando cadastrado.
- A autorização restringe `/portal/*` ao perfil `STUDENT`; a equipe continua no painel `/dashboard` conforme seu perfil.

## Arquivos modificados

- `app/views/landing/index.php` — novos assets, textos alternativos, links de acesso e animações JavaScript.
- `public/css/landing.css` — estados de header, reveal, pulso e parallax.
- `app/Services/RouteAuthorizationService.php` — autorização exclusiva do portal para alunos.
- `app/routes/web.php` — rota `/portal/dashboard`.
- `app/views/auth/login.php` — instrução sobre os dois tipos de acesso.
- `public/css/app.css` — estilos do portal do aluno.
- `public/assets/landing/*.png` — fotos realistas geradas para a landing.
- `app/bootstrap.php` — detecta o host publico para gerar URLs corretas atras de tunel.
- `.env` — porta local do PostgreSQL ajustada para `5432`.

## Arquivos criados

- `app/Repositories/StudentPortalRepository.php` — consulta o aluno vinculado e suas mensalidades.
- `app/controllers/PortalController.php` — prepara os dados do portal.
- `app/views/portal/dashboard.php` — tela do aluno e cópia do PIX.

## Decisões técnicas

- O login continua centralizado em `/login`: perfis `STUDENT` vão para `/portal/dashboard`; demais perfis vão para `/dashboard`.
- O portal só lê mensalidades do aluno vinculado ao usuário autenticado.
- O botão de pagamento copia o PIX cadastrado; cartão, boleto e confirmação automática dependem de um provedor e credenciais reais, que não existem no projeto.
- As imagens antigas de placeholder foram mantidas no repositório, mas não são mais referenciadas pela landing.

## Como validar

- Acesse a raiz pública e confira as fotos da landing, o reveal ao rolar, o hover dos cards e o menu mobile.
- Acesse `/login` com um usuário `STUDENT` vinculado a `alunos.usuario_id` e confira `/portal/dashboard`.
- Cadastre `pix_copia_e_cola` em uma mensalidade para conferir o botão de cópia no portal.
- Acesse `/login` com `ADMIN` ou `SUPER_ADMIN` e confira o redirecionamento para `/dashboard` e o menu de gestão.

## Resultado da validação

- Os oito assets foram copiados para `public/assets/landing/` e conferidos visualmente.
- As dimensões dos arquivos foram conferidas; os verticais estão em torno de `1024x1536` e a galeria em `1774x887`.
- Os scripts JavaScript embutidos em `app/views/landing/index.php` e `app/views/portal/dashboard.php` passaram na validação com `node`/`vm.Script`.
- `git diff --check` não apontou erros de whitespace.
- A pagina local respondeu `200`; com o host simulado de tunel, os links foram gerados como `https://demo-tunnel.example/css/landing.css` e `https://demo-tunnel.example/assets/landing/hero-training.png`.
- A porta `5432` respondeu e a porta anterior `55432` recusou conexao, confirmando o ajuste do erro do PostgreSQL.
- `composer test` e lint PHP não puderam ser executados porque `composer` e `php` não estão disponíveis no PATH deste ambiente.
