<?php
/**
 * @author gaobinzhan <gaobinzhan@gmail.com>
 */


namespace Gaobinzhan\RpcClient\Contract;


use Gaobinzhan\RpcClient\Client;

interface ProviderInterface
{
    public function getList(Client $client): array;
}