<?php

namespace Oooiik\LaravelExportPostman\Convert;

use Oooiik\LaravelExportPostman\Helper\HelperInterface;
use Illuminate\Container\Container;
use Illuminate\Routing\Route;

class DocCommentConvert
{
    /** @var HelperInterface */
    protected $helper;

    /** @var \ReflectionMethod|\ReflectionFunction */
    protected $ref;

    protected $docs = [
//        "AuthBearerToken" => "{{token}}"
    ];

    /**
     * @param \ReflectionMethod|\ReflectionFunction $ref
     */
    public function __construct($ref)
    {
        $this->helper = Container::getInstance()->make(HelperInterface::class);
        $this->ref = $ref;
        $this->docCommentToArray();
//        dd($this->docs);
    }

    /**
     * @return array
     */
    public function docCommentToArray()
    {
        if (!empty($this->annotations)) {
            return $this->annotations;
        }
        $docComment = $this->ref->getDocComment();

        preg_match_all('/@(\w+)\s+([^\r\n]*)/', $docComment, $matches, PREG_SET_ORDER);

        $result = [];
        foreach ($matches as $match) {
            $tag = $match[1];
            $value = $match[2];

            if (isset($result[$tag])) {
                if (!is_array($result[$tag])) {
                    $result[$tag] = [$result[$tag]];
                }
                $result[$tag][] = $value;
            } else {
                $result[$tag] = $value;
            }
        }

        $this->docs = $result;
        return $this->docs;
    }

    public function hasAuth()
    {
        return !empty(array_intersect(['AuthNo', 'AuthParent', 'AuthBearer'], array_keys($this->docs)));
    }
    /**
     * @return array|null
     */
    public function getAuth()
    {
        if (array_key_exists('AuthNo', $this->docs)) {
            return [
                "type" => "noauth"
            ];
        }

        if (array_key_exists('AuthParent', $this->docs)) {
            return null;
        }

        if (array_key_exists('AuthBearer', $this->docs)) {
            return [
                "type" => "bearer",
                "bearer" => [
                    [
                        "key" => "token",
                        "value" => $this->docs['AuthBearer'],
                        "type" => "string"
                    ]
                ]
            ];
        }

        return null;
    }

    /**
     * @return array|null
     */
    public function getHeader()
    {
        if (!array_key_exists('Header', $this->docs)) {
            return null;
        }

        $headers = is_array($this->docs['Header']) ? $this->docs['Header'] : [$this->docs['Header']];

        $res = [];
        foreach ($headers as $header) {
            $exp = explode('=>', $header);
            if (count($exp) !== 2) {
                continue;
            }
            $res[] = [
                "key" => trim($exp[0]),
                "value" => trim($exp[1]),
                "type" => "text"
            ];
        }
        return $res;
    }

    public function hasPreRequestScript(): bool
    {
        return !empty(array_intersect([
            'PreRequestScriptContext',
            'PreRequestScriptFileBasePath',
            'PreRequestScriptFileResourcePath',
            ], array_keys($this->docs)));
    }
    public function getPreRequestScript()
    {
        if (!array_key_exists('PreRequestScriptContext', $this->docs)
            && !array_key_exists('PreRequestScriptFileBasePath', $this->docs)
            && !array_key_exists('PreRequestScriptFileResourcePath', $this->docs)) {
            return null;
        }

        $exec = [];

        if (array_key_exists('PreRequestScriptContext', $this->docs)) {
            if (is_array($this->docs['PreRequestScriptContext'])) {
                $exec = array_merge($exec, $this->docs['PreRequestScriptContext']);
            } else {
                $exec[] = $this->docs['PreRequestScriptContext'];
            }
        }

        if (array_key_exists('PreRequestScriptFileBasePath', $this->docs)) {
            $paths = is_array($this->docs['PreRequestScriptFileBasePath'])
                ? $this->docs['PreRequestScriptFileBasePath']
                : [$this->docs['PreRequestScriptFileBasePath']];
            foreach ($paths as $path) {
                $path = base_path($path);
                if (file_exists($path)) {
                    $exec[] = file_get_contents($path);
                }
            }
        }

        if (array_key_exists('PreRequestScriptFileResourcePath', $this->docs)) {
            $paths = is_array($this->docs['PreRequestScriptFileResourcePath'])
                ? $this->docs['PreRequestScriptFileResourcePath']
                : [$this->docs['PreRequestScriptFileResourcePath']];
            foreach ($paths as $path) {
                $path = resource_path($path);
                if (file_exists($path)) {
                    $exec[] = file_get_contents($path);
                }
            }
        }

        return [
            "listen" => "prerequest",
            "script" => [
                "exec" => $exec,
                "type" => "text/javascript",
                "packages" => []
            ]
        ];
    }


