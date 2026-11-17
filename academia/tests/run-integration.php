<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use Academia\Repositories\BillingRepository;
use Academia\Repositories\CheckinRepository;
use Academia\Repositories\SettingsRepository;
use Academia\Services\BillingService;
use Academia\Services\CheckinService;

$dsn = getenv('TEST_DATABASE_DSN');
if (!$dsn) {
    fwrite(STDERR, "Defina TEST_DATABASE_DSN apontando para um PostgreSQL de testes.\n");
    exit(1);
}
$pdo = new PDO($dsn, getenv('TEST_DATABASE_USER') ?: 'academia', getenv('TEST_DATABASE_PASSWORD') ?: '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false, PDO::ATTR_STRINGIFY_FETCHES => true,
]);
$schema = 'test_academia_' . bin2hex(random_bytes(6));
$checks = 0;
function verify(bool $condition, string $message): void
{
    global $checks;
    if (!$condition) {
        throw new RuntimeException($message);
    }
    ++$checks;
}
$pdo->exec('CREATE SCHEMA ' . $schema);
try {
    $pdo->exec('SET search_path TO ' . $schema);
    $pdo->exec(file_get_contents(dirname(__DIR__) . '/database/schema.sql'));
    foreach (glob(dirname(__DIR__) . '/database/migrations/*.php') as $file) {
        if (!str_contains($file, 'initial_schema')) {
            (require $file)($pdo);
        }
    }
    $pdo->exec("INSERT INTO usuarios (nome,email,senha_hash,perfil_id) SELECT 'Operador teste','integration@example.test','unused',id FROM perfis WHERE nome='SUPER_ADMIN'");
    $userId = (int) $pdo->query('SELECT id FROM usuarios')->fetchColumn();
    $pdo->exec("INSERT INTO alunos (nome,cpf,matricula_codigo) VALUES ('Aluno teste','123.456.789-00','GA-TEST')");
    $studentId = (int) $pdo->query('SELECT id FROM alunos')->fetchColumn();
    $pdo->exec("INSERT INTO modalidades (nome) VALUES ('Jiu-jitsu')");
    require_once dirname(__DIR__) . '/app/models/BaseModel.php';
    $legacyModel = new BaseModel($pdo, 'professores');
    $teacherId = $legacyModel->create(['nome' => 'Professor teste', 'ativo' => true]);
    verify($teacherId > 0, 'Legacy creation returns the PostgreSQL identifier');
    $legacyModel->update($teacherId, ['ativo' => false]);
    verify((int) $pdo->query('SELECT COUNT(*) FROM professores WHERE ativo=FALSE')->fetchColumn() === 1, 'Deactivation binds PostgreSQL booleans correctly');
    $pdo->exec("INSERT INTO planos (nome,valor,dia_vencimento) VALUES ('Mensal',150,31)");
    $pdo->exec("INSERT INTO matriculas (aluno_id,modalidade_id,plano_id,valor_plano,dia_vencimento,data_inicio)
        SELECT a.id,md.id,p.id,150,31, CURRENT_DATE - INTERVAL '2 years' FROM alunos a CROSS JOIN modalidades md CROSS JOIN planos p");
    $enrollmentId = (int) $pdo->query('SELECT id FROM matriculas')->fetchColumn();
    $repository = new BillingRepository($pdo);
    $billing = new BillingService($repository);
    verify($billing->generate('2026-02', $userId) === 1, 'Generates a monthly invoice');
    verify($billing->generate('2026-02', $userId) === 0, 'Repeated generation must not duplicate bills');
    verify($pdo->query('SELECT data_vencimento FROM mensalidades')->fetchColumn() === '2026-02-28', 'Clamps day 31 to the end of February');
    $oldBill = (int) $pdo->query('SELECT id FROM mensalidades')->fetchColumn();
    $pdo->exec("UPDATE mensalidades SET data_vencimento=CURRENT_DATE - INTERVAL '20 days'");
    $billing->generate(date('Y-m'), $userId);
    $newBill = (int) $pdo->query('SELECT MAX(id) FROM mensalidades')->fetchColumn();
    verify($billing->settle($newBill, 'PIX', $userId), 'Confirms a received payment');
    verify(!$billing->settle($newBill, 'PIX', $userId), 'Repeated payment confirmation is idempotent');
    verify((int) $pdo->query('SELECT COUNT(*) FROM pagamentos')->fetchColumn() === 1, 'Only one payment is recorded');
    $checkinRepository = new CheckinRepository($pdo);
    $checkin = new CheckinService($checkinRepository, new SettingsRepository($pdo));
    verify(!$checkin->checkin('GA-TEST', null, $userId, 'WEB')['allowed'], 'Old debt blocks access despite a newer paid invoice');
    verify((int) $pdo->query('SELECT COUNT(*) FROM frequencias')->fetchColumn() === 0, 'Blocked access must not register attendance');
    $billing->settle($oldBill, 'DINHEIRO', $userId);
    verify($checkin->checkin('12345678900', null, $userId, 'WEB')['allowed'], 'Payment releases the student and accepts unformatted CPF');
    verify($checkin->checkin('', $studentId, $userId, 'BIOMETRIA')['allowed'], 'Facial check-in reuses the same server-side financial policy');
    verify((int) $pdo->query('SELECT COUNT(*) FROM frequencias')->fetchColumn() === 1, 'Manual and facial requests do not duplicate attendance');
    $pdo->exec("UPDATE matriculas SET situacao='SUSPENSA'");
    verify(!$checkin->checkin('GA-TEST', null, $userId, 'WEB')['allowed'], 'Suspended enrollment blocks entry even when paid');
    $pdo->exec("UPDATE matriculas SET situacao='ATIVA', data_inicio=CURRENT_DATE + INTERVAL '1 day'");
    verify(!$checkin->checkin('GA-TEST', null, $userId, 'WEB')['allowed'], 'Future enrollment cannot enter before its start date');
    $pdo->exec("INSERT INTO alunos (nome,matricula_codigo) VALUES ('Aluno teste','GA-DUPLICATE')");
    verify($checkinRepository->findByIdentifier('Aluno teste') === null, 'Ambiguous names must not select an arbitrary student');
    verify((int) $pdo->query('SELECT COUNT(*) FROM financeiro_historico')->fetchColumn() === 2, 'Payment history is recorded for each confirmed invoice');
    $modalityRepository = new \Academia\Repositories\ModalityManagementRepository($pdo);
    $modalityService = new \Academia\Services\ModalityManagementService($modalityRepository);
    $modalityId = $modalityService->create(['nome' => 'Muay Thai', 'horarios' => [
        ['dia_semana' => 2, 'horario_inicio' => '20:00', 'observacao' => 'Fundamentos de Boxe'],
    ]]);
    $lesson = $modalityRepository->find($modalityId)['schedules'][0];
    verify($lesson['horario_fim'] === null, 'A published start-only schedule does not invent a finishing time');
    verify($lesson['observacao'] === 'Fundamentos de Boxe', 'Lesson notes survive storage and retrieval');
    $pdo->exec("UPDATE alunos SET whatsapp_cobranca=TRUE,telefone='(41) 99999-0000' WHERE id={$studentId}");
    $pdo->exec("UPDATE matriculas SET situacao='ATIVA',data_inicio=CURRENT_DATE WHERE id={$enrollmentId}");
    $pdo->exec("UPDATE mensalidades SET status='ABERTA',data_pagamento=NULL,data_vencimento=CURRENT_DATE WHERE id={$newBill}");
    $gateway = new class () implements \Academia\Services\WhatsAppGatewayInterface {
        public array $calls = [];
        public bool $uncertain = false;

        public function status(): array
        {
            return ['state' => 'READY'];
        }

        public function send(string $key, string $phone, string $message): array
        {
            $this->calls[] = [$key, $phone, $message];
            if ($this->uncertain) {
                throw new \Academia\Services\WhatsAppGatewayException('Simulated uncertain delivery', true);
            }
            return ['messageId' => 'test-message-' . count($this->calls)];
        }
    };
    $whatsappRepository = new \Academia\Repositories\WhatsAppBillingRepository($pdo);
    $whatsappService = new \Academia\Services\WhatsAppBillingService($pdo, $gateway);
    $noon = new DateTimeImmutable('today 12:00');
    verify($whatsappService->run($noon)['status'] === 'PAUSADO', 'WhatsApp automation remains paused until enabled');
    $pdo->exec("UPDATE configuracoes SET valor='true'::jsonb WHERE chave='whatsapp_cobranca_ativa'");
    verify($whatsappRepository->enqueue(0) === 1, 'Queues the due monthly bill for an opted-in student');
    verify($whatsappRepository->enqueue(0) === 0, 'Repeated preparation does not duplicate reminders');
    verify($whatsappService->run($noon)['enviadas'] === 1, 'Delivers one reminder through the gateway');
    verify($whatsappService->run($noon)['enviadas'] === 0 && count($gateway->calls) === 1, 'Repeated scheduled ticks do not send twice');
    verify($gateway->calls[0][1] === '5541999990000', 'WhatsApp destination includes country and area code');
    $outboxId = (int) $pdo->query('SELECT id FROM whatsapp_cobrancas')->fetchColumn();
    $pdo->exec("UPDATE whatsapp_cobrancas SET status='PENDENTE' WHERE id={$outboxId}");
    $pdo->exec("UPDATE mensalidades SET status='PAGA',data_pagamento=CURRENT_DATE WHERE id={$newBill}");
    $whatsappService->run($noon);
    verify(count($gateway->calls) === 1 && $pdo->query('SELECT status FROM whatsapp_cobrancas')->fetchColumn() === 'CANCELADA', 'A payment recorded after queuing cancels the send');
    $pdo->exec("UPDATE mensalidades SET status='ABERTA',data_pagamento=NULL WHERE id={$newBill}");
    $pdo->exec("UPDATE whatsapp_cobrancas SET status='PENDENTE' WHERE id={$outboxId}");
    $pdo->exec("UPDATE alunos SET whatsapp_cobranca=FALSE WHERE id={$studentId}");
    $whatsappService->run($noon);
    verify(count($gateway->calls) === 1, 'Opting out after queuing prevents sending');
    $pdo->exec("UPDATE alunos SET whatsapp_cobranca=TRUE WHERE id={$studentId}");
    $pdo->exec("UPDATE whatsapp_cobrancas SET status='PENDENTE' WHERE id={$outboxId}");
    $gateway->uncertain = true;
    $whatsappService->run($noon);
    verify($pdo->query('SELECT status FROM whatsapp_cobrancas')->fetchColumn() === 'REVISAO', 'Unconfirmed delivery requires review');
    $whatsappService->run($noon);
    verify(count($gateway->calls) === 2, 'Unconfirmed delivery is never retried automatically');
    verify($whatsappService->run(new DateTimeImmutable('today 22:00'))['status'] === 'FORA_DO_HORARIO', 'Messages are not sent overnight');
    echo "OK: {$checks} verificações de integração PostgreSQL.\n";
} finally {
    $pdo->exec('SET search_path TO public');
    $pdo->exec('DROP SCHEMA ' . $schema . ' CASCADE');
}
