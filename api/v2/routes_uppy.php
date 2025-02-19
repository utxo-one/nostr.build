<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/config.php';
require_once __DIR__ . '/helper_functions.php';

require $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;

$app->group('/uppy', function (RouteCollectorProxy $group) {
  // Route to upload file(s) via form
  $group->post('/files', function (Request $request, Response $response) {
    $files = $request->getUploadedFiles();

    // If no files are provided, return a 400 response
    if (empty($files)) {
      return uppyResponse($response, 'error', 'No files provided', new stdClass(), 400);
    }
    $upload = $this->get('freeUpload');

    try {
      // Handle exceptions thrown by the MultimediaUpload class
      $upload->setPsrFiles($files);
      $data = ($upload->uploadFiles()) ? $upload->getUploadedFiles() : new stdClass();
      return uppyResponse($response, 'success', 'Files uploaded successfully', $data);
    } catch (\Exception $e) {
      return uppyResponse($response, 'error', 'Upload failed: ' . $e->getMessage(), new stdClass(), 500);
    }
  });

  $group->get('/ping', function (Request $request, Response $response) {
    $response->getBody()->write('pong');
    return $response;
  });
});
