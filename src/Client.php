<?php
/**
 * @author gaobinzhan <gaobinzhan@gmail.com>
 */


namespace Gaobinzhan\RpcClient;


use Gaobinzhan\Rpc\Contract\PacketInterface;
use Gaobinzhan\RpcClient\Contract\ConnectionInterface;
use Gaobinzhan\RpcClient\Contract\ProviderInterface;

class Client
{
    /**
     * @var string
     */
    protected $host = '127.0.0.1';

    /**
     * @var string
     */
    protected $port = 9502;

    /**
     * @var array
     */
    protected $setting = [];

    /**
     * @var PacketInterface
     */
    protected $packet;

    /**
     * @var ProviderInterface
     */
    protected $provider;

    /**
     * @return string
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * @param string $host
     */
    public function setHost(string $host): void
    {
        $this->host = $host;
    }

    /**
     * @return string
     */
    public function getPort(): string
    {
        return $this->port;
    }

    /**
     * @param string $port
     */
    public function setPort(string $port): void
    {
        $this->port = $port;
    }

    /**
     * @return PacketInterface
     */
    public function getPacket(): ?PacketInterface
    {
        return $this->packet;
    }

    /**
     * @param PacketInterface $packet
     */
    public function setPacket(PacketInterface $packet): void
    {
        $this->packet = $packet;
    }

    /**
     * @return ProviderInterface
     */
    public function getProvider(): ?ProviderInterface
    {
        return $this->provider;
    }

    /**
     * @param ProviderInterface $provider
     */
    public function setProvider(ProviderInterface $provider): void
    {
        $this->provider = $provider;
    }

    /**
     * @return array
     */
    public function getSetting(): array
    {
        return array_merge($this->defaultSetting(), $this->setting);
    }

    /**
     * @param array $setting
     */
    public function setSetting(array $setting): void
    {
        $this->setting = $setting;
    }

    public function create(?ConnectionInterface $connection = null): ConnectionInterface
    {
        if (!$connection) {
            $connection = new Connection($this);
        }
        $connection->create();

        return $connection;
    }

    /**
     * @return array
     */
    private function defaultSetting(): array
    {
        return [
            'open_eof_check' => true,
            'open_eof_split' => true,
            'package_eof' => "\r\n\r\n",
        ];
    }
}