# Academia Garagem Aço

## Visão Geral

Sistema web de gestão da Academia Garagem Aço. Engloba painel administrativo, áreas de recepção, professores e financeiro, portal do aluno e check-in web. Não há aplicativo Android nem APK como parte do produto.

## Stack Técnica

- PHP 8.2+ com PDO e Composer.
- PostgreSQL 17+ como banco de dados único.
- Views PHP renderizadas no servidor, CSS e JavaScript progressivo no diretório `public/`.
- PHPUnit para testes automatizados.

## Estrutura do Projeto

- `app/Core/` — bootstrap, configuração, sessão, roteamento, respostas HTTP e tratamento de erros.
- `app/Controllers/` — bordas HTTP; autorizam, validam a requisição e delegam aos serviços.
- `app/Services/` — regras de negócio e transações.
- `app/Repositories/` — consultas PDO e persistência.
- `app/Models/` — modelos legados a serem substituídos gradualmente por repositórios e DTOs explícitos.
- `app/Support/` — utilitários de domínio sem estado, validação e value objects.
- `app/views/` — templates PHP sem regras de negócio.
- `database/migrations/` — migrations PostgreSQL idempotentes, executadas por `bin/migrate.php`.
- `bin/` — comandos portáveis para cron Linux e Agendador de Tarefas do Windows.
- `tests/` — testes PHPUnit.

## Como Rodar

1. Copie `.env.example` para `.env` e informe credenciais locais do PostgreSQL.
2. Instale dependências: `composer install`.
3. Crie o banco indicado no `.env` e execute `php bin/migrate.php`.
4. Crie o primeiro administrador com `php bin/create-admin.php`.
5. Configure o servidor web com `public/` como document root e encaminhe rotas inexistentes para `public/index.php`.

## Como Fazer Deploy

Use `public/` como document root. No deploy, forneça variáveis de ambiente reais, execute migrations antes de publicar a nova versão e agende os comandos de vencimentos e geração de mensalidades. Nunca publique `.env`, `storage/` nem credenciais.

O PostgreSQL pode ficar no Supabase usando a connection string exibida em `Connect` e `DB_SSLMODE=require`. A aplicação PHP continua precisando de um servidor com runtime PHP; um frontend separado no Cloudflare Pages exigiria uma API nova e uma migração de autenticação.

## Arquitetura e Decisões Técnicas

- A grade pública de modalidades é mantida em `modalidade_horarios` e publicada pela landing; turmas são reservadas para aulas particulares.
- Graduações registradas com valor geram cobranças avulsas do tipo `GRADUACAO`, vinculadas à matrícula do aluno.
- A landing usa cache público invalidado quando a grade de modalidades é alterada; arquivos estáticos têm cache de longo prazo.

- O backend permanece em PHP/PDO; o acesso a PostgreSQL é centralizado em repositories.
- Sessões de navegador são a autenticação oficial. APIs de equipamentos usam credenciais próprias, nunca a sessão do usuário.
- Autorizações são verificadas no backend e em nível de recurso; esconder links não é controle de acesso.
- Valores monetários são strings decimalizadas em PHP e `NUMERIC(12,2)` no PostgreSQL. Nunca usar `float` para dinheiro.
- Exclusões de registros operacionais são substituídas por desativação ou cancelamento auditável.
- Tokens QR e tokens de equipamentos são aleatórios, armazenados somente como hash e podem ser revogados.

## Arquitetura de Camadas

| Camada | Pasta | Responsabilidade | Proibido |
|---|---|---|---|
| Core | `app/Core/` | Bootstrap e infraestrutura HTTP/configuração | Regras de negócio de academia |
| Controllers | `app/Controllers/` | Receber HTTP, aplicar autorização e chamar serviços | SQL e regras de negócio |
| Services | `app/Services/` | Regras, transações e políticas | Ler `$_POST`, renderizar view ou SQL direto |
| Repositories | `app/Repositories/` | Consultas parametrizadas e mapeamento de persistência | Regras de negócio ou HTTP |
| Support | `app/Support/` | Validação e utilitários puros | Acesso ao banco e estado HTTP |
| Views | `app/views/` | Renderizar dados já autorizados e escapados | SQL, mutação de dados e autorização |

Dependências permitidas: Controller → Service → Repository. Core pode ser usado nas bordas. Views recebem somente dados preparados por controllers e usam helpers de escaping.

## Testes

| Item | Valor |
|---|---|
| Framework | PHPUnit 11 |
| Rodar tudo | `composer test` |
| Rodar um arquivo | `vendor/bin/phpunit tests/Unit/NomeDoTeste.php` |
| Cobertura | `vendor/bin/phpunit --coverage-text` |
| Local e nome | `tests/**/*Test.php` |
| Meta | 100% dos fluxos críticos novos de autenticação, acesso e financeiro |

## Regras e Convenções

- PHP com `declare(strict_types=1)`, PSR-12 e classes namespaced sob `Academia`.
- Toda mutação HTTP requer método seguro (POST/PUT/PATCH/DELETE) e token CSRF válido.
- Todos os valores externos são validados; toda saída HTML passa por `e()`.
- Toda alteração financeira relevante e override de capacidade gera auditoria.
- Somente migrations alteram estrutura de banco; não editar banco manualmente em produção.

## Restrições e Cuidados

- Não implementar APK, Android nativo ou Kotlin.
- Não armazenar imagens ou templates biométricos no servidor. A pedido do usuário, o modo facial web guarda descritores apenas no IndexedDB do celular, mediante autorização e com exclusão disponível. Não usar como controle físico sem prova de vida.
- Não inserir senha padrão, token ou credencial no Git.
- Não integrar PIX ou biometria de fornecedor sem contrato, credenciais e validação do protocolo.

## Implementações

Veja `.claude/implements/status.md` para o histórico e `.claude/specs/index.md` para o planejamento.
