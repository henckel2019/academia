# Status das Implementações

| # | Título | Data | Status | Arquivos Afetados |
|---|---|---|---|---|
| 0010 | Cobrança automática de graduação | 2026-08-31 | Concluido | `database/migrations/20260831_000004_graduation_charges.php`, `app/Repositories/GraduationManagementRepository.php`, `app/Services/GraduationManagementService.php`, `app/controllers/GraduacaoController.php`, `app/views/graduacoes/`, `app/Repositories/StudentPortalRepository.php`, `app/views/portal/dashboard.php` |
| 0009 | Otimização de carregamento e cache | 2026-08-31 | Concluido | `app/bootstrap.php`, `app/config/database.php`, `app/routes/web.php`, `app/Repositories/ModalityManagementRepository.php`, `public/router.php`, `public/.htaccess`, `public/css/landing.css` |
| 0008 | Turmas reservadas para aulas particulares | 2026-08-31 | Concluido | `app/views/layouts/main.php`, `app/views/dashboard/index.php`, `app/views/turmas/`, `app/controllers/TurmaController.php` |
| 0007 | Modalidades e grade semanal publicada | 2026-08-31 | Concluido | `database/migrations/20260831_000003_modality_schedules.php`, `app/Repositories/ModalityManagementRepository.php`, `app/Services/ModalityManagementService.php`, `app/controllers/ModalidadeController.php`, `app/views/modalidades/`, `app/views/landing/index.php`, `public/css/app.css`, `public/css/landing.css` |
| 0006 | Dashboard operacional e financeiro mensal | 2026-08-31 | ConcluÃ­do | `app/Repositories/DashboardRepository.php`, `app/Repositories/DashboardRepositoryInterface.php`, `app/Services/DashboardService.php`, `app/controllers/DashboardController.php`, `app/views/dashboard/index.php`, `app/helpers.php`, `public/css/app.css`, `tests/Unit/DashboardServiceTest.php` |
| 0001 | Landing page pública Garagem Aço | 2026-08-31 | Concluído | `app/bootstrap.php`, `public/index.php`, `app/routes/web.php`, `app/views/landing/index.php`, `public/css/landing.css`, `public/assets/` |
| 0002 | Fotos realistas, animações, portal e suporte a túnel | 2026-08-31 | Concluído | `app/views/landing/index.php`, `public/css/landing.css`, `public/assets/landing/`, `app/Repositories/StudentPortalRepository.php`, `app/controllers/PortalController.php`, `app/views/portal/dashboard.php`, `app/Services/RouteAuthorizationService.php`, `app/routes/web.php`, `app/views/auth/login.php`, `public/css/app.css`, `app/bootstrap.php`, `.env` |
| 0003 | Suporte a PostgreSQL remoto do Supabase | 2026-08-31 | Concluído | `app/config/database.php`, `app/bootstrap.php`, `.env.example` |
| 0004 | Reformulação visual do painel e portal | 2026-08-31 | Concluído | `app/helpers.php`, `app/views/layouts/main.php`, `app/views/auth/`, `app/views/errors/`, `app/views/dashboard/`, `app/views/portal/`, `app/views/alunos/`, `app/views/modalidades/`, `app/views/turmas/`, `app/views/graduacoes/`, `app/views/professores/`, `app/views/promocoes/`, `app/views/frequencia/`, `app/views/usuarios/`, `app/views/configuracoes/`, `app/views/financeiro/`, `app/views/checkin/`, `app/views/biometria/`, `public/css/app.css` |
| 0005 | Logo compacta e sidebar responsiva | 2026-08-31 | Concluído | `app/views/layouts/main.php`, `app/views/auth/login.php`, `app/views/errors/error.php`, `app/helpers.php`, `public/css/app.css` |

## 2026-11-09 — retomada da operação web

