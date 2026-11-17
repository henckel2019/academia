<?php

declare(strict_types=1);

$appName = (string) app_config('app_name');
$user = $_SESSION['auth'] ?? [];
$role = (string) ($user['role'] ?? '');
$financeRoles = ['SUPER_ADMIN', 'ADMIN', 'FINANCE'];
$adminRoles = ['SUPER_ADMIN', 'ADMIN'];
$currentRoute = trim((string) ($path ?? $GLOBALS['path'] ?? ''), '/');

$navGroups = [
    [
        'label' => 'Visão geral',
        'items' => [
            ['route' => 'dashboard', 'label' => 'Visão geral', 'icon' => 'dashboard'],
        ],
    ],
    [
        'label' => 'Gestão acadêmica',
        'items' => [
            ['route' => 'alunos', 'label' => 'Alunos', 'icon' => 'users'],
            ['route' => 'modalidades', 'label' => 'Modalidades', 'icon' => 'dumbbell'],
            ['route' => 'turmas', 'label' => 'Aulas particulares', 'icon' => 'calendar'],
            ['route' => 'graduacoes', 'label' => 'Graduações', 'icon' => 'award'],
            ['route' => 'professores', 'label' => 'Professores', 'icon' => 'user-round'],
        ],
    ],
    [
        'label' => 'Operação',
        'items' => [
            ['route' => 'frequencia', 'label' => 'Frequência', 'icon' => 'clipboard'],
            ['route' => 'checkin', 'label' => 'Check-in', 'icon' => 'qr'],
            ['route' => 'biometria', 'label' => 'Cadastro facial', 'icon' => 'fingerprint'],
        ],
    ],
];

if (in_array($role, $financeRoles, true)) {
    $navGroups[] = [
        'label' => 'Financeiro',
        'items' => [
            ['route' => 'financeiro', 'label' => 'Financeiro', 'icon' => 'wallet'],
        ],
    ];
}

if (in_array($role, $adminRoles, true)) {
    $navGroups[] = [
        'label' => 'Administração',
        'items' => [
            ['route' => 'promocoes', 'label' => 'Promoções', 'icon' => 'tag'],
            ['route' => 'usuarios', 'label' => 'Usuários', 'icon' => 'users'],
            ['route' => 'whatsapp', 'label' => 'WhatsApp e cobranças', 'icon' => 'wallet'],
            ['route' => 'configuracoes', 'label' => 'Configurações', 'icon' => 'settings'],
        ],
    ];
}

$accessRoles = ['SUPER_ADMIN', 'ADMIN', 'RECEPTION'];
foreach ($navGroups as &$group) {
    $group['items'] = array_values(array_filter($group['items'], static function (array $item) use ($role, $accessRoles): bool {
        return match ($item['route']) {
            'alunos', 'turmas', 'checkin', 'biometria' => in_array($role, $accessRoles, true),
            'frequencia', 'graduacoes' => in_array($role, [...$accessRoles, 'INSTRUCTOR'], true),
            default => true,
        };
    }));
}
unset($group);
$currentTitle = 'Visão geral';
foreach ($navGroups as $group) {
    foreach ($group['items'] as $item) {
        if ($currentRoute === $item['route'] || str_starts_with($currentRoute, $item['route'] . '/')) {
            $currentTitle = $item['label'];
        }
    }
}

$userName = (string) ($user['name'] ?? 'Usuário');
$userInitial = mb_strtoupper(mb_substr($userName, 0, 1));
$roleNames = ['SUPER_ADMIN' => 'Administrador', 'ADMIN' => 'Administrador', 'RECEPTION' => 'Recepção', 'FINANCE' => 'Financeiro', 'INSTRUCTOR' => 'Professor'];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#090a0c">
    <title><?php echo isset($title) ? e($title) . ' — ' : ''; ?><?php echo e($appName); ?></title>
    <link rel="icon" href="<?= e(asset('/assets/logo-garagem-aco.png')) ?>">
    <link rel="stylesheet" href="<?= e(asset('/css/fonts.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('/css/tokens.css')) ?>">
    <link rel="stylesheet" href="<?php echo e(asset('/css/app.css')); ?>">
