<?php
/**
 * @author gaobinzhan <gaobinzhan@gmail.com>
 */


namespace Gaobinzhan\RpcClient\Exception;


use Gaobinzhan\Rpc\Response;

class RpcResponseException extends \Exception
{
    /**
     * @var Response
     */
    protected $response;

    /**
     * @return Response
     */
    public function getResponse(): Response
    {
        return $this->response;
    }

    /**
     * @param Response $response
     */
    public function setResponse(Response $response): void
    {
        $this->response = $response;
    }
}