- Financeiro com geração idempotente de mensalidades e confirmação manual de pagamentos, auditoria e histórico transacional.
- Check-in consulta a dívida mais antiga, bloqueia matrículas fora da vigência e evita presença duplicada por aluno/modalidade/dia.
- Cadastro facial com três amostras, autorização, exclusão e descritores no IndexedDB do celular; modelos locais, sem fotos no servidor. Uso supervisionado, sem prova de vida certificada.
- Inicialização local: `bin/start-local.ps1`, PostgreSQL na porta 55432 e credenciais aleatórias em `.local/acesso-local.txt`, sem alterar `.env` remoto.
- Correções de redirecionamento, booleans PostgreSQL, origem da frequência e criação de usuários sem senha padrão.
- Verificado: 26 testes PHPUnit / 78 assertions, 17 verificações reais no PostgreSQL, 3 testes JavaScript e lint PHP/JavaScript/PowerShell.
- Pendente de validação externa: câmera e desempenho no celular real, HTTPS de hospedagem e conexão Supabase. O navegador negou permissão à inspeção local; a revisão visual não foi realizada.

## 2026-11-09 — redesign completo e material do proprietário

- Paleta e logo preservadas; site público, login, painel, formulários, financeiro, portal e presença redesenhados com Barlow/Barlow Condensed locais e tokens compartilhados.
- Fotos reais da pasta fornecida pelo usuário, com destaque ao mestre. Endereço confirmado: Rua Alexandre Toscani Filho, 101, Santa Felicidade, Curitiba; telefone (41) 99921-3112 com ligação, WhatsApp e mapa.
- Grade de 14 aulas importada da imagem para o banco e editável em Modalidades. Fim opcional e observação de Fundamentos de Boxe preservada na terça às 20h.
- Playwright: 65 combinações de telas internas/larguras, 10 de página pública/login e 15 verificações de interação. Auditoria axe sem violações detectadas nas páginas examinadas.
- 27 testes PHP / 82 assertions, 19 verificações PostgreSQL e 3 testes de comparação facial passaram. Logo conferida por SHA-256.
- Gate Taste: OPEN, 31 apontamentos após três rodadas. Limites, exceções e lista de apontamentos estão em `design-system/REVIEW.md`; não foi declarado aprovação integral do checker estético.

## 2026-11-09 — WhatsApp e validação facial HTTPS

- Integração WhatsApp Web com QR, sessão local protegida e acesso administrativo. Cadastro do aluno com telefone e autorização de cobrança; vencimento na matrícula.
- Agendamento mensal, revalidação de pagamento e autorização, fila idempotente e revisão explícita de entregas incertas. Automação inicialmente pausada; nenhum envio real a alunos realizado.
- Inicialização local mantém porta 8080 e inicia o serviço Node e seu agendador. Túnel HTTPS do proprietário validado no navegador, câmera física abriu e modelos locais carregaram.
- Reconhecimento com modelos reais e vídeo controlado verificou bloqueio por atraso, liberação após pagamento e prevenção de presença duplicada. Fixture removida após a verificação. Desempenho no celular antigo e entrega real de mensagens dependem do aparelho e do pareamento da conta.
- Verificação final: 30 testes PHP, 90 assertions, 30 verificações PostgreSQL e 5 testes Node aprovados. Tela WhatsApp sem overflow nas larguras examinadas, HTTPS seguro e PHP permanece disponível em 8080.
- Dependência não oficial whatsapp-web.js com alertas transitivos de npm audit documentados no README. Credenciais, sessão, token e diário não são versionados.

## 2026-11-16 — revisão de legibilidade e publicação

- Padronizada a formatação dos arquivos PHP, JavaScript e CSS alterados, com configurações de estilo versionadas e EditorConfig.
- Processamento de cobranças separado em seleção do lote, transação por cobrança e envio. Nomes deixam explícitos a antecedência, o contador de envios e a matrícula ativa; comentários explicam os bloqueios que evitam concorrência.
- Após a revisão, passaram 30 testes PHP (90 assertions), 30 verificações reais em schema PostgreSQL isolado e 5 testes JavaScript.
- Credenciais, banco local, sessão do WhatsApp e gravações pessoais não fazem parte da publicação.

_Atualizado durante as implementações._
