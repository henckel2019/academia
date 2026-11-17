<?php

// app/routes/web.php
$route = $path ?? '';

if ($route === '') {
    $landingModalities = read_landing_schedule_cache();
    $landingHasDatabase = $pdo instanceof PDO || $landingModalities !== [] || is_file(landing_schedule_cache_file());
    if ($pdo instanceof PDO) {
        try {
            $landingRepository = new \Academia\Repositories\ModalityManagementRepository($pdo);
            $landingModalities = $landingRepository->publicSchedule();
            $cachePath = landing_schedule_cache_file();
            if (!is_dir(dirname($cachePath))) {
                mkdir(dirname($cachePath), 0775, true);
            }
            file_put_contents($cachePath, json_encode($landingModalities, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT), LOCK_EX);
        } catch (\Throwable) {
            $landingModalities = read_landing_schedule_cache();
        }
    }
    require __DIR__ . '/../views/landing/index.php';
    exit;
}

if (empty($pdo)) {
    http_response_code(500);
    echo 'Falta conexão com banco.';
    exit;
}

if ($route !== 'login') {
    \Academia\Services\RouteAuthorizationService::enforce($route);
}

if (str_starts_with($route, 'login') || $route === 'login') {
    require __DIR__ . '/../controllers/AuthController.php';
    (new AuthController($pdo))->login();
    exit;
}

if ($route === 'whatsapp' || $route === 'whatsapp/status') {
    require __DIR__ . '/../controllers/WhatsAppController.php';
    $controller = new WhatsAppController($pdo);
    $route === 'whatsapp/status' ? $controller->status() : $controller->index();
    exit;
}

if ($route === 'portal/dashboard') {
    require __DIR__ . '/../controllers/PortalController.php';
    (new PortalController($pdo))->dashboard();
    exit;
}

if ($route === 'logout') {
    require __DIR__ . '/../controllers/AuthController.php';
    (new AuthController($pdo))->logout();
    exit;
}

if ($route === 'dashboard') {
    require __DIR__ . '/../controllers/DashboardController.php';
    (new DashboardController($pdo))->index();
    exit;
}

if ($route === 'alunos') {
    require __DIR__ . '/../controllers/AlunoController.php';
    (new AlunoController($pdo))->index();
    exit;
}

if ($route === 'alunos/novo' || $route === 'alunos/criar') {
    require __DIR__ . '/../controllers/AlunoController.php';
    (new AlunoController($pdo))->create();
    exit;
}

if (preg_match('#^alunos/editar/(\d+)$#', $route, $m)) {
    require __DIR__ . '/../controllers/AlunoController.php';
    (new AlunoController($pdo))->edit((int) $m[1]);
    exit;
}

if (preg_match('#^alunos/(inativar|ativar|excluir)/(\d+)$#', $route, $m)) {
    require __DIR__ . '/../controllers/AlunoController.php';
    $action = (string) $m[1];
    (new AlunoController($pdo))->{$action}((int) $m[2]);
    exit;
}

if ($route === 'modalidades') {
    require __DIR__ . '/../controllers/ModalidadeController.php';
    (new ModalidadeController($pdo))->index();
    exit;
}

if ($route === 'modalidades/novo' || $route === 'modalidades/criar') {
    require __DIR__ . '/../controllers/ModalidadeController.php';
    (new ModalidadeController($pdo))->create();
    exit;
}

if (preg_match('#^modalidades/editar/(\d+)$#', $route, $m)) {
    require __DIR__ . '/../controllers/ModalidadeController.php';
    (new ModalidadeController($pdo))->edit((int) $m[1]);
    exit;
}

if (preg_match('#^modalidades/(inativar|ativar|excluir)/(\d+)$#', $route, $m)) {
    require __DIR__ . '/../controllers/ModalidadeController.php';
    $action = (string) $m[1];
    (new ModalidadeController($pdo))->{$action}((int) $m[2]);
    exit;
}

if ($route === 'turmas') {
    require __DIR__ . '/../controllers/TurmaController.php';
    (new TurmaController($pdo))->index();
    exit;
}

