<?php

declare(strict_types=1);

namespace Academia\Services;

use Academia\Core\Csrf;
use Academia\Core\Request;
use Academia\Core\HttpException;
use Academia\Support\Role;

final class RouteAuthorizationService
{
    public static function enforce(string $route): void
    {
        $method = Request::method();
        if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            Csrf::verifyRequest();
        }

        if (preg_match('#/(excluir|inativar|ativar)/#', $route) === 1 && $method !== 'POST') {
            throw new HttpException(405, 'Exclusões exigem uma solicitação POST protegida.');
        }

        AuthorizationService::requireAuthenticated();
        if ($route === 'logout') {
            return;
        }

        if (str_starts_with($route, 'portal')) {
            AuthorizationService::requireAnyRole([Role::STUDENT]);
            return;
        }

        $roles = self::rolesFor($route);
        AuthorizationService::requireAnyRole($roles);
    }

    /** @return list<string> */
    private static function rolesFor(string $route): array
    {
        $isMutation = Request::method() !== 'GET' || preg_match('#/(novo|nova|criar|editar|excluir)/?#', $route) === 1;
        if (str_starts_with($route, 'whatsapp')) {
            return [Role::SUPER_ADMIN, Role::ADMIN];
        }
        if (str_starts_with($route, 'financeiro')) {
            return [Role::SUPER_ADMIN, Role::ADMIN, Role::FINANCE];
        }
        if (str_starts_with($route, 'usuarios') || str_starts_with($route, 'configuracoes')) {
            return [Role::SUPER_ADMIN, Role::ADMIN];
        }
        if (str_starts_with($route, 'biometria')) {
            return [Role::SUPER_ADMIN, Role::ADMIN, Role::RECEPTION];
        }
        if (str_starts_with($route, 'checkin')) {
            return [Role::SUPER_ADMIN, Role::ADMIN, Role::RECEPTION];
        }
        if (str_starts_with($route, 'frequencia') || str_starts_with($route, 'graduacoes')) {
            return [Role::SUPER_ADMIN, Role::ADMIN, Role::INSTRUCTOR, Role::RECEPTION];
        }
        if (str_starts_with($route, 'alunos') || str_starts_with($route, 'turmas')) {
            return [Role::SUPER_ADMIN, Role::ADMIN, Role::RECEPTION];
        }
        if (str_starts_with($route, 'modalidades') || str_starts_with($route, 'planos') || str_starts_with($route, 'professores') || str_starts_with($route, 'promocoes')) {
            return $isMutation ? [Role::SUPER_ADMIN, Role::ADMIN] : [Role::SUPER_ADMIN, Role::ADMIN, Role::FINANCE, Role::INSTRUCTOR, Role::RECEPTION];
        }

        return [Role::SUPER_ADMIN, Role::ADMIN, Role::FINANCE, Role::INSTRUCTOR, Role::RECEPTION];
    }
}
