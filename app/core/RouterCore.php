<?php

namespace app\core;

class RouterCore {

  private $uri;
  private $method;
  private $getArr = [];

  public function __construct () {
    $this->initialize();
    require_once('../app/config/Router.php');
    $this->execute();
  }

  private function initialize () {
    $this->method = $_SERVER['REQUEST_METHOD'];
    $uri = $_SERVER['REQUEST_URI'];

    $ex = explode('/', $uri);
    $uri = $this->normalizeURI($ex);

    for ($i = 0; $i < UNSET_URI_COUNT; $i++) {
      unset($uri[$i]);
    }

    $this->uri = implode('/', $this->normalizeURI($uri));
  }

  private function normalizeURI ($arr) {
    return array_values(array_filter($arr));
  }

  private function get ($router, $call) {
    $this->getArr[] = [
      'router' => $router,
      'call' => $call
    ];
  }

  private function execute () {
    switch ($this->method) {
      case 'GET':
        $this->executeGet();
        break;
      case 'POST':
        // $this->executePost();
        break;
    }
  }

  private function executeGet () {
    foreach ($this->getArr as $get) {
      $r = substr($get['router'], 1);
      if ($r == $this->uri) {
        if (is_callable($get['call'])) {
          $get['call']();
          break;
        } else {
          $this->executeController($get['call']);
        }
      }
    }
  }

  private function executeController ($get) {
    $ex = explode('@', $get);
    if (!isset($ex[0], $ex[1])) {
      (new \app\controller\ErrorController)->error_404();
      return;
    }

    $cont = 'app\\controller\\' . $ex[0];
    if (!class_exists($cont)) {
      (new \app\controller\ErrorController)->error_404();
      return;
    }

    if (!method_exists($cont, $ex[1])) {
      (new \app\controller\ErrorController)->error_404();
      return;
    }

    call_user_func_array([
      new $cont,
      $ex[1]
    ], []);
  }
}
