<?php

namespace Overtrue\Hyperpay;

use GuzzleHttp\Client;
use Overtrue\Hyperpay\Exceptions\HttpException;
use Overtrue\Hyperpay\Exceptions\InvalidArgumentException;

class Hyperpay
{
    private $url = 'https://restapi.amap.com/v3/weather/weatherInfo';
    protected $key;
    protected $guzzleOptions = [];

    public function __construct(string $key)
    {
        $this->key = $key;
    }

    public function getHttpClient()
    {
        return new Client($this->guzzleOptions);
    }

    public function setGuzzleOptions(array $options)
    {
        $this->guzzleOptions = $options;
    }


    /**
     * TEST
     * @param $city
     * @param string $type
     * @param string $format
     * @return mixed|string
     * @throws HttpException
     * @throws InvalidArgumentException
     */
    public function getHyperpay($city, $type = 'base', $format = 'json')
    {
        if (!\in_array($format, ['xml', 'json'])) {
            throw new InvalidArgumentException('Invalid response format: '. $format);
        }

        if (!\in_array(\strtolower($type), ['base', 'all'])) {
            throw new InvalidArgumentException('Invalid type value(base/all): '.$type);
        }

        $query = array_filter([
            'key' => $this->key,
            'city' => $city,
            'output' => $format,
            'extensions' => $type,
        ]);

        try {
            // 传递参数为两个：$url、['query' => $query]，
            $response = $this->getHttpClient()->get($this->url, [
                'query' => $query,
            ])->getBody()->getContents();
            // 当 $format 为 json 时，返回数组格式，否则为 xml。
            return $format === 'json' ? \json_decode($response, true) : $response;
        } catch (\Exception $e) {
            // 并将调用异常作为 $previousException 传入。
            throw new HttpException($e->getMessage(), $e->getCode(), $e);
        }
    }
}