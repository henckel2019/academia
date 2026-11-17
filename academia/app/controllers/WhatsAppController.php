<?php

declare(strict_types=1);
use Academia\Integrations\WhatsAppGateway;
use Academia\Repositories\AuditRepository;
use Academia\Repositories\BillingRepository;
use Academia\Repositories\SettingsRepository;
use Academia\Repositories\WhatsAppBillingRepository;
use Academia\Services\WhatsAppGatewayException;

final class WhatsAppController
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function status(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
            abort(405, 'Método não permitido.');
        }
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store, private');
        try {
            $state = (new WhatsAppGateway())->status();
        } catch (WhatsAppGatewayException $error) {
            $state = ['state' => 'OFFLINE', 'error' => $error->getMessage(), 'qr' => null];
        }
        echo json_encode($state, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
    }

    public function index(): void
    {
        $settingsRepository = new SettingsRepository($this->pdo);
        $notice = $_SESSION['whatsapp_notice'] ?? null;
        unset($_SESSION['whatsapp_notice']);
        $error = null;
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            try {
                $userId = (int) $_SESSION['auth']['user_id'];
                switch ($_POST['acao'] ?? '') {
                    case 'conectar':
                        (new WhatsAppGateway())->connect();
                        $notice = 'Preparando o QR Code. Aguarde alguns segundos.';
                        break;
                    case 'desconectar':
                        (new WhatsAppGateway())->disconnect();
                        $notice = 'Conta desconectada. Os envios ficarão aguardando uma nova conexão.';
                        break;
                    case 'salvar':
                        $lead = filter_var($_POST['dias_antecedencia'] ?? null, FILTER_VALIDATE_INT);
                        if ($lead === false || $lead < 0 || $lead > 10) {
                            throw new InvalidArgumentException('Informe de 0 a 10 dias de antecedência.');
                        }
                        $values = ['whatsapp_cobranca_ativa' => isset($_POST['ativa']), 'whatsapp_dias_antecedencia' => $lead];
                        $settingsRepository->save($values, $userId);
                        (new AuditRepository($this->pdo))->record($userId, 'CONFIGURAR_COBRANCA_WHATSAPP', 'configuracoes', null, null, $values);
                        $notice = 'Configuração salva. O serviço verifica os vencimentos a cada minuto e envia entre 9h e 20h.';
                        break;
                    case 'preparar':
                        (new BillingRepository($this->pdo))->generate(date('Y-m'), $userId);
                        $settings = $settingsRepository->all();
                        $count = (new WhatsAppBillingRepository($this->pdo))->enqueue((int) ($settings['whatsapp_dias_antecedencia'] ?? 0));
                        $notice = "{$count} cobrança(s) adicionada(s) à fila. O envio ocorre quando a automação estiver ativa e o WhatsApp conectado.";
                        break;
                    case 'repetir':
                        (new WhatsAppBillingRepository($this->pdo))->retry((int) ($_POST['id'] ?? 0), $userId);
                        $notice = 'Cobrança devolvida à fila para uma nova tentativa.';
                        break;
                    default:
                        throw new InvalidArgumentException('Ação inválida.');
                }
                $_SESSION['whatsapp_notice'] = $notice;
                redirect('/whatsapp');
            } catch (InvalidArgumentException|WhatsAppGatewayException $exception) {
                $error = $exception->getMessage();
            }
        }
        $settings = $settingsRepository->all();
        $history = (new WhatsAppBillingRepository($this->pdo))->history();
        require __DIR__ . '/../views/whatsapp/index.php';
    }
}
