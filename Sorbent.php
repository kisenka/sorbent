<?php

namespace kisenka\Sorbent;

use kisenka\Sorbent\Config;
use kisenka\Sorbent\Util\Dom;

class Sorbent
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var \DomNode[]
     */
    public $nodesToRemove = array();

    /**
     * @param Config $config
     */
    public function __construct(Config $config = null)
    {
        if ($config === null) {
            $config = new Config();
        }

        $this->config = $config;
    }


    /**
     * @return Config
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param Config $config
     * @return void
     */
    public function setConfig(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @param $content
     * @param callable $callback
     * @return string
     */
    public function filter($content, \Closure $callback = null)
    {
        $doc = Dom::createDomDocument($content);
        $body = $doc->getElementsByTagName('body')->item(0);
        $nodesCount = $body->childNodes->length;

        for ($i = 0; $i < $nodesCount; $i++) {
            $childNode = $body->childNodes->item($i);

            if ($childNode !== null) {
                $this->filterNode($childNode, $callback);
            }
        }

        if (!empty($this->nodesToRemove)) {
            foreach ($this->nodesToRemove as $node) {
                $node->parentNode->removeChild($node);
            }
            $this->nodesToRemove = array();
        }

        $str = Dom::toHtml($doc);

        return $str;
    }

    /**
     * @param \DOMNode $node
     * @param callable $callback
     * @return \DOMNode|null
     */
    private function filterNode(\DOMNode $node, \Closure $callback = null)
    {
        $nodeConfig = $this->config->getMatchedRules($node->nodeName);
        $isNodeAllowed = $nodeConfig['is_allowed'];
        $nodeAllowedAttributes = $nodeConfig['attrs'];
        $nodeCallback = $nodeConfig['callback'];
        $isElement = $node->nodeType === XML_ELEMENT_NODE;

        // Node is not allowed
        if (!$isNodeAllowed && $isElement) {
            $this->nodesToRemove[] = $node;
            return null;
        }

        // Node attributes
        if ($node->hasAttributes()) {
            $this->filterNodeAttributes($node, $nodeAllowedAttributes);
        }

        // Node callback
        if ($nodeCallback !== null && $isElement) {
            $nodeCallback($node);
        }

        // Global callback
        if ($callback !== null && $isElement) {
            $callback($node);
        }

        // Node childs
        if ($node->hasChildNodes()) {
            foreach ($node->childNodes as $child) {
                $this->filterNode($child, $callback);
            }
        }

        return $node;
    }

    /**
     * @param \DOMNode $node
     * @param array $allowedAttributes
     */
    private function filterNodeAttributes(\DOMNode $node, array $allowedAttributes)
    {
        $removeAttributes = array();

        foreach ($node->attributes as $attribute) {
            if (!$this->isAttributeAllowed($attribute, $allowedAttributes)) {
                $removeAttributes[] = $attribute->nodeName;
            }
        }

        if (!empty($removeAttributes)) {
            foreach ($removeAttributes as $removeAttribute) {
                $node->removeAttribute($removeAttribute);
            }
        }
    }

    /**
     * @param \DOMAttr $attribute
     * @param array $allowedAttributes
     * @return bool
     */
    private function isAttributeAllowed(\DOMAttr $attribute, array $allowedAttributes)
    {
        $isAllowed = false;
        $attributeName = $attribute->nodeName;

        foreach ($allowedAttributes as $allowedAttrName => $allowedAttrValue) {
            if (strpos($allowedAttrName, '*') !== false) {
                $attributeNamePiece = substr($allowedAttrName, 0, strpos($allowedAttrName, '*'));

                if (strpos($attributeName, $attributeNamePiece) === 0) {
                    $isAllowed = true;
                    break;
                }

            } else if ($allowedAttrName === $attributeName) {
                $isAllowed = true;
                break;
            }
        }

        return $isAllowed;
    }
}