    public function hasTestScript(): bool
    {
        return !empty(array_intersect([
            'TestScriptContext',
            'TestScriptFileBasePath',
            'TestScriptFileResourcePath',
        ], array_keys($this->docs)));
    }

    public function getTestScript()
    {
        if (!array_key_exists('TestScriptContext', $this->docs)
            && !array_key_exists('TestScriptFileBasePath', $this->docs)
            && !array_key_exists('TestScriptFileResourcePath', $this->docs)) {
            return null;
        }

        $exec = [];

        if (array_key_exists('TestScriptContext', $this->docs)) {
            if (is_array($this->docs['TestScriptContext'])) {
                $exec = array_merge($exec, $this->docs['TestScriptContext']);
            } else {
                $exec[] = $this->docs['TestScriptContext'];
            }
        }

        if (array_key_exists('TestScriptFileBasePath', $this->docs)) {
            $paths = is_array($this->docs['TestScriptFileBasePath'])
                ? $this->docs['TestScriptFileBasePath']
                : [$this->docs['TestScriptFileBasePath']];
            foreach ($paths as $path) {
                $path = base_path($path);
                if (file_exists($path)) {
                    $exec[] = file_get_contents($path);
                }
            }
        }

        if (array_key_exists('TestScriptFileResourcePath', $this->docs)) {
            $paths = is_array($this->docs['TestScriptFileResourcePath'])
                ? $this->docs['TestScriptFileResourcePath']
                : [$this->docs['TestScriptFileResourcePath']];
            foreach ($paths as $path) {
                $path = resource_path($path);
                if (file_exists($path)) {
                    $exec[] = file_get_contents($path);
                }
            }
        }

        return [
            "listen" => "test",
            "script" => [
                "exec" => $exec,
                "type" => "text/javascript",
                "packages" => []
            ]
        ];
    }

    public function getDescription()
    {
        if (!array_key_exists('DescriptionContext', $this->docs)
            && !array_key_exists('DescriptionBasePath', $this->docs)
            && !array_key_exists('DescriptionResourcePath', $this->docs)) {
            return null;
        }

        $text = "";

        if (array_key_exists('DescriptionContext', $this->docs)) {
            if (is_array($this->docs['DescriptionContext'])) {
                $text .= implode("\n", $this->docs['DescriptionContext']);
            } else {
                $text .= $this->docs['DescriptionContext'];
            }
        }

        if (array_key_exists('DescriptionBasePath', $this->docs)) {
            $paths = is_array($this->docs['DescriptionBasePath'])
                ? $this->docs['DescriptionBasePath']
                : [$this->docs['DescriptionBasePath']];
            foreach ($paths as $path) {
                $path = base_path($path);
                if (file_exists($path)) {
                    if(!empty($text)) {
                        $text .= "\n";
                    }
                    $text .= file_get_contents($path);
                }
            }
        }

        if (array_key_exists('DescriptionResourcePath', $this->docs)) {
            $paths = is_array($this->docs['DescriptionResourcePath'])
                ? $this->docs['DescriptionResourcePath']
                : [$this->docs['DescriptionResourcePath']];
            foreach ($paths as $path) {
                $path = resource_path($path);
                if (file_exists($path)) {
                    if(!empty($text)) {
                        $text .= "\n";
                    }
                    $text .= file_get_contents($path);
                }
            }
        }

        return $text;
    }
}