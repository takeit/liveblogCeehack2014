<?php
require_once __DIR__.'/../vendor/autoload.php';

use Silex\Provider\FormServiceProvider;

$app = new Silex\Application();

$app->register(new Silex\Provider\SessionServiceProvider());
$app->register(new Silex\Provider\UrlGeneratorServiceProvider());
$app->register(new Silex\Provider\ValidatorServiceProvider());
$app->register(new FormServiceProvider());

/*$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
    'db.options' => array(
        'driver'   => 'pdo_sqlite',
        'path'     => __DIR__.'/app.db',
    ),
));*/

// ... definitions

$app->get('/', function (Silex\Application $app) {

    return  "<h1>Działa</h1>";
});


$app->run();