<?php
/**
 * @author gaobinzhan <gaobinzhan@gmail.com>
 */


namespace Gaobinzhan\RpcClient\Contract;


use Gaobinzhan\RpcClient\Client;

interface ConnectionInterface
{
    public function create(): void;

    public function send(string $string): bool;

    public function recv();

    public function close(): bool;

    public function call(string $interfaceClass, string $method, array $params);
}