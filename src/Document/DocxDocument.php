<?php

declare(strict_types=1);

namespace D36Dak\DocxBuilder\Document;

use D36Dak\DocxBuilder\Document\Elements\DocxElement;
use D36Dak\DocxBuilder\Renderer\RenderContext;

class DocxDocument
{
    /** @var array<DocxElement> */
    private array $elements = [];

    public function addElement(DocxElement $element): void
    {
        $this->elements[] = $element;
    }

    public function toXml(RenderContext $context): string
    {
        $xml = '<w:document'
            . ' xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"'
            . ' xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"'
            . ' xmlns:wp="http://schemas.openxmlformats.org/drawingml/2006/wordprocessingDrawing"'
            . ' xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main"'
            . ' xmlns:pic="http://schemas.openxmlformats.org/drawingml/2006/picture"'
            . '>';
        $xml .= '<w:body>';
        foreach ($this->elements as $element) {
            $xml .= $element->toXml($context);
        }
        $xml .= '</w:body>';
        $xml .= '</w:document>';

        return $xml;
    }
}
