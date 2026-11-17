# Academia Garagem Aço

Sistema web em PHP 8.2+ e PostgreSQL para uma academia de artes marciais. O painel mantém alunos, modalidades, aulas particulares, graduações, frequência e cobranças. O celular da recepção usa o próprio navegador; não precisa de APK.

## Iniciar neste computador

No PowerShell, dentro desta pasta:

```powershell
powershell -ExecutionPolicy Bypass -File bin/start-local.ps1
```

Abra `http://localhost:8080/login`. O script usa o PostgreSQL 17 instalado, inicia o banco local na porta 55432, aplica migrations, gera as mensalidades do mês e inicia o servidor PHP. As credenciais geradas ficam em `.local/acesso-local.txt`, ignorado pelo Git. O usuário antigo e o `.env` remoto são preservados. Ctrl+C encerra o servidor web. Esta instalação local é para desenvolvimento e validação; não substitui uma hospedagem com backups.

## Rotina da recepção

1. Cadastre as modalidades e os alunos, incluindo modalidade, valor, início e vencimento de cada matrícula.
2. Em **Financeiro**, escolha a competência e clique em **Gerar mensalidades**. Repetir não duplica cobranças. Gere também meses anteriores quando houver pendências a importar.
3. Após receber o valor integral, escolha a forma e confirme o recebimento. PIX nesta tela é baixa manual de um PIX já recebido, sem integração bancária automática.
4. No celular da recepção, faça login com um usuário ADMIN ou RECEPTION, abra **Biometria**, selecione o aluno e registre a autorização. Abra a câmera e capture as três amostras.
5. Abra **Check-in** e toque em **Iniciar presença facial**. O servidor consulta aluno, matrícula e a cobrança não paga mais antiga, incluindo outras modalidades. Uma nova cobrança paga não esconde dívidas anteriores. O prazo de tolerância e o bloqueio são definidos em Configurações.
6. O resultado informa liberação, alerta ou bloqueio. Só entradas liberadas geram presença, uma vez por aluno/modalidade/dia. Matrículas futuras, expiradas ou suspensas não liberam entrada.

Também há entrada por matrícula, CPF ou nome completo. Nomes repetidos exigem CPF ou matrícula. O financeiro registra pagamentos e histórico em transação, impedindo confirmação duplicada.

## Celular antigo e câmera

A câmera exige **HTTPS válido** no celular. Um endereço como `http://192.168...:8080` permite a entrada por matrícula, mas não habilita a câmera. Publique a aplicação PHP com HTTPS ou configure um domínio/túnel HTTPS confiável para o servidor local. Configure `APP_URL` com o endereço público. Não ignore alertas de certificado. No navegador, permita a câmera quando solicitado.

Os modelos de reconhecimento estão incluídos em `public/vendor/face-api`, sem CDN em execução. O navegador processa uma imagem por vez, com resolução reduzida e pausa entre leituras. O desempenho depende do aparelho: valide com alunos reais, iluminação e o celular que ficará na recepção. Se o navegador não tiver os recursos necessários ou o aparelho estiver lento, use a matrícula.

O cadastro guarda **descritores faciais no IndexedDB do próprio navegador**, vinculados ao endereço do sistema. Não grava fotos e não envia descritores ao servidor. Limpar os dados do site, usar janela anônima, mudar o endereço ou trocar o celular exige novo cadastro. Em Biometria é possível excluir o cadastro de cada aluno. Registre autorização do aluno ou responsável. A entrada por matrícula permanece disponível.

Este modo é **supervisionado pela recepção**, não é controle físico de porta: não possui prova de vida certificada e fotos podem enganar a identificação. O navegador identifica o aluno; o backend exige sessão da equipe e CSRF, revalida pagamentos e registra a presença. Não deixe uma sessão administrativa desbloqueada para uso público sem supervisão.

## Hospedagem

Use `public/` como document root em um servidor PHP 8.2+ com PDO PostgreSQL. Execute `composer install --no-dev`, configure `.env`, rode `php bin/migrate.php` e crie o administrador com `php bin/create-admin.php`. Apache usa o `.htaccess` incluído; Nginx deve encaminhar rotas inexistentes para `index.php`. Não exponha `.env`, `.local`, `storage`, `app` ou o banco PostgreSQL.

Agende diariamente (cron ou Agendador de Tarefas), com as mesmas variáveis de ambiente da aplicação:

```sh
php bin/gerar-mensalidades.php
php bin/processar-vencimentos.php
```

A geração aceita competência explícita: `php bin/gerar-mensalidades.php 2026-11`. Ela cria cobranças para matrículas ativas que abrangem o mês, ajusta dias 29–31 ao último dia do mês e preserva cobranças existentes. A checagem de acesso considera o vencimento real mesmo antes de rodar o comando de vencimentos. Matrículas sem cobrança pendente não têm débito detectável: mantenha a geração agendada e importe pendências antigas antes do uso real. Mantenha backups do PostgreSQL e teste a restauração.

O `.env` existente apontava para um host Supabase sem resolução DNS nesta sessão. A conexão remota não foi alterada nem validada. O script local usa variáveis de ambiente apenas no processo para funcionar com um banco separado.

## Verificação

O código PHP segue PSR-12, com as regras em `.php-cs-fixer.dist.php`. JavaScript e CSS usam `.prettierrc.json`; `.editorconfig` mantém a mesma indentação nos editores. Ao alterar arquivos, execute PHP CS Fixer 3 e Prettier 3 sobre os arquivos modificados e rode os testes abaixo. Bibliotecas de terceiros em `public/vendor/` são preservadas.

