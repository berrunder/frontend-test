<?php
require 'vendor/autoload.php';
require 'src/Product.php';

define('DATA_DIR', __DIR__ . DIRECTORY_SEPARATOR . 'data');

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
    $result = saveJson($body);

    $app->response->headers->set('Content-Type', 'application/json');
    $app->response->setBody(json_encode($result));
});

$app->get('/product', function() use ($app) {
    $products = getProducts();
    $app->response->headers->set('Content-Type', 'application/json');
    $app->response->setBody(json_encode($products));
});

$app->delete('/product/:id', function($id) use ($app) {
    if (Product::remove($id)) {
        $app->response->setStatus(204);
    } else {
        $app->response->setStatus(500);
    }
});

$app->run();

function saveJson($raw)
{
    $data = json_decode($raw);

    if ($data) {
        $data->submitted = time() * 1000;
        $files = array_filter(glob(DATA_DIR . DIRECTORY_SEPARATOR . '*.json'), 'is_file');
        $num = 1;
        foreach ($files as $file) {
            $fileId = intval(basename($file, ".json"));
            if ($num <= $fileId) {
                $num = $fileId + 1;
            }
        }

        if (file_put_contents(DATA_DIR . DIRECTORY_SEPARATOR . $num . '.json', json_encode($data)) !== false) {
            return array(
                'success' => true,
                'id' => $num,
                'submitted' => $data->submitted,
            );
        }
    }

    return array('success' => false);
}

function getProducts() {
    $products = [];
    $files = array_filter(glob(DATA_DIR . DIRECTORY_SEPARATOR . '*.json'), 'is_file');

    foreach ($files as $file) {
        $fileId = intval(basename($file, ".json"));
        $raw = file_get_contents($file);
        $data = json_decode($raw);
        if ($data && is_object($data)) {
            $data->id = $fileId;
            $products[] = $data;
        }
    }

    return $products;
}