<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();
        // Las APIs deben responder JSON; las paginas normales pueden redirigir al login.
        $isApiRequest = str_starts_with(trim($request->getUri()->getPath(), '/'), 'api/')
            || str_contains($request->getHeaderLine('Accept'), 'application/json')
            || str_contains($request->getHeaderLine('Content-Type'), 'application/json');

        if ($session->get('login')) {
            // Control de expiracion por inactividad; el valor viene de .env o Docker.
            $timeout = (int) env('app.sessionInactivityTimeout', 1800);
            $lastActivity = (int) $session->get('last_activity_at');

            if ($lastActivity > 0 && time() - $lastActivity > $timeout) {
                $session->destroy();

                return $this->unauthorized($isApiRequest, 'La sesion expiro por inactividad. Inicie sesion nuevamente.');
            }

            $session->set('last_activity_at', time());
        }

        if (! $session->get('login')) {
            return $this->unauthorized($isApiRequest, 'Debe iniciar sesion para continuar.');
        }

        if (! empty($arguments)) {
            $permissions = $session->get('permissions') ?? [];

            // Cada ruta puede pedir un permiso puntual: users.view, tickets.create, etc.
            foreach ($arguments as $permission) {
                if (! in_array($permission, $permissions, true)) {
                    if ($isApiRequest) {
                        return service('response')->setStatusCode(403)->setJSON([
                            'status' => false,
                            'message' => 'No tiene permisos para realizar esta accion.',
                        ]);
                    }

                    return redirect()->to('/main')->with('error', 'No tiene permisos para acceder a esta seccion.');
                }
            }
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }

    private function unauthorized(bool $isApiRequest, string $message)
    {
        if ($isApiRequest) {
            return service('response')->setStatusCode(401)->setJSON([
                'status' => false,
                'message' => $message,
            ]);
        }

        return redirect()->to('/dashboard')->with('error', $message);
    }
}
