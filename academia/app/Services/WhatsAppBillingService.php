<?php

declare(strict_types=1);

namespace Academia\Services;

use Academia\Repositories\SettingsRepository;
use Academia\Repositories\WhatsAppBillingRepository;
use Academia\Support\PhoneNumber;
use DateTimeImmutable;
use PDO;

final class WhatsAppBillingService
{
    private const SEND_START_HOUR = 9;
    private const SEND_END_HOUR = 20;

    public function __construct(private readonly PDO $pdo, private readonly WhatsAppGatewayInterface $gateway)
    {
    }

    public function run(?DateTimeImmutable $now = null): array
    {
        $settings = (new SettingsRepository($this->pdo))->all();
        if (!($settings['whatsapp_cobranca_ativa'] ?? false)) {
            return ['status' => 'PAUSADO', 'enviadas' => 0];
        }
        $now ??= new DateTimeImmutable();
        $today = $now->setTime(0, 0);
        $hour = (int) $now->format('G');
        if ($hour < self::SEND_START_HOUR || $hour >= self::SEND_END_HOUR) {
            return ['status' => 'FORA_DO_HORARIO', 'enviadas' => 0];
        }
        if (($this->gateway->status()['state'] ?? '') !== 'READY') {
            return ['status' => 'DESCONECTADO', 'enviadas' => 0];
        }
        // Impede que o agendador e uma execução manual enviem o mesmo lote ao mesmo tempo.
        if (!$this->pdo->query('SELECT pg_try_advisory_lock(739021,1)')->fetchColumn()) {
            return ['status' => 'EM_EXECUCAO', 'enviadas' => 0];
        }
        $sentCount = 0;
        $leadDays = max(0, min(10, (int) ($settings['whatsapp_dias_antecedencia'] ?? 0)));
        try {
            $repository = new WhatsAppBillingRepository($this->pdo);
            $repository->enqueue($leadDays);
            $academyName = (string) ($settings['nome_academia'] ?? 'Garagem Aço');
            foreach ($repository->pendingIds() as $reminderId) {
                if ($this->processReminder($repository, (int) $reminderId, $today, $leadDays, $academyName)) {
                    ++$sentCount;
                }
            }
        } finally {
            $this->pdo->query('SELECT pg_advisory_unlock(739021,1)');
        }
        return ['status' => 'CONCLUIDO', 'enviadas' => $sentCount];
    }

    private function processReminder(
        WhatsAppBillingRepository $repository,
        int $reminderId,
        DateTimeImmutable $today,
        int $leadDays,
        string $academyName
    ): bool {
        $this->pdo->beginTransaction();
        try {
            // Pagamento e autorização ficam bloqueados até a confirmação do envio.
            $bill = $repository->lock($reminderId);
            $lastDueDate = $today->modify("+{$leadDays} days")->format('Y-m-d');
            if (!$bill || $bill['status'] !== 'PENDENTE' || $bill['data_vencimento'] > $lastDueDate) {
                $this->pdo->commit();
                return false;
            }

            $sent = false;
            if ($this->isEligible($bill, $today)) {
                $sent = $this->sendReminder($repository, $bill, $academyName);
            } else {
                $repository->finish($reminderId, 'CANCELADA', error: 'Pagamento, matrícula ou preferência de contato alterada.');
            }
            $this->pdo->commit();
            return $sent;
        } catch (\InvalidArgumentException $error) {
            $repository->finish($reminderId, 'FALHA', error: mb_substr($error->getMessage(), 0, 255));
            $this->pdo->commit();
            return false;
        } catch (\Throwable $error) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $error;
        }
    }

    private function sendReminder(WhatsAppBillingRepository $repository, array $bill, string $academyName): bool
    {
        $reminderId = (int) $bill['id'];
        $phone = PhoneNumber::brazil((string) $bill['current_phone']);
        $message = BillingReminderMessage::compose($bill, $academyName);
        try {
            $response = $this->gateway->send((string) $bill['chave_envio'], $phone, $message);
            if (empty($response['messageId'])) {
                throw new WhatsAppGatewayException('Envio sem confirmação. Confira a conversa antes de repetir.');
            }
            $repository->finish($reminderId, 'ENVIADA', $phone, $message, externalId: (string) $response['messageId']);
            return true;
        } catch (WhatsAppGatewayException $error) {
            $status = $error->uncertain ? 'REVISAO' : 'FALHA';
            $repository->finish($reminderId, $status, $phone, $message, mb_substr($error->getMessage(), 0, 255));
            return false;
        }
    }

    private function isEligible(array $bill, DateTimeImmutable $today): bool
    {
        $date = $today->format('Y-m-d');
        $hasActiveEnrollment = $bill['enrollment_status'] === 'ATIVA'
            && $bill['data_inicio'] <= $date
            && ($bill['data_fim'] === null || $bill['data_fim'] >= $date);

        return in_array($bill['bill_status'], ['ABERTA', 'VENCIDA'], true) && $bill['student_status'] === 'ATIVO'
            && $hasActiveEnrollment && $bill['deleted_at'] === null
            && filter_var($bill['whatsapp_cobranca'], FILTER_VALIDATE_BOOL)
            && $bill['competencia'] >= $today->format('Y-m');
    }
}
