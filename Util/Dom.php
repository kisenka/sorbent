<?php

namespace kisenka\Sorbent\Util;

class Dom
{
    /**
     * @param string $content
     * @param string $charset
     * @return \DOMDocument
     */
    public static function createDomDocument($content, $charset = 'UTF-8')
    {
        $current = libxml_use_internal_errors(true);
        $disableEntities = libxml_disable_entity_loader(true);

        $doc = new \DOMDocument('1.0', $charset);
        $doc->substituteEntities = true;
        $doc->validateOnParse = true;

        $content = mb_convert_encoding($content, 'HTML-ENTITIES', $charset);

        @$doc->loadHTML($content);

        libxml_use_internal_errors($current);
        libxml_disable_entity_loader($disableEntities);

        return $doc;
    }

    /**
     * @param \DOMDocument $doc
     * @return string
     */
    public static function toHtml(\DOMDocument $doc)
    {
        // remove DOCTYPE
        $doc->removeChild($doc->firstChild);

        // remove <html><body></body></html>
        $doc->replaceChild($doc->firstChild->firstChild, $doc->firstChild);

        $html = html_entity_decode($doc->saveHTML(), ENT_QUOTES, 'utf-8');
        $html = str_replace(array('<body>', '</body>'), '', $html);

        return $html;
    }
}
