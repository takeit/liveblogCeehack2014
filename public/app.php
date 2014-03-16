<?php
require_once __DIR__.'/../vendor/autoload.php';

use Silex\Provider\FormServiceProvider;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

$app = new Silex\Application();

$app->register(new Silex\Provider\ValidatorServiceProvider());
$app->register(new Silex\Provider\SessionServiceProvider());
$app->register(new Silex\Provider\UrlGeneratorServiceProvider());
$app->register(new Silex\Provider\ValidatorServiceProvider());
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/views',
));
$app->register(new Silex\Provider\TranslationServiceProvider(), array(
    'translator.domains' => array(),
));
$app->register(new FormServiceProvider());

/*$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
    'db.options' => array(
        'driver'   => 'pdo_sqlite',
        'path'     => __DIR__.'/app.db',
    ),
));*/

// ... definitions

$app->get('/', function (Silex\Application $app, Request $request) {

   /* $yaml = new Parser();

    $file = $yaml->parse(file_get_contents(__DIR__.'/config.yml'));
    $curlClient = new \Buzz\Client\Curl();
    $browser = new \Buzz\Browser($curlClient);
    $result =  $browser->post($file['config']['host'].'/resources/Security/Authentication', array(), 'userName='.$file['config']['username']);
    $token = json_decode($result->getContent(), true);

    $password = $file['config']['password'];
    $hashedPassword = hash('sha512', $password);
    $hashedUsername = hash_hmac('sha512', $hashedPassword, $file['config']['username']);
    $hashedToken = hash_hmac('sha512', $token['Token'], $hashedUsername);

    $loginResult = $browser->post($file['config']['host'].'/resources/Security/Authentication/Login', array(),
        'UserName='.$file['config']['username'].'&Token='.$token['Token'].'&HashedToken='.$hashedToken);

    $loginResponse = json_decode($loginResult->getContent(), true);

    $creatBlogResult = $browser->post(
        $file['config']['host'].'/resources/my/LiveDesk/Blog',
        array(
            'Authorization' => $loginResponse['Session'],
            'X-Filter' => 'Id'
        ),
        'Language=3&Title=addme3&Type=1&Description=test description&Creator='.$loginResponse['User']['Id']
    );

    $createBlogResponse = json_decode($creatBlogResult->getContent(), true);

    $creatUserResult = $browser->post(
        $file['config']['host'].'/resources/my/HR/User',
        array(
            'Authorization' => $loginResponse['Session'],
            'X-Filter' => 'Id',
        ),
        'FirstName=testuser356&LastName=testlastname356&Name=addme3&EMail=example35@domain.com&Password='.hash('sha512', 'test')
    );

    $creatUserResponse = json_decode($creatUserResult->getContent(), true);

    //add user role: collaborator role
    $roleUserResult = $browser->post(
        $file['config']['host'].'/resources/HR/User/'. $creatUserResponse['Id'].'/Role/2',
        array(
            'Authorization' => $loginResponse['Session'],
            'X-HTTP-Method-Override' => 'PUT'
        )
    );

    // collaborator request

    $collaboratorResult = $browser->post(
        $file['config']['host'].'/resources/Data/Collaborator',
        array('X-Filter' => 'Id'),
        'User='.$creatUserResponse['Id'].'&Source=10'
    );

    $collaboratorResponse = json_decode($collaboratorResult->getContent(), true);

    //add user as a collaborator to blog
    $addUserResult = $browser->post(
        $file['config']['host'].'/resources/my/LiveDesk/Blog/'.$createBlogResponse['Id'].'/Collaborator/'.$collaboratorResponse['Id'].'/Add',
        array(
            'Authorization' => $loginResponse['Session'],
            'X-HTTP-Method-Override' => 'PUT',
            'X-Requested-With' => 'XMLHttpRequest'
        )
    );

    $blogColResult = $browser->post(
        $file['config']['host'].'/resources/my/LiveDesk/Blog/'.$createBlogResponse['Id'].'/Collaborator/'.$collaboratorResponse['Id'].'/Type/Administrator',
        array(
            'Authorization' => $loginResponse['Session'],
            'X-HTTP-Method-Override' => 'PUT',
            'X-Requested-With' => 'XMLHttpRequest'
        )
    );*/

    $data = array(
        'name' => 'Your name',
        'email' => 'Your email',
    );

    $form = $app['form.factory']->createBuilder('form')
        ->add('firstName', 'text', array(
            'constraints' => array(new Assert\NotBlank(), new Assert\Length(array('min' => 5)))
        ))
        ->add('lastName', 'text', array(
            'constraints' => array(new Assert\NotBlank(), new Assert\Length(array('min' => 5)))
        ))
        ->add('email', 'email', array(
            'constraints' => new Assert\Email()
        ))
        ->add('login', 'text', array(
            'constraints' => array(new Assert\NotBlank(), new Assert\Length(array('min' => 3)))
        ))
        ->getForm();

    $form->handleRequest($request);

    if ($form->isValid()) {
        $data = $form->getData();

        // do something with the data

        // redirect somewhere
        return $app->redirect('/');
    }

    return $app['twig']->render('index.html.twig', array('form' => $form->createView()));
});

$app->run();