<?php

namespace Oooiik\LaravelExportPostman\Convert;

use Illuminate\Container\Container;
use Oooiik\LaravelExportPostman\Helper\HelperInterface;

class RuleConvert
{
    /** @var HelperInterface */
    protected $helper;

    protected $convert;

    protected $fieldName;

    protected $rules = [];

    protected function __construct()
    {
        $this->helper = Container::getInstance()->make(HelperInterface::class);
    }

    /**
     * @param string $fieldName
     * @return void
     */
    public function setField(string $fieldName)
    {
        $this->fieldName = $fieldName;
    }

    /**
     * @param array|string $rules
     * @return void
     */
    public function setRules($rules)
    {
        if (is_string($rules)) {
            $rules = [$rules];
        }
        $parsedRules = [];
        foreach ($rules as $rule) {
            if (is_string($rule)) {
               $parsedRules = array_merge($parsedRules, preg_split('/\s*\|\s*/', $rule));
            } elseif (is_object($rule)) {
                if (method_exists($rule, "__toString")) {
                    $parsedRules[] = $rule;
                }
            }

        }

        $this->rules = $parsedRules;

    }


    public static function parse($fieldName, $rules): self
    {
        $new = new self();
        $new->setField($fieldName);
        $new->setRules($rules);
        $new->convert();
        return $new;
    }


    protected function convert()
    {
        $this->convert = [
            'key' => $this->fieldName,
            'value' => $this->helper->formData()[$this->fieldName] ?? null,
            'type' => 'text',
        ];
        $this->convertDescription();
    }

    /**
     * @return void
     */
    protected function convertDescription()
    {
        $this->convert['description'] = implode(', ', $this->rules);
    }

    public function toArray()
    {
        return $this->convert;
    }
}