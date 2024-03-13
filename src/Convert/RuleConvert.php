<?php

namespace Oooiik\LaravelExportPostman\Convert;

use Illuminate\Container\Container;
use Oooiik\LaravelExportPostman\Helper\HelperInterface;

class RuleConvert
{
    /** @var HelperInterface */
    protected $helper;

    protected $convert;

    protected $fieldOriginal;
    protected $field;

    /** @var array */
    protected $rules = [];

    /** @var mixed */
    protected $value;

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
        $this->fieldOriginal = $fieldName;
        $this->field = str_replace(
            [".*.", ".*"],
            ["[]", "[]"],
            $this->fieldOriginal
        );
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

    protected function setValue()
    {
        if (in_array('integer', $this->rules)) {
            $this->value = 1;
        } elseif (in_array('string', $this->rules)) {
            $this->value = "string";
        } elseif (in_array("numeric", $this->rules)) {
            $this->value = 1.23;
        } elseif (in_array("boolean", $this->rules)) {
            $this->value = true;
        } elseif (in_array("email", $this->rules)) {
            $this->value = "user@example.com";
        } elseif (in_array("array", $this->rules)) {
            $this->value = [];
        } elseif (in_array("nullable", $this->rules)) {
            $this->value = null;
        }
    }


    public static function parse($fieldName, $rules): self
    {
        $new = new self();
        $new->setField($fieldName);
        $new->setRules($rules);
        $new->setValue();
        $new->convert();
        return $new;
    }


    protected function convert()
    {
        $this->convert = [
            'key' => $this->field,
            'value' => $this->helper->formData()[$this->fieldOriginal] ?? $this->value,
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