<?php

declare(strict_types=1);

namespace Academia\Integrations;

use Academia\Core\Env;
use Academia\Services\WhatsAppGatewayInterface;
use Academia\Services\WhatsAppGatewayException;

final class WhatsAppGateway implements WhatsAppGatewayInterface
{
    public function status(): array
    {
        return $this->request('/status');
    }

    public function connect(): array
    {
        return $this->request('/connect', []);
    }

    public function disconnect(): array
    {
        return $this->request('/disconnect', []);
    }

    public function send(string $key, string $phone, string $message): array
    {
        return $this->request('/send', ['key' => $key, 'phone' => $phone, 'message' => $message]);
    }

    private function request(string $path, ?array $body = null): array
    {
        $base = rtrim((string) Env::get('WHATSAPP_BRIDGE_URL', 'http://127.0.0.1:3001'), '/');
        $tokenFile = dirname(__DIR__, 2) . '/storage/whatsapp/bridge-token';
        $token = trim((string) Env::get('WHATSAPP_BRIDGE_TOKEN', is_file($tokenFile) ? file_get_contents($tokenFile) : ''));
        if ($token === '') {
            throw new WhatsAppGatewayException('O serviço WhatsApp ainda não foi iniciado.', false);
        }
        $context = stream_context_create(['http' => [
            'method' => $body === null ? 'GET' : 'POST', 'timeout' => 65, 'ignore_errors' => true,
            'header' => "Authorization: Bearer {$token}\r\nContent-Type: application/json\r\n",
            'content' => $body === null ? '' : json_encode($body, JSON_THROW_ON_ERROR),
        ]]);
        try {
            $response = file_get_contents($base . $path, false, $context);
            if ($response === false) {
                throw new \RuntimeException('Serviço indisponível.');
            }
            $result = json_decode($response, true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable $error) {
            throw new WhatsAppGatewayException('Não foi possível contatar o serviço WhatsApp. Verifique se ele está iniciado.');
        }
        if (!is_array($result)) {
            throw new WhatsAppGatewayException('Resposta inválida do WhatsApp.');
        }
        if (isset($result['error'])) {
            throw new WhatsAppGatewayException((string) $result['error'], (bool) ($result['uncertain'] ?? true));
        }
        return $result;
    }
}
