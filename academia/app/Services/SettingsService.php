<?php

declare(strict_types=1);

namespace Academia\Services;

use InvalidArgumentException;

final class SettingsService
{
    /** @param array<string, mixed> $input @return array<string, mixed> */
    public function validate(array $input): array
    {
        $settings = [
            'nome_academia' => trim((string) ($input['nome_academia'] ?? '')),
            'cnpj' => trim((string) ($input['cnpj'] ?? '')),
            'telefone' => trim((string) ($input['telefone'] ?? '')),
            'email' => trim((string) ($input['email'] ?? '')),
            'endereco' => trim((string) ($input['endereco'] ?? '')),
            'logo' => trim((string) ($input['logo'] ?? '')),
            'dias_tolerancia_financeira' => filter_var($input['dias_tolerancia_financeira'] ?? null, FILTER_VALIDATE_INT),
            'dia_padrao_vencimento' => filter_var($input['dia_padrao_vencimento'] ?? null, FILTER_VALIDATE_INT),
            'moeda' => strtoupper(trim((string) ($input['moeda'] ?? 'BRL'))),
            'timezone' => trim((string) ($input['timezone'] ?? 'America/Sao_Paulo')),
            'permitir_bloqueio_inadimplencia' => isset($input['permitir_bloqueio_inadimplencia']),
            'tempo_expiracao_sessao' => filter_var($input['tempo_expiracao_sessao'] ?? null, FILTER_VALIDATE_INT),
            'max_tentativas_login' => filter_var($input['max_tentativas_login'] ?? null, FILTER_VALIDATE_INT),
        ];
        if ($settings['nome_academia'] === '' || ($settings['email'] !== '' && filter_var($settings['email'], FILTER_VALIDATE_EMAIL) === false)) {
            throw new InvalidArgumentException('Informe o nome da academia e um e-mail válido quando preenchido.');
        }
        if ($settings['dias_tolerancia_financeira'] === false || $settings['dias_tolerancia_financeira'] < 0 || $settings['dias_tolerancia_financeira'] > 90 || $settings['dia_padrao_vencimento'] === false || $settings['dia_padrao_vencimento'] < 1 || $settings['dia_padrao_vencimento'] > 31 || $settings['tempo_expiracao_sessao'] === false || $settings['tempo_expiracao_sessao'] < 300 || $settings['max_tentativas_login'] === false || $settings['max_tentativas_login'] < 3 || $settings['max_tentativas_login'] > 20) {
            throw new InvalidArgumentException('Há configurações numéricas fora dos limites permitidos.');
        }
        if (!in_array($settings['moeda'], ['BRL'], true) || !in_array($settings['timezone'], timezone_identifiers_list(), true)) {
            throw new InvalidArgumentException('Moeda ou timezone inválidos.');
        }

        return $settings;
    }
}
