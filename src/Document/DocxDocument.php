<?php

declare(strict_types=1);

namespace D36Dak\DocxBuilder\Document;

use D36Dak\DocxBuilder\Document\Elements\DocxElement;
use D36Dak\DocxBuilder\Document\Elements\ParagraphElement;
use D36Dak\DocxBuilder\Renderer\RenderContext;

class DocxDocument
{
    /** @var array<DocxElement> */
    private array $elements = [];
    private ?ParagraphElement $defaultHeader = null;
    private ?ParagraphElement $firstPageHeader = null;
    private ?ParagraphElement $defaultFooter = null;
    private ?ParagraphElement $firstPageFooter = null;

    public function addElement(DocxElement $element): void
    {
        $this->elements[] = $element;
    }

    public function addHeader(ParagraphElement $header): void
    {
        $this->defaultHeader = $header;
    }

    public function addFirstPageHeader(ParagraphElement $header): void
    {
        $this->firstPageHeader = $header;
    }

    public function addFooter(ParagraphElement $footer): void
    {
        $this->defaultFooter = $footer;
    }

    public function addFirstPageFooter(ParagraphElement $footer): void
    {
        $this->firstPageFooter = $footer;
    }

    public function toXml(RenderContext $context): string
    {
        $headerRelationshipIds = $this->registerHeaders($context);
        $footerRelationshipIds = $this->registerFooters($context);

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
        $xml .= $this->renderSectionProperties($headerRelationshipIds, $footerRelationshipIds);
        $xml .= '</w:body>';
        $xml .= '</w:document>';

        return $xml;
    }

    /**
     * @return array{default: string|null, first: string|null}
     */
    private function registerHeaders(RenderContext $context): array
    {
        $defaultRelationshipId = $this->defaultHeader === null
            ? null
            : $context->addHeader($this->defaultHeader->toXml($context));
        $firstRelationshipId = $this->firstPageHeader === null
            ? null
            : $context->addHeader($this->firstPageHeader->toXml($context));

        return [
            'default' => $defaultRelationshipId,
            'first' => $firstRelationshipId ?? $defaultRelationshipId,
        ];
    }

    /**
     * @return array{default: string|null, first: string|null}
     */
    private function registerFooters(RenderContext $context): array
    {
        $defaultRelationshipId = $this->defaultFooter === null
            ? null
            : $context->addFooter($this->defaultFooter->toXml($context));
        $firstRelationshipId = $this->firstPageFooter === null
            ? null
            : $context->addFooter($this->firstPageFooter->toXml($context));

        return [
            'default' => $defaultRelationshipId,
            'first' => $firstRelationshipId ?? $defaultRelationshipId,
        ];
    }

    /**
     * @param array{default: string|null, first: string|null} $headerRelationshipIds
     * @param array{default: string|null, first: string|null} $footerRelationshipIds
     */
    private function renderSectionProperties(array $headerRelationshipIds, array $footerRelationshipIds): string
    {
        $properties = '';
        $usesFirstPage = $this->firstPageHeader !== null || $this->firstPageFooter !== null;

        if ($usesFirstPage) {
            $properties .= '<w:titlePg/>';
        }

        if ($headerRelationshipIds['default'] !== null) {
            $properties .= sprintf(
                '<w:headerReference w:type="default" r:id="%s"/>',
                $headerRelationshipIds['default']
            );
        }

        if ($usesFirstPage && $headerRelationshipIds['first'] !== null) {
            $properties .= sprintf('<w:headerReference w:type="first" r:id="%s"/>', $headerRelationshipIds['first']);
        }

        if ($footerRelationshipIds['default'] !== null) {
            $properties .= sprintf(
                '<w:footerReference w:type="default" r:id="%s"/>',
                $footerRelationshipIds['default']
            );
        }

        if ($usesFirstPage && $footerRelationshipIds['first'] !== null) {
            $properties .= sprintf('<w:footerReference w:type="first" r:id="%s"/>', $footerRelationshipIds['first']);
        }

        if ($properties === '') {
            return '';
        }

        return sprintf('<w:sectPr>%s</w:sectPr>', $properties);
    }
}
