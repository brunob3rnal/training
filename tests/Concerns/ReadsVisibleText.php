<?php

namespace Tests\Concerns;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMText;

/**
 * Extrae todo el texto que un usuario ve (o lee un lector de pantalla) en un HTML:
 * nodos de texto del <body> y atributos placeholder/title/alt/aria-label, ignorando
 * <script>, <style>, <svg> y lo marcado con aria-hidden. El <title> (nombre de la app) no cuenta.
 */
trait ReadsVisibleText
{
    /**
     * @return list<string>
     */
    protected function visibleTexts(string $html): array
    {
        $dom = new DOMDocument;
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="utf-8" ?>'.$html);
        libxml_clear_errors();

        $texts = [];
        $this->collectVisibleTexts($dom->getElementsByTagName('body')->item(0), $texts);

        return $texts;
    }

    private function collectVisibleTexts(DOMNode $node, array &$texts): void
    {
        foreach ($node->childNodes as $child) {
            if ($child instanceof DOMText) {
                $text = trim(preg_replace('/\s+/u', ' ', $child->nodeValue));
                if ($text !== '') {
                    $texts[] = $text;
                }

                continue;
            }

            if (! $child instanceof DOMElement) {
                continue;
            }

            if (in_array($child->tagName, ['script', 'style', 'svg', 'noscript'], true)
                || $child->getAttribute('aria-hidden') === 'true') {
                continue;
            }

            foreach (['placeholder', 'title', 'alt', 'aria-label'] as $attribute) {
                if (trim($child->getAttribute($attribute)) !== '') {
                    $texts[] = trim($child->getAttribute($attribute));
                }
            }

            if ($child->tagName === 'input' && in_array($child->getAttribute('type'), ['submit', 'button'], true)) {
                $texts[] = $child->getAttribute('value');
            }

            $this->collectVisibleTexts($child, $texts);
        }
    }
}
