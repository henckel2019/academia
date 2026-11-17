<?php

declare(strict_types=1);
require dirname(__DIR__) . '/app/bootstrap.php';
// The persistent WhatsApp process invokes this every minute; the database decides whether it is due.
$settings = (new \Academia\Repositories\SettingsRepository($pdo))->all();
if (!($settings['whatsapp_cobranca_ativa'] ?? false)) {
    echo "Cobranças automáticas pausadas.\n";
    exit;
}
$billing = new \Academia\Repositories\BillingRepository($pdo);
$billing->generate(date('Y-m'), null);
$future = (new DateTimeImmutable('today'))->modify('+' . max(0, min(10, (int) ($settings['whatsapp_dias_antecedencia'] ?? 0))) . ' days')->format('Y-m');
if ($future !== date('Y-m')) {
    $billing->generate($future, null);
}
$billing->markOverdue();
try {
    $result = (new \Academia\Services\WhatsAppBillingService($pdo, new \Academia\Integrations\WhatsAppGateway()))->run();
    echo json_encode($result, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE) . PHP_EOL;
} catch (\Academia\Services\WhatsAppGatewayException $error) {
    fwrite(STDERR, $error->getMessage() . PHP_EOL);
    exit(1);
}
