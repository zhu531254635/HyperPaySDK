<?php
    require __DIR__ .'/vendor/autoload.php';

    use Overtrue\Hyperpay\Hyperpay;

    $key = 'a981cb7750aa6e4509fe7f473f5f4d98';
    $w = new Hyperpay($key);
    $response = $w->getHyperpay('合肥', 'all', 'json');

    echo "<pre>";
    print_r($response);