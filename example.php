<?php

require_once 'vendor/autoload.php';

try {
    $listUid = '1w548tk65s20c';

    \Postex\Base::setConfig(new \Postex\Config([
        'publicKey'  => '1f5835d6c382a06e76ec8956b7f2ef6d4be102b5',
        'privateKey' => '2fad3f737b1a5d55db1075e0b426886ab76ed06e',
    ]));

    $endpoint = new \Postex\Endpoint\ListSubscribers();

    /** @var \Postex\Http\Response $response */
    $response = $endpoint->create($listUid, [
        'EMAIL'   => 'test' . mt_rand(0, 123456) . '@test.com',
        'details' => [
            'status'     => 'confirmed',
            'ip_address' => $_SERVER['REMOTE_ADDR']
        ],
    ]);

    print_r($response->body);

} catch (ReflectionException $e) {

} catch (Exception $e) {

}
