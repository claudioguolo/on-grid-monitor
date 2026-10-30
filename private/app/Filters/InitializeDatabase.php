<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/** Initial schema installation only; existing databases are never rebuilt. */
class InitializeDatabase implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (!filter_var(env('DB_AUTO_MIGRATE', true), FILTER_VALIDATE_BOOLEAN)) {
            return null;
        }

        $db = null;
        $locked = false;
        $lockName = '';
        try {
            $db = db_connect();
            if (!array_diff(['readings','users','system_settings','admin_audit'], $db->listTables(false))) {
                return null;
            }
            $lockName = 'energia-init-' . sha1($db->getDatabase());
            $locked = (int) $db->query('SELECT GET_LOCK(?, 10) AS acquired', [$lockName])->getRowArray()['acquired'] === 1;
            if (!$locked) {
                throw new \RuntimeException('Inicializacao em andamento; tente novamente');
            }
            // Re-check after acquiring the lock, bypassing the connection cache.
            if (array_diff(['readings','users','system_settings','admin_audit'], $db->listTables(false))) {
                if (!service('migrations')->setNamespace('App')->latest()) {
                    throw new \RuntimeException('Nao foi possivel executar a migration inicial');
                }
                if (array_diff(['readings','users','system_settings','admin_audit'], $db->listTables(false))) {
                    throw new \RuntimeException('Estrutura inicial nao encontrada apos migration');
                }
            }
        } catch (\Throwable $exception) {
            log_message('error', 'Falha na inicializacao do banco: {message}', ['message' => $exception->getMessage()]);
            return service('response')->setStatusCode(503)->setHeader('Retry-After', '10')
                ->setHeader('Cache-Control', 'no-store')->setJSON([
                    'error' => 'Nao foi possivel inicializar o banco. Confira as credenciais, permissoes MySQL e logs da aplicacao.',
                ]);
        } finally {
            if ($locked && $db !== null) {
                try {
                    $db->query('SELECT RELEASE_LOCK(?)', [$lockName]);
                } catch (\Throwable $ignored) {
                    // Closing the connection also releases MySQL advisory locks.
                }
            }
        }
        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