</head>
<body class="app-body">
    <a class="skip-link" href="#main-content">Pular para o conteúdo</a>
    <div class="app-shell" data-app-shell>
        <button class="sidebar-backdrop" type="button" data-sidebar-close aria-label="Fechar menu"></button>

        <aside class="sidebar" id="main-navigation" data-sidebar aria-label="Navegação principal">
            <div>
                <div class="sidebar-brand-row">
                    <a class="sidebar-brand" href="<?php echo e(url('/dashboard')); ?>" aria-label="<?php echo e($appName); ?> — dashboard">
                        <span class="sidebar-brand-mark"><img src="<?php echo e(asset('/assets/logo-garagem-aco.png')); ?>" alt="" width="54" height="54"></span>
                        <span class="brand-name" translate="no">GARAGEM<strong>AÇO</strong></span>
                    </a>
                    <button class="sidebar-close icon-button" type="button" data-sidebar-close aria-label="Fechar menu">
                        <?php echo icon('close'); ?>
                    </button>
                </div>

                <div class="sidebar-kicker">Centro de treinamento</div>

                <nav class="sidebar-nav">
                    <?php foreach ($navGroups as $group): ?>
                        <div class="nav-group">
                            <div class="nav-group-label"><?php echo e($group['label']); ?></div>
                            <?php foreach ($group['items'] as $item): ?>
                                <?php $isActive = $currentRoute === $item['route'] || str_starts_with($currentRoute, $item['route'] . '/'); ?>
                                <a class="nav-link<?php echo $isActive ? ' is-active' : ''; ?>" href="<?php echo e(url('/' . $item['route'])); ?>"<?php echo $isActive ? ' aria-current="page"' : ''; ?>>
                                    <?php echo icon($item['icon'], 'icon nav-link-icon'); ?>
                                    <span><?php echo e($item['label']); ?></span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                </nav>
            </div>

            <div class="sidebar-footer">
                <div class="sidebar-user">
                    <span class="avatar" aria-hidden="true"><?php echo e($userInitial); ?></span>
                    <span class="sidebar-user-copy"><strong><?php echo e($userName); ?></strong><small><?php echo e($roleNames[$role] ?? $role); ?></small></span>
                </div>
                <form method="post" action="<?php echo e(url('/logout')); ?>">
                    <input type="hidden" name="_csrf" value="<?php echo e(\Academia\Core\Csrf::token()); ?>">
                    <button type="submit" class="logout-button">
                        <?php echo icon('log-out', 'icon'); ?>
                        <span>Sair da conta</span>
                    </button>
                </form>
            </div>
        </aside>

        <main class="content">
            <header class="topbar">
                <div class="topbar-leading">
                    <button class="menu-trigger icon-button" type="button" data-sidebar-open aria-label="Abrir menu" aria-expanded="false" aria-controls="main-navigation">
                        <?php echo icon('menu'); ?>
                    </button>
                    <div>
                        <p class="topbar-kicker">Gestão da academia <span>/</span></p>
                        <span class="topbar-title"><?php echo e($currentTitle); ?></span>
                    </div>
                </div>
                <div class="topbar-actions">
                    <time class="topbar-date" datetime="<?= date('Y-m-d') ?>"><?= date('d.m.Y') ?></time>
                    <?php if (in_array($role, $accessRoles, true) && $currentRoute !== 'checkin'): ?>
                    <a class="btn btn-secondary" href="<?= e(url('/checkin')) ?>"><?= icon('qr') ?><span>Abrir check-in</span></a>
                    <?php endif ?>
                </div>
            </header>

            <div class="content-inner" id="main-content" tabindex="-1">
                <?php echo $content ?? ''; ?>
            </div>
        </main>
    </div>

    <script defer src="<?= e(asset('/js/interface.js')) ?>"></script>
</body>
</html>
