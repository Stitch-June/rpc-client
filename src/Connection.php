<?php
/**
 * @author gaobinzhan <gaobinzhan@gmail.com>
 */


namespace Gaobinzhan\RpcClient;


use Gaobinzhan\Rpc\Protocol;
use Gaobinzhan\RpcClient\Contract\ConnectionInterface;
use Gaobinzhan\RpcClient\Exception\RpcClientException;
use Gaobinzhan\RpcClient\Exception\RpcResponseException;
use Swoole\Coroutine\Client as SwooleClient;

class Connection implements ConnectionInterface
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var SwooleClient
     */
    protected $connection;

    public function __construct(Client $client)
    {
        $this->client = $client;
        return $this;
    }

    public function create(): void
    {
        [$host, $port] = $this->getHostPort();
        $connection = new SwooleClient(SWOOLE_SOCK_TCP);

        $setting = $this->client->getSetting();
        if ($setting) {
            $connection->set($setting);
        }

        if (!$connection->connect($host, (int)$port)) {
            throw new RpcClientException("Connect failed host={$host} port={$port}");
        }

        $this->connection = $connection;
    }

    public function send(string $data): bool
    {
        return (bool)$this->connection->send($data);
    }

    public function recv()
    {
        return $this->connection->recv();
    }

    public function close(): bool
    {
        return $this->connection->close();
    }

    public function getHostPort(): array
    {
        $provider = $this->client->getProvider();
        if (!$provider) {
            return [$this->client->getHost(), $this->client->getPort()];
        }

        $hostPort = $provider->getList($this->client);
        if (!$hostPort) {
            throw new RpcClientException('Provider return host and port can not empty!');
        }


        if (count($hostPort) < 2) {
            throw new RpcClientException('Provider return format is error!');
        }

        [$host, $port] = $hostPort;
        return [$host, $port];
    }

    public function call(string $interfaceClass, string $method, array $params)
    {
        $packet = $this->client->getPacket();
        if (!$packet) {
            throw new RpcClientException("Client({$packet}) packet can not be null");
        }

        $protocol = Protocol::create($interfaceClass, $method, $params);
        $requestData = $packet->encode($protocol);

        $rawResult = $this->sendAndRecv($requestData);
        $response = $packet->decodeResponse($rawResult);

        if ($error = $response->getError()) {
            $rpcResponseException = new RpcResponseException(
                "Rpc call error!code={$error->getCode()} message={$error->getMessage()} data={$error->getData()}",
                $error->getCode()
            );
            $rpcResponseException->setResponse($response);

            throw $rpcResponseException;
        }

        return $response->getResult();
    }

    public function reconnect(): bool
    {
        $this->create();
        return true;
    }

    /**
     * @return int
     */
    public function getErrCode(): int
    {
        return (int)$this->connection->errCode;
    }

    /**
     * @return string
     */
    public function getErrMsg(): string
    {
        return (string)$this->connection->errMsg;
    }

    protected function sendAndRecv(string $data, bool $reconnect = false): string
    {
        if ($reconnect) {
            $this->reconnect();
        }

        $message = "Rpc call error!code=%d message=%s data=%s";

        if (!$this->send($data)) {
            if ($reconnect) {
                throw new RpcClientException(sprintf($message, $this->getErrCode(), $this->getErrMsg(), $data));
            }
            return $this->sendAndRecv($data, true);
        }

        $result = $this->recv();
        if ($result === false || empty($result)) {
            if ($reconnect || $this->getErrCode() === SOCKET_ETIMEDOUT) {
                throw new RpcClientException(sprintf($message, $this->getErrCode(), $this->getErrMsg(), $data));
            }
            return $this->sendAndRecv($data, true);
        }

        return $result;
    }
}