<?php
/*
 * This file is part of UCRM Plugin SDK.
 *
 * Copyright (c) 2018 Ubiquiti Networks
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Ubnt\UcrmPluginSdk\Service;

use Eloquent\Phony\Phpunit\Phony;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Ubnt\UcrmPluginSdk\Exception\ConfigurationException;
use Ubnt\UcrmPluginSdk\Exception\InvalidPluginRootPathException;

class UcrmApiTest extends \PHPUnit\Framework\TestCase
{
    private const TEST_APP_KEY = 'testAppKey/xyz';

    public function testCreate(): void
    {
        $exception = null;

        try {
            UcrmApi::create(__DIR__ . '/../../files_enabled');
        } catch (ConfigurationException | InvalidPluginRootPathException $exception) {
        }

        self::assertNull($exception);
    }

    public function testCreateWrongPath(): void
    {
        $exception = null;

        try {
            UcrmApi::create(__DIR__);
        } catch (InvalidPluginRootPathException $exception) {
        }

        self::assertInstanceOf(InvalidPluginRootPathException::class, $exception);
    }

    public function testDisabledPlugin(): void
    {
        $exception = null;

        try {
            UcrmApi::create(__DIR__ . '/../../files_disabled');
        } catch (ConfigurationException $exception) {
        }

        self::assertInstanceOf(ConfigurationException::class, $exception);
    }

    public function testPost(): void
    {
        $responseHandle = Phony::mock(Response::class);
        $responseHandle->getStatusCode->returns(201);
        $responseMock = $responseHandle->get();

        $clientHandle = Phony::mock(Client::class);
        $clientHandle->request->returns($responseMock);
        $clientMock = $clientHandle->get();

        $ucrmApi = new UcrmApi($clientMock, self::TEST_APP_KEY);
        $endpoint = 'clients';
        $data = [
            'firstName' => 'John',
            'lastName' => 'Doe',
        ];
        $ucrmApi->post($endpoint, $data);

        $clientHandle->request->calledWith(
            'POST',
            $endpoint,
            [
                'json' => $data,
                'headers' => [
                    'x-auth-app-key' => self::TEST_APP_KEY,
                ],
            ]
        );
    }

    public function testPatch(): void
    {
        $responseHandle = Phony::mock(Response::class);
        $responseHandle->getStatusCode->returns(200);
        $responseMock = $responseHandle->get();

        $clientHandle = Phony::mock(Client::class);
        $clientHandle->request->returns($responseMock);
        $clientMock = $clientHandle->get();

        $ucrmApi = new UcrmApi($clientMock, self::TEST_APP_KEY);
        $endpoint = 'clients';
        $data = [
            'firstName' => 'John',
            'lastName' => 'Doe',
        ];
        $ucrmApi->patch($endpoint, $data);

        $clientHandle->request->calledWith(
            'PATCH',
            $endpoint,
            [
                'json' => $data,
                'headers' => [
                    'x-auth-app-key' => self::TEST_APP_KEY,
                ],
            ]
        );
    }

    public function testDelete(): void
    {
        $responseHandle = Phony::mock(Response::class);
        $responseHandle->getStatusCode->returns(200);
        $responseMock = $responseHandle->get();

        $clientHandle = Phony::mock(Client::class);
        $clientHandle->request->returns($responseMock);
        $clientMock = $clientHandle->get();

        $ucrmApi = new UcrmApi($clientMock, self::TEST_APP_KEY);
        $endpoint = 'clients';
        $ucrmApi->delete($endpoint);

        $clientHandle->request->calledWith(
            'DELETE',
            $endpoint,
            [
                'headers' => [
                    'x-auth-app-key' => self::TEST_APP_KEY,
                ],
            ]
        );
    }

    /**
     * @param mixed[]|string $expectedResult
     *
     * @dataProvider getProvider
     */
    public function testGet(string $contentType, string $returnedBody, $expectedResult): void
    {
        $responseHandle = Phony::mock(Response::class);
        $responseHandle->getStatusCode->returns(201);
        $responseHandle->getBody->returns($returnedBody);
        $responseHandle->getHeaderLine->with('content-type')->returns($contentType);
        $responseMock = $responseHandle->get();

        $clientHandle = Phony::mock(Client::class);
        $clientHandle->request->returns($responseMock);
        $clientMock = $clientHandle->get();

        $ucrmApi = new UcrmApi($clientMock, self::TEST_APP_KEY);
        $endpoint = 'clients';
        $query = [
            'order' => 'client.id',
            'direction' => 'DESC',
        ];
        $result = $ucrmApi->get($endpoint, $query);
        self::assertSame($expectedResult, $result);

        $clientHandle->request->calledWith(
            'GET',
            $endpoint,
            [
                'query' => $query,
                'headers' => [
                    'x-auth-app-key' => self::TEST_APP_KEY,
                ],
            ]
        );
    }

    /**
     * @return mixed[]
     */
    public function getProvider(): array
    {
        return [
            [
                'contentType' => 'text/plain',
                'returnedBody' => 'lorem ipsum dolor',
                'expectedResult' => 'lorem ipsum dolor',
            ],
            [
                'contentType' => 'application/json',
                'returnedBody' => '["lorem", "ipsum"]',
                'expectedResult' => ['lorem', 'ipsum'],
            ],
        ];
    }
}
