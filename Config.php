<?php

namespace kisenka\Sorbent;

class Config
{
    /**
     * @var array
     */
    protected $rules = array();

    /**
     * @var \Closure[]
     */
    protected $callbacks = array();

    /**
     * @param array $config
     */
    public function __construct(array $config = null)
    {
        if (is_array($config)) {
            foreach ($config as $optionKey => $optionValue) {
                if (is_numeric($optionKey)) {
                    $this->setRule($optionValue);
                } else {
                    $this->setRule($optionKey, $optionValue);
                }
            }
        } else {
            $this->setDefaults();
        }
    }

    /**
     * @return void
     */
    private function setDefaults()
    {
        // Global attributes
        $this->setRule('*', array(
            'lang',
            'class',
            'id',
            'title',
            'role',
            'data-*',
        ));

        // A
        $this->setRule('address');
        $this->setRule('a', array(
            'href',
            'target',
            'rel',
            'rev',
        ));
        $this->setRule('abbr');
        $this->setRule('acronym');
        $this->setRule('article');
        $this->setRule('aside');

        // B
        $this->setRule('b');
        $this->setRule('button', array(
            'disabled',
            'name',
            'type',
            'value',
        ));

        // C
        $this->setRule('caption');
        $this->setRule('cite');
        $this->setRule('col');

        // D
        $this->setRule('del');
        $this->setRule('dd');
        $this->setRule('details');
        $this->setRule('div');
        $this->setRule('dl');
        $this->setRule('dt');

        // E
        $this->setRule('em');

        // F
        $this->setRule('figure');
        $this->setRule('figcaption');
        $this->setRule('footer');

        // H
        $this->setRule('h1');
        $this->setRule('h2');
        $this->setRule('h3');
        $this->setRule('h4');
        $this->setRule('h5');
        $this->setRule('h6');
        $this->setRule('header');
        $this->setRule('hgroup');
        $this->setRule('hr');

        // I
        $this->setRule('i');
        $this->setRule('img', array(
            'alt',
            'border',
            'width',
            'height',
            'src',
        ));
        $this->setRule('ins');

        // K
        $this->setRule('kbd');

        // L
        $this->setRule('li');

        // M
        $this->setRule('menu');

        // N
        $this->setRule('nav');

        // P
        $this->setRule('p');
        $this->setRule('pre');

        // Q
        $this->setRule('q');

        // S
        $this->setRule('s');
        $this->setRule('span');
        $this->setRule('section');
        $this->setRule('small');
        $this->setRule('strong');
        $this->setRule('sub');
        $this->setRule('summary');
        $this->setRule('sup');

        // T
        $this->setRule('table');
        $this->setRule('tbody');
        $this->setRule('td', array(
            'colspan',
            'rowspan',
            'width',
            'height',
            'align' => array('left', 'right', 'center'),
            'valign',
        ));
        $this->setRule('tr');
        $this->setRule('tt');

        // U
        $this->setRule('u');
        $this->setRule('ul', array(
            'type',
        ));

        // O
        $this->setRule('ol', array(
            'start',
            'type',
        ));

        // V
        $this->setRule('var');
    }

    /**
     * @param string $expr
     * @return array Will return empty array if is no rules defined yet
     */
    public function getRule($expr)
    {
        $rule = isset($this->rules[$expr])
            ? $this->rules[$expr]
            : null;

        return $rule;
    }

    /**
     * @param $expr
     * @param array $cfg
     * @param callable $callback
     * @return void
     */
    public function setRule($expr, array $cfg = null, \Closure $callback = null)
    {
        $cfg = (array) $cfg;
        $config = array();

        if (!empty($cfg)) {
            $attrName = null;

            // Do some restructuring with base config
            foreach ($cfg as $paramKey => $paramValue) {
                $hasAttrsList = is_string($paramKey);

                $attrName = ($hasAttrsList)
                    ? $paramKey
                    : $paramValue;

                $config[$attrName] = ($hasAttrsList)
                    ? $paramValue
                    : true;
            }
        }

        $this->rules[$expr] = $config;

        if ($callback) {
            $this->callbacks[$expr] = $callback;
        }
    }

    /**
     * @param $expr
     * @param array $extraCfg
     * @param callable $callback
     * @return bool
     */
    public function extendRule($expr, array $extraCfg = null, \Closure $callback = null)
    {
        $rule = $this->getRule($expr);

        if ($rule !== null) {
            $this->setRule($expr, array_merge($rule, (array) $extraCfg), $callback);
            return true;
        }

        return false;
    }

    /**
     * @param string $nodeName
     * @return array|null
     */
    public function getMatchedRules($nodeName)
    {
        $nodeRule = $this->getRule($nodeName);
        $config = array(
            'is_allowed' => false,
            'attrs' => array(),
            'callback' => null
        );

        // If there is some rules about $nodeName
        if ($nodeRule !== null) {
            $config['is_allowed'] = true;

            // Attributes
            if (!empty($nodeRule)) {
                $config['attrs'] = $nodeRule;
            }

            // Callback
            if (isset($this->callbacks[$nodeName])) {
                $config['callback'] = $this->callbacks[$nodeName];
            }

            // Global selector
            if (isset($this->rules['*'])) {
                $config['attrs'] = array_merge($this->rules['*'], $config['attrs']);
            }
        }

        return $config;
    }
}
