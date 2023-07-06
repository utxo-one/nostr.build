<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/libs/MultimediaUpload.class.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/libs/S3Service.class.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/libs/db/UsersImages.class.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/libs/db/UsersImagesFolders.class.php';

use DI\Container;
use Slim\Factory\AppFactory;
use Slim\Middleware\ContentLengthMiddleware;

require $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';

// Create Container using PHP-DI
$container = new Container();

// Set container to create App with on AppFactory
AppFactory::setContainer($container);

// Define our container dependencies for injection

// Free upload dependencies
$container->set('freeUpload', function () {
  global $awsConfig;
  global $link;
  // Instantiate S3Service
  $s3 = new S3Service($awsConfig);
  return new MultimediaUpload($link, $s3);
});

// Pro upload dependencies
$container->set('proUpload', function () {
  global $awsConfig;
  global $link;
  // Instantiate S3Service
  $s3 = new S3Service($awsConfig);
  return new MultimediaUpload($link, $s3, true, $_SESSION['usernpub'] ?? '');
});

$container->set('userImages', function () {
  global $link;
  return new UsersImages($link);
});

$container->set('userImagesFolders', function () {
  global $link;
  return new UsersImagesFolders($link);
});

// Create app
$app = AppFactory::create();
$app->setBasePath('/api/v2');
$app->addErrorMiddleware(true, true, true);
$app->addBodyParsingMiddleware();

require_once __DIR__ . '/routes_upload.php'; // Include free upload routes
require_once __DIR__ . '/routes_uppy.php'; // Include uppy upload routes
require_once __DIR__ . '/routes_account.php'; // Include pro account routes

$contentLengthMiddleware = new ContentLengthMiddleware();
$app->add($contentLengthMiddleware);
$app->run();