```sh
php vendor/phpunit/phpunit/phpunit
node --test tests/facial-match.test.mjs
```

Integração real com PostgreSQL (cria e remove um schema isolado, sem alterar as tabelas do sistema):

```powershell
$env:TEST_DATABASE_DSN='pgsql:host=127.0.0.1;port=55432;dbname=academia_garagem_aco;sslmode=disable'
$env:TEST_DATABASE_USER='academia'
php tests/run-integration.php
```

O reconhecimento é implementado com [face-api.js](https://github.com/justadudewhohacks/face-api.js); versão de origem e licença estão na pasta do fornecedor. A exigência de HTTPS segue a [API de câmera do navegador](https://developer.mozilla.org/en-US/docs/Web/API/MediaDevices/getUserMedia).

## Interface e informações da academia

O redesign mantém preto, grafite e vermelho, com fontes Barlow e Barlow Condensed hospedadas localmente. A logo original não foi alterada. As fotos reais fornecidas pelo proprietário ficam em `public/assets/landing/academy-*.webp`, incluindo o retrato do mestre. Os arquivos anteriores do projeto foram preservados.

O endereço e o telefone confirmados pelo proprietário estão em `app/config/app.php`. As variáveis opcionais `CONTACT_ADDRESS`, `CONTACT_PHONE`, `CONTACT_PHONE_DIGITS`, `WHATSAPP_URL` e `GOOGLE_PROFILE_URL` permitem ajustá-los por ambiente. Os links de ligação, WhatsApp e mapa usam esses dados. Horários de funcionamento não foram inventados.

A grade fornecida na imagem foi transcrita para `app/config/initial-schedule.php` e importada no banco, com 14 aulas de segunda a quinta. O horário final é opcional e a aula de terça às 20h mantém a observação “Fundamentos de Boxe”. Para instalar a grade em outro ambiente:

```sh
php bin/migrate.php
php bin/import-schedule.php
```

A importação preserva grades que a equipe já tenha alterado. Depois da instalação, use **Modalidades → Editar** para manter os horários. O script de inicialização local já executa essa importação.

Os cenários de navegador estão em `tests/Browser/` e são executados pelo Playwright CLI. `responsive.js`, `interactions.js` e `accessibility.js` usam uma sessão autenticada da equipe; `public.js` usa uma sessão sem login. As capturas ficam em `.local/design-review/`. A auditoria de acessibilidade requer `npm install --prefix .local/audit axe-core --no-save`.

Veja `design-system/REVIEW.md` para os resultados e os limites da revisão visual.

## WhatsApp e cobrança mensal

O início local por `bin/start-local.ps1 -Port 8080` inicia PHP e o serviço Node.js do WhatsApp, incluindo o agendamento a cada minuto. Requer Node.js 22+ e Google Chrome instalado. O túnel HTTPS pode continuar apontando para a porta 8080; o serviço interno 3001 fica somente em loopback e não deve ser publicado.

1. Entre como administrador em `/whatsapp`, clique em conectar e leia o QR Code em **WhatsApp → Aparelhos conectados → Conectar aparelho** no telefone da academia.
2. No cadastro do aluno, informe o telefone com DDD e marque a autorização de cobrança pelo WhatsApp. Na matrícula, informe o dia do vencimento.
3. Ative a cobrança automática na tela WhatsApp e escolha a antecedência. Ela começa pausada para permitir conferir os cadastros.

O agendador gera as mensalidades e envia uma cobrança por mensalidade elegível, entre 9h e 20h no fuso configurado da aplicação. Revalida pagamento, matrícula e autorização imediatamente antes do envio. Cobranças antigas de competências anteriores não são disparadas automaticamente. O histórico mostra envio, falha e revisão. Quando a entrega é incerta, confira a conversa antes de autorizar uma nova tentativa: não há repetição automática nesses casos.

A sessão, o token e o diário de idempotência ficam em `storage/whatsapp/`, ignorados pelo Git. O computador precisa permanecer ligado e conectado. A integração usa `whatsapp-web.js`, não é um serviço oficial da Meta e pode exigir novo pareamento quando o WhatsApp Web mudar. A instalação atual tem alertas transitivos no `npm audit`; não foi aplicado o downgrade sugerido automaticamente. O envio real depende de parear a conta e não foi testado com mensagens a alunos.

Para outro servidor, instale `npm ci --prefix integrations/whatsapp`, execute as migrations e mantenha `node integrations/whatsapp/server.mjs` ativo com as mesmas variáveis de banco do PHP. O próprio serviço executa `bin/whatsapp-reminders.php`; evite outro agendador desnecessário. Configure `WHATSAPP_CHROME_PATH` se o navegador estiver em outro caminho.

## Facial no celular com HTTPS

O endereço configurado para o túnel é `https://kauan-test.totusone.com.br/`, encaminhado para a porta 8080. Cadastre os rostos e faça o check-in no mesmo navegador do celular que ficará na recepção: os descritores ficam somente naquele navegador e endereço. Cadastros feitos em localhost ou em outro aparelho não são compartilhados. Permita o uso da câmera e mantenha boa iluminação frontal.

Foi verificada a abertura da câmera física pelo HTTPS e o carregamento dos modelos locais. O teste completo de reconhecimento usou vídeo controlado com uma foto real e os modelos reais: bloqueou mensalidade atrasada, liberou após pagamento e impediu presença duplicada. Isso não comprova desempenho em todo celular antigo nem detecção de fraude por foto; utilize o terminal com supervisão.
