<?php

namespace Oooiik\LaravelExportPostman\Convert;

use Closure;
use Illuminate\Container\Container;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Str;
use Oooiik\LaravelExportPostman\Helper\HelperInterface;
use Oooiik\LaravelExportPostman\Utils\ObjUtil;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionParameter;

class RouteConvert
{
    /** @var HelperInterface */
    protected $helper;

    /** @var Route */
    protected $route;

    protected $requests = [
//       [
//           'name' => null,
//           'request' => [
//               'method' => null,
//               'header' => null,
//               'url' => [],
//               'body' => [],
//               'protocolProfileBehavior' => [
//                   'disableBodyPruning' => true
//               ]
//           ]
//       ]
    ];

    /**
     * @throws BindingResolutionException|ReflectionException
     */
    public function __construct(Route $route)
    {
        $this->helper = Container::getInstance()->make(HelperInterface::class);
        $this->route = $route;
        $this->convert();
    }

    /**
     * @return array
     */
    protected function methodTypes(): array
    {
        return array_unique(array_filter($this->route->methods(), function ($type) {
            return $type !== "HEAD" && !empty($type);
        }));
    }

    /**
     * @throws ReflectionException
     */
    public function convert()
    {
        foreach ($this->methodTypes() as $methodType) {
            $this->requests[$methodType]['name'] = $this->route->uri();
            $this->requests[$methodType]['request']['method'] = strtoupper($methodType);
            $this->requests[$methodType]['request']['header'] = $this->helper->headers();
            $this->requests[$methodType]['protocolProfileBehavior'] = [
                'disableBodyPruning' => true
            ];

            $uri = Str::of($this->route->uri())->replaceMatches('/{([[:alnum:]]+)([?}]+)/', ':$1');
            $variables = $uri->matchAll('/(?<={)[[:alnum:]]+(?=})/m');
            $this->requests[$methodType]['request']['url'] = [
                'raw' => "{{{$this->helper->baseUrlKey()}}}/" . $uri,
                'host' => ["{{{$this->helper->baseUrlKey()}}}"],
                'path' => $uri->explode('/')->filter(),
                'variable' => $variables->transform(function ($variable) {
                    return ['key' => $variable, 'value' => ''];
                })->all(),
            ];

            $this->convertRules($methodType);
        }
    }

    /**
     * @throws ReflectionException
     */
    protected function convertRules($method)
    {
        /** @var ReflectionParameter $reflectionRulesParameter */
        $reflectionRulesMethod = $this->reflectionMethod();
        if (empty($reflectionRulesMethod)) {
            $this->requests[$method]['request']['body'] = [];
            return;
        }
        $reflectionRulesParameter = collect($reflectionRulesMethod->getParameters())
            ->filter(function (ReflectionParameter $value) {
                return $value->getType() && is_subclass_of($value->getType()->getName(), Request::class);
            })
            ->first();
        if (empty($reflectionRulesParameter)) {
            $this->requests[$method]['request']['body'] = [];
            return;
        }
        $reflectionClassName = $reflectionRulesParameter->getType()->getName();
        $rulesParameter = new $reflectionClassName;
        $rules = method_exists($rulesParameter, 'rules') ? $rulesParameter->rules() : [];
        $requestRules = [];
        foreach ($rules as $fieldName => $rule) {
            $ruleConvert = RuleConvert::parse($fieldName, $rule);
            ObjUtil::object_set($requestRules, $ruleConvert->getField(), $ruleConvert->toContent());
        }
        $this->requests[$method]['request']['body'] = [
            'mode' => $this->helper->contentTypePostman(),
        ];
        if (in_array($this->helper->contentType(), ["form-data", "x-www-form-urlencoded"])) {
            $this->requests[$method]['request']['body'][$this->helper->contentTypePostman()] = array_values($requestRules);
        } elseif ($this->helper->contentType() == "json") {
            $this->requests[$method]['request']['body'][$this->helper->contentTypePostman()] = json_encode($requestRules);
        }
    }

    /**
     * @return null|ReflectionFunction|ReflectionMethod
     * @throws ReflectionException
     */
    protected function reflectionMethod()
    {
        if (!empty($this->route->getControllerClass())) {
            $reflection = new ReflectionClass($this->route->getControllerClass());

            if (!$reflection->hasMethod($this->route->getActionMethod())) {
                return null;
            }
            return $reflection->getMethod($this->route->getActionMethod());
        }


        if ($this->route->getAction('uses') instanceof Closure) {
            return new ReflectionFunction($this->route->getAction('uses'));
        }

        if (is_string($this->route->getAction('uses'))) {
            $obj = unserialize($this->route->getAction('uses'));
            if(!$obj instanceof Closure && method_exists($obj, "getClosure")){
                $obj = $obj->getClosure();
            }
            if ($obj instanceof Closure) {
                return new ReflectionFunction($obj);
            }
        }

        return null;
    }

    /**
     * @return bool
     * @throws ReflectionException
     */
    public function hasReflectionMethod(): bool
    {
        return !!$this->reflectionMethod();
    }

    public function toArrays(): array
    {
        return array_values($this->requests);
    }

    public function path(): string
    {
        $routeNames = explode('/', $this->route->uri());
        $uri = '';
        $level = 0;
        foreach ($routeNames as $routeName) {
            if (!empty($uri)) $uri .= "/";
            $uri .= $routeName;
            if (in_array($uri, array_keys($this->helper->folders()))) {
                if (array_key_exists('level', $this->helper->folders()[$uri])) {
                    $level = $this->helper->folders()[$uri]['level'];
                }
            }
        }
        if ($level != 0) {
            $routeNames = array_slice($routeNames, 0, $level);
        }
        return implode('/', $routeNames);
    }

}