if ($route === 'turmas/novo' || $route === 'turmas/criar') {
    require __DIR__ . '/../controllers/TurmaController.php';
    (new TurmaController($pdo))->create();
    exit;
}

if (preg_match('#^turmas/editar/(\d+)$#', $route, $m)) {
    require __DIR__ . '/../controllers/TurmaController.php';
    (new TurmaController($pdo))->edit((int) $m[1]);
    exit;
}

if (preg_match('#^turmas/excluir/(\d+)$#', $route, $m)) {
    require __DIR__ . '/../controllers/TurmaController.php';
    (new TurmaController($pdo))->delete((int) $m[1]);
    exit;
}

if ($route === 'graduacoes') {
    require __DIR__ . '/../controllers/GraduacaoController.php';
    (new GraduacaoController($pdo))->index();
    exit;
}

if ($route === 'graduacoes/nova') {
    require __DIR__ . '/../controllers/GraduacaoController.php';
    (new GraduacaoController($pdo))->create();
    exit;
}

if ($route === 'frequencia') {
    require __DIR__ . '/../controllers/FrequenciaController.php';
    (new FrequenciaController($pdo))->index();
    exit;
}

if ($route === 'frequencia/novo') {
    require __DIR__ . '/../controllers/FrequenciaController.php';
    (new FrequenciaController($pdo))->create();
    exit;
}

if ($route === 'biometria') {
    require __DIR__ . '/../controllers/BiometriaController.php';
    (new BiometriaController($pdo))->index();
    exit;
}

if ($route === 'checkin') {
    require __DIR__ . '/../controllers/CheckinController.php';
    (new CheckinController($pdo))->index();
    exit;
}

if ($route === 'financeiro') {
    require __DIR__ . '/../controllers/FinanceiroController.php';
    (new FinanceiroController($pdo))->index();
    exit;
}

if ($route === 'configuracoes') {
    require __DIR__ . '/../controllers/ConfiguracaoController.php';
    (new ConfiguracaoController($pdo))->index();
    exit;
}

if ($route === 'promocoes') {
    require __DIR__ . '/../controllers/PromocaoController.php';
    (new PromocaoController($pdo))->index();
    exit;
}

if ($route === 'promocoes/nova') {
    require __DIR__ . '/../controllers/PromocaoController.php';
    (new PromocaoController($pdo))->create();
    exit;
}

if (preg_match('#^promocoes/editar/(\d+)$#', $route, $m)) {
    require __DIR__ . '/../controllers/PromocaoController.php';
    (new PromocaoController($pdo))->edit((int) $m[1]);
    exit;
}

if (preg_match('#^promocoes/excluir/(\d+)$#', $route, $m)) {
    require __DIR__ . '/../controllers/PromocaoController.php';
    (new PromocaoController($pdo))->delete((int) $m[1]);
    exit;
}

if ($route === 'usuarios') {
    require __DIR__ . '/../controllers/UsuarioController.php';
    (new UsuarioController($pdo))->index();
    exit;
}

if ($route === 'usuarios/novo') {
    require __DIR__ . '/../controllers/UsuarioController.php';
    (new UsuarioController($pdo))->create();
    exit;
}

if ($route === 'professores') {
    require __DIR__ . '/../controllers/ProfessorController.php';
    (new ProfessorController($pdo))->index();
    exit;
}

if ($route === 'professores/novo' || $route === 'professores/criar') {
    require __DIR__ . '/../controllers/ProfessorController.php';
    (new ProfessorController($pdo))->create();
    exit;
}

if (preg_match('#^professores/editar/(\d+)$#', $route, $m)) {
    require __DIR__ . '/../controllers/ProfessorController.php';
    (new ProfessorController($pdo))->edit((int) $m[1]);
    exit;
}

if (preg_match('#^professores/excluir/(\d+)$#', $route, $m)) {
    require __DIR__ . '/../controllers/ProfessorController.php';
    (new ProfessorController($pdo))->delete((int) $m[1]);
    exit;
}

http_response_code(404);
echo 'Página não encontrada.';
