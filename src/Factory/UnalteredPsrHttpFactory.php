<?php

declare(strict_types=1);

namespace loophp\UnalteredPsrHttpMessageBridgeBundle\Factory;

use Generator;
use Symfony\Bridge\PsrHttpMessage\HttpMessageFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class UnalteredPsrHttpFactory implements HttpMessageFactoryInterface
{
    /**
     * @var \Symfony\Bridge\PsrHttpMessage\HttpMessageFactoryInterface
     */
    private $httpMessageFactory;

    public function __construct(HttpMessageFactoryInterface $httpMessageFactory)
    {
        $this->httpMessageFactory = $httpMessageFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function createRequest(Request $symfonyRequest)
    {
        // Call the original object to avoid duplicating code.
        $request = $this->httpMessageFactory->createRequest($symfonyRequest);

        // If a query string does not exist, return the original object.
        if ('' === $unalteredQueryString = $symfonyRequest->server->get('QUERY_STRING')) {
            return $request;
        }

        // This is where all is happening.
        // We do not rely on $symfonyRequest->query->all() because it relies
        // on parse_str() which is altering the query string parameters.
        // We rely on $symfonyRequest->server->get('QUERY_STRING') so we are
        // sure that the query string hasn't been altered.
        // Create a new request with the URI and Params updated.
        return $request
            ->withQueryParams(
                iterator_to_array($this->parseStr($unalteredQueryString))
            )
            ->withUri(
                $request
                    ->getUri()
                    ->withQuery($unalteredQueryString)
            );
    }

    /**
     * {@inheritdoc}
     */
    public function createResponse(Response $symfonyResponse)
    {
        return $this->httpMessageFactory->createResponse($symfonyResponse);
    }

    /**
     * Custom parse_str() function that doesn't alter the parameters key value.
     *
     * @see: https://github.com/ecphp/cas-lib/issues/5.
     *
     * @param string $queryString
     *
     * @return Generator<string, string>
     */
    private function parseStr(string $queryString): Generator
    {
        $encodedQueryString = preg_replace_callback(
            '/(^|(?<=&))[^=[&]+/',
            static function (array $key): string {
                return bin2hex(urldecode(current($key)));
            },
            $queryString
        );

        if (null === $encodedQueryString) {
            return yield from [];
        }

        parse_str(
            $encodedQueryString,
            $parameters
        );

        foreach ($parameters as $key => $value) {
            yield (string) hex2bin((string) $key) => $value;
        }
    }
}
