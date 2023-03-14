<?php

namespace core\Http;

use stdClass;
use core\Config;

class Controller
{
    protected Request  $request;
    protected Response $response;
    protected Plaster  $plaster;
    protected Http     $http;
    protected array    $assign = array();

    public function __construct(stdClass $base)
    {
        $this->request  = $base->request;
        $this->response = $base->response;
        $this->plaster  = $base->plaster;
        $this->http     = $base->http;
    }

    public function assign(string $key, mixed $value): void
    {
        $this->assign[$key] = $value;
    }

    public function json($data): Controller
    {
        if (is_array($data)) {
            $data = json_encode($data);
        }
        $this->header('Content-Type', 'application/json');
        return $data;
    }

    public function header(string $key, string $value): void
    {
        $this->response->setHeader($key, $value);
    }

    public function __toString(): string
    {
        // TODO: Implement __toString() method.
        $templateInfo               = debug_backtrace()[1];
        $class                      = $templateInfo['class'];
        $function                   = $templateInfo['function'];
        $controllerName             = strtolower(substr($class, strrpos($class, '\\') + 1));
        $functionToTemplateFileName = strtolower(preg_replace('/([A-Z])/', "$0_", $function));
        $template                   = file_get_contents(TMP_PATH . FS . $controllerName . FS . $functionToTemplateFileName . '.' . Config::get('http.template_extension'));
        $template                   = $this->plaster->apply($template, $this->assign);
        if (!is_string($template)) {
            return $this->http->httpErrorHandle($template->getcode(), $template->getMessage(), $template->getFile(), $template->getLine(), 503);
        }
        return $template;
    }
}