<?php

declare(strict_types=1);

namespace Tests;

use phpCAS;
use Subfission\Cas\PhpCasProxy;

/**
 * phpCAS stores its client in a static property for the lifetime of the PHP process, but every test boots a fresh
 * application and therefore a fresh CasManager. phpCAS treats a second client() or proxy() call as a fatal error
 * and exits the process, so only the first initialization is forwarded.
 */
final class InitializeOncePhpCasProxy extends PhpCasProxy
{
    #[\Override]
    public function client(
        $server_version,
        $server_hostname,
        $server_port,
        $server_uri,
        $service_base_url,
        $changeSessionID = true,
        $sessionHandler = null
    ): void {
        if (phpCAS::isInitialized()) {
            return;
        }

        parent::client(
            $server_version,
            $server_hostname,
            $server_port,
            $server_uri,
            $service_base_url,
            $changeSessionID,
            $sessionHandler
        );
    }

    #[\Override]
    public function proxy(
        $server_version,
        $server_hostname,
        $server_port,
        $server_uri,
        $service_base_url,
        $changeSessionID = true,
        $sessionHandler = null
    ): void {
        if (phpCAS::isInitialized()) {
            return;
        }

        parent::proxy(
            $server_version,
            $server_hostname,
            $server_port,
            $server_uri,
            $service_base_url,
            $changeSessionID,
            $sessionHandler
        );
    }
}
