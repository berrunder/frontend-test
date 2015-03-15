<?php
require 'vendor/autoload.php';
require 'src/Product.php';

$app = new \Slim\Slim(array(
    'debug' => true,
    'templates.path' => './templates',
));

$app->get('/', function () use ($app) {
    $data = array();
    $app->render('main.php', $data);
});

$app->post('/product', function () use ($app) {
    $body = $app->request->getBody();
    $result = Product::add(json_decode($body));

    if ($result['success']) {
        $app->response->setStatus(201);
    } else {
        $app->response->setStatus(500);
    }

    $app->response->headers->set('Content-Type', 'application/json');
    $app->response->setBody(json_encode($result));
});

$app->get('/product', function() use ($app) {
    $products = Product::getAll();
    $app->response->headers->set('Content-Type', 'application/json');
    $app->response->setBody(json_encode($products));
});

$app->put('/product/:id', function($id) use ($app) {
    $body = $app->request->getBody();
    if (Product::update(json_decode($body)) !== false) {
        $app->response->setStatus(204);
    } else {
        $app->response->setStatus(500);
    }
});

$app->delete('/product/:id', function($id) use ($app) {
    if (Product::remove($id)) {
        $app->response->setStatus(204);
    } else {
        $app->response->setStatus(500);
    }
});

$app->run();