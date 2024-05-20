<?php

namespace Oooiik\LaravelExportPostman\Convert;

use Closure;
use Illuminate\Container\Container;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Str;
use Oooiik\LaravelExportPostman\Helper\HelperInterface;
use Oooiik\LaravelExportPostman\Helper\RouteHelper;
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

    protected $action = [
//        "middleware" => ["api"],
//        "uses" => "App\Http\Controllers\Auth\AuthController@login",
//        "controller" => "App\Http\Controllers\Auth\AuthController@login",
//        "namespace" => null,
//        "prefix" => "api",
//        "where" => [],
    ];

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
        $this->action = $route->action;
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

            // uri
            $uri = $this->route->uri();
            $uriPregReplace = preg_replace('/{([[:alnum:]]+)([?}]+)/', ':$1', $uri, -1);
            preg_match_all('/(?<={)[[:alnum:]]+(?=})/m', $uriPregReplace, $matches);
            $variables = empty($matches[0]) ? collect() : collect($matches[1] ?? $matches[0]);

            $this->requests[$methodType]['request']['url'] = [
                'raw' => "{{{$this->helper->baseUrlKey()}}}/" . $uri,
                'host' => ["{{{$this->helper->baseUrlKey()}}}"],
                'path' => collect(explode('/', $uriPregReplace, PHP_INT_MAX))->filter(),
                'variable' => $variables->transform(function ($variable) {
                    return ['key' => $variable, 'value' => ''];
                })->all(),
            ];

            // rules
            $this->convertRules($methodType);

            if (is_null($this->reflectionMethod())) {
                continue;
            }

            // docs
            $docCom = new DocCommentConvert($this->reflectionMethod());

            $Headers = [];
            foreach ($this->requests[$methodType]['request']['header'] as $item) {
                $Headers[$item['key']] = $item;
            }
            foreach ($docCom->getHeader() ?? [] as $item){
                $Headers[$item['key']] = $item;
            }
            $this->requests[$methodType]['request']['header'] = array_values($Headers);

            $dDescription = $docCom->getDescription();
            if(!empty($dDescription)) {
                $this->requests[$methodType]['request']['description'] = $dDescription;
            }

            if($docCom->hasAuth()) {
                $this->requests[$methodType]['request']['auth'] = $docCom->getAuth();
            }

            if (!array_key_exists('event', $this->requests[$methodType])) {
                $this->requests[$methodType]['event'] = [];
            }

            if ($docCom->hasPreRequestScript()) {
                $this->requests[$methodType]['event'][] = $docCom->getPreRequestScript();
            }

            if ($docCom->hasTestScript()) {
                $this->requests[$methodType]['event'][] = $docCom->getTestScript();
            }



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
        if (is_string($this->action['uses']) && !RouteHelper::containsSerializedClosure($this->action)) {
            $reflection = new ReflectionClass(Str::parseCallback($this->action['uses'])[0]);

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