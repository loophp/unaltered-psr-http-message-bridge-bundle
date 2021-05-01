<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace loophp\UnalteredPsrHttpMessageBridgeBundle\Factory;

use League\Uri\Parser\QueryString;
use Symfony\Bridge\PsrHttpMessage\HttpMessageFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use const PHP_QUERY_RFC1738;

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
        if ('' === $unalteredQueryString = $symfonyRequest->server->get('QUERY_STRING', '')) {
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
                QueryString::extract($unalteredQueryString, '&', PHP_QUERY_RFC1738)
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
}
