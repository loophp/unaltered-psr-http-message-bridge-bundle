<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace spec\loophp\UnalteredPsrHttpMessageBridgeBundle\Factory;

use loophp\UnalteredPsrHttpMessageBridgeBundle\Factory\UnalteredPsrHttpFactory;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\ServerRequest;
use PhpSpec\ObjectBehavior;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;
use Symfony\Bridge\PsrHttpMessage\HttpMessageFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UnalteredPsrHttpFactorySpec extends ObjectBehavior
{
    public function it_can_convert_a_request_having_a_simple_parameter()
    {
        $tests = [
            [
                'url' => 'http://localhost/api?filter.field=foobar&simple=bar',
                'toString' => 'http://localhost/api?filter.field=foobar&simple=bar',
                'getQueryParams' => ['filter.field' => 'foobar', 'simple' => 'bar'],
            ],
            [
                'url' => 'http://localhost/api?filter.field[]=foo&filter.field[]=bar',
                'toString' => 'http://localhost/api?filter.field%5B%5D=foo&filter.field%5B%5D=bar',
                'getQueryParams' => ['filter.field' => ['foo', 'bar']],
            ],
            [
                'url' => 'http://localhost/api?filter.field=foo&filter.field=bar',
                'toString' => 'http://localhost/api?filter.field=foo&filter.field=bar',
                'getQueryParams' => ['filter.field' => 'bar'],
            ],
            [
                'url' => 'http://localhost/api?filter.field[]=foobar&filter.field[]=barfoo&simple=foo',
                'toString' => 'http://localhost/api?filter.field%5B%5D=foobar&filter.field%5B%5D=barfoo&simple=foo',
                'getQueryParams' => [
                    'filter.field' => [
                        'foobar',
                        'barfoo',
                    ],
                    'simple' => 'foo',
                ],
            ],
        ];

        foreach ($tests as $test) {
            $symfonyRequest = Request::create($test['url']);

            $this
                ->createRequest($symfonyRequest)
                ->getUri()
                ->__toString()
                ->shouldReturn($test['toString']);

            $this
                ->createRequest($symfonyRequest)
                ->getQueryParams()
                ->shouldReturn($test['getQueryParams']);
        }
    }

    public function it_can_convert_a_symfony_request_into_a_psr_request()
    {
        $symfonyRequest = Request::create('http://localhost/api?filter.field=foobar');

        $this
            ->createRequest($symfonyRequest)
            ->shouldReturnAnInstanceOf(RequestInterface::class);
    }

    public function it_does_not_do_anything_if_the_uri_does_not_have_query_parameters(HttpMessageFactoryInterface $httpMessageFactory)
    {
        $symfonyRequest = Request::create('http://localhost/api');
        $psrRequest = new ServerRequest('GET', 'http://localhost/api');

        $httpMessageFactory
            ->createRequest($symfonyRequest)
            ->willReturn($psrRequest);

        $this->beConstructedWith($httpMessageFactory);

        $this
            ->createRequest($symfonyRequest)
            ->shouldReturn($psrRequest);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(UnalteredPsrHttpFactory::class);
        $this->shouldImplement(HttpMessageFactoryInterface::class);
    }

    public function it_should_not_do_anything_if_the_uri_does_not_have_parameters(HttpMessageFactoryInterface $httpMessageFactory, Request $symfonyRequest, ServerRequestInterface $psrRequest)
    {
        $symfonyRequest = Request::create('http://localhost/api');

        $httpMessageFactory
            ->createRequest($symfonyRequest)
            ->willReturn($psrRequest);

        $this->beConstructedWith($httpMessageFactory);

        $this
            ->createRequest($symfonyRequest)
            ->shouldReturn($psrRequest);

        $psrRequest
            ->withQueryParams()
            ->shouldNotBeCalled();
    }

    public function it_should_not_take_care_of_the_response(HttpMessageFactoryInterface $httpMessageFactory, Response $symfonyResponse, ResponseInterface $psrResponse)
    {
        $randomBody = sha1(microtime());

        $psrResponse
            ->getStatusCode()
            ->willReturn(302);

        $psrResponse
            ->getProtocolVersion()
            ->willReturn('1.1');

        $psrResponse
            ->getBody()
            ->willReturn($randomBody);

        $httpMessageFactory
            ->createResponse($symfonyResponse)
            ->willReturn($psrResponse);

        $this->beConstructedWith($httpMessageFactory);

        $this
            ->createResponse($symfonyResponse)
            ->shouldReturn($psrResponse);

        $httpMessageFactory
            ->createResponse($symfonyResponse)
            ->shouldHaveBeenCalledOnce();

        $this
            ->createResponse($symfonyResponse)
            ->getStatusCode()
            ->shouldEqual(302);

        $this
            ->createResponse($symfonyResponse)
            ->getProtocolVersion()
            ->shouldEqual('1.1');

        $this
            ->createResponse($symfonyResponse)
            ->getBody()
            ->shouldEqual($randomBody);
    }

    public function let()
    {
        $psr17Factory = new Psr17Factory();

        $psrHttpMessageFactory = new PsrHttpFactory(
            $psr17Factory,
            $psr17Factory,
            $psr17Factory,
            $psr17Factory
        );

        $this->beConstructedWith($psrHttpMessageFactory, $psr17Factory);
    }
}
