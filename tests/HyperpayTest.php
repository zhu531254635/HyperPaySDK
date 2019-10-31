<?php
namespace Overtrue\Hyperpay\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;
use Mockery\Matcher\AnyArgs;
use Overtrue\Hyperpay\Exceptions\HttpException;
use Overtrue\Hyperpay\Exceptions\InvalidArgumentException;
use Overtrue\Hyperpay\Hyperpay;
use PHPUnit\Framework\TestCase;

class HyperpayTest extends TestCase
{
    public function testGetHyperpayWithInvalidType()
    {
        $w = new Hyperpay('mock-key');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid type value(base/all): foo');

        $w->getHyperpay('合肥', 'foo');

        $this->fail('Failed to assert getHyperpay throw exception with invalid argument.');
    }

    public function testGetHyperpayWithInvalidFormat()
    {
        $w = new Hyperpay('mock-key');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid response format: array');

        $w->getHyperpay('合肥', 'base', 'array');

        $this->fail('Failed to assert getHyperpay throw exception with invalid argument.');
    }

    public function testGetHyperpay()
    {
        // json
        $response = new Response(200, [], '{"success": true}');
        $client = \Mockery::mock(Client::class);
        $client->allows()->get('https://restapi.amap.com/v3/Hyperpay/HyperpayInfo', [
            'query' => [
                'key' => 'mock-key',
                'city' => '合肥',
                'output' => 'json',
                'extensions' => 'base',
            ],
        ])->andReturn($response);

        $w = \Mockery::mock(Hyperpay::class, ['mock-key'])->makePartial();
        $w->allows()->getHttpClient()->andReturn($client);

        $this->assertSame(['success' => true], $w->getHyperpay('合肥'));

        // xml
        $response = new Response(200, [], '<hello>content</hello>');
        $client = \Mockery::mock(Client::class);
        $client->allows()->get('https://restapi.amap.com/v3/Hyperpay/HyperpayInfo', [
            'query' => [
                'key' => 'mock-key',
                'city' => '合肥',
                'extensions' => 'all',
                'output' => 'xml',
            ],
        ])->andReturn($response);

        $w = \Mockery::mock(Hyperpay::class, ['mock-key'])->makePartial();
        $w->allows()->getHttpClient()->andReturn($client);

        $this->assertSame('<hello>content</hello>', $w->getHyperpay('合肥', 'all', 'xml'));
    }

    public function testGetHyperpayWithGuzzleRuntimeException()
    {
        $client = \Mockery::mock(Client::class);
        $client->allows()
            ->get(new AnyArgs())
            ->andThrow(new \Exception('request timeout'));

        $w = \Mockery::mock(Hyperpay::class, ['mock-key'])->makePartial();
        $w->allows()->getHttpClient()->andReturn($client);

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('request timeout');

        $w->getHyperpay('合肥');
    }

    public function testGetHttpClient()
    {
        $w = new Hyperpay('mock-key');

        // 断言返回结果为 GuzzleHttp\ClientInterface 实例
        $this->assertInstanceOf(ClientInterface::class, $w->getHttpClient());
    }

    public function testSetGuzzleOptions()
    {
        $w = new Hyperpay('mock-key');

        // 设置参数前，timeout 为 null
        $this->assertNull($w->getHttpClient()->getConfig('timeout'));

        // 设置参数
        $w->setGuzzleOptions(['timeout' => 5000]);

        // 设置参数后，timeout 为 5000
        $this->assertSame(5000, $w->getHttpClient()->getConfig('timeout'));
    }
}