<?php


namespace App\Services\SoapServer;

use SoapFault;
use Laminas\Soap\Server;
use Illuminate\Routing\ResponseFactory;
use Illuminate\Contracts\Container\Container;
use Laminas\Soap\Server\DocumentLiteralWrapper;
use Illuminate\Routing\Controller as BaseController;

abstract class AbstractSoapServerController extends BaseController
{
    /**
     * @var bool
     */
    public $faultResponse = true;

    /**
     * @return string Server host class name
     */
    abstract protected function getService(): string;

    /**
     * @return string URL endpoint of server
     */
    abstract protected function getEndpoint(): string;

    /**
     * @return string URL endpoint of WSDL
     */
    abstract protected function getWsdlUri(): string;

    /**
     * response wsdl
     * @param ResponseFactory $responseFactory
     * @return mixed
     */
    abstract public function wsdlProvider(ResponseFactory $responseFactory);

    /**
     * @return string Service name (Defaults to server host class basename)
     */
    protected function getName(): string
    {
        return class_basename($this->getService());
    }

    /**
     * @return string[] Fault exception handlers to register
     */
    protected function getFaultExceptionsNames(): array
    {
        return [\Exception::class];
    }

    /**
     * @return string[] Class map
     */
    protected function getClassmap(): array
    {
        return [];
    }

    /**
     * @return string[] Additional headers to sent with server responses
     */
    protected function getHeaders(): array
    {
        return config('soap-server.headers.soap');
    }

    /**
     * @return string[] SOAP server options
     */
    protected function getOptions(): array
    {
        return [];
    }

    /**
     * @return string[] Additional headers to sent with WSDL responses
     */
    protected function getWsdlHeaders(): array
    {
        return config('soap-server.headers.wsdl');
    }

    /**
     * @return bool Check if WSDL extension cache
     */
    protected function getWsdlCacheEnabled(): bool
    {
        return config('soap-server.wsdl_cache_enabled');
    }

    public function soapServer(Container $container, ResponseFactory $responseFactory)
    {
        $this->disableSoapCacheWhenNeeded();

        try {
            $service = $container->make($this->getService());
            $server = new Server($this->getWsdlUri());
            $server->setClass(new DocumentLiteralWrapper($service));
            if (config('app.debug')) {
                $server->setDebugMode(true);
            }
            $server->registerFaultException($this->getFaultExceptionsNames());
            $server->setClassmap($this->getClassmap());
            $server->setOptions($this->getOptions());

            // Intercept response, then decide what to do with it.
            $server->setReturnResponse(true);

            $response = $server->handle();

            // Deal with a thrown exception that was converted into a SoapFault.
            // SoapFault thrown directly in a service class bypasses this code.
            if ($response instanceof SoapFault && $this->faultResponse) {
                return $responseFactory->make(self::serverFault($response), 500, $this->getHeaders());
            } else {
                return $responseFactory->make($response, 200, $this->getHeaders());
            }

        } catch (\Exception $e) {
            return $responseFactory->make(self::serverFault($e), 500, $this->getHeaders());
        }
    }

    /**
     * Return error response and log stack trace.
     *
     * @param \Exception $exception
     *
     * @return string
     */
    protected static function serverFault(\Exception $exception)
    {
        report($exception);

        $faultcode = 'SOAP-ENV:Server';
        $faultstring = $exception->getMessage();

        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/">
    <SOAP-ENV:Body>
        <SOAP-ENV:Fault>
            <faultcode>{$faultcode}</faultcode>
            <faultstring>{$faultstring}</faultstring>
        </SOAP-ENV:Fault>
    </SOAP-ENV:Body>
</SOAP-ENV:Envelope>
XML;
    }

    protected function disableSoapCacheWhenNeeded(): void
    {
        if (!$this->getWsdlCacheEnabled()) {
            ini_set("soap.wsdl_cache_enabled", false);
            ini_set('soap.wsdl_cache_ttl', 0);
        }
    }
}
