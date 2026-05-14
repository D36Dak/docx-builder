<?php

declare(strict_types=1);

namespace D36Dak\DocxBuilder\Builder;

use D36Dak\DocxBuilder\Document\DocxDocument;
use D36Dak\DocxBuilder\Document\Elements\ImageElement;
use D36Dak\DocxBuilder\Document\Elements\PageBreakElement;
use D36Dak\DocxBuilder\Document\Elements\ParagraphElement;
use D36Dak\DocxBuilder\Document\Elements\TableElement;
use D36Dak\DocxBuilder\Renderer\RenderContext;
use D36Dak\DocxBuilder\Writer\DocxWriter;

class DocxBuilder
{
    private DocxDocument $document;
    private DocxWriter $writer;

    public function __construct()
    {
        $this->document = new DocxDocument();
        $this->writer = new DocxWriter();
    }

    /**
     * Calling this method multiple times overwrites the default header instead of appending.
     *
     * @param array{
     *     alignment?: 'left'|'right'|'center'|'both',
     *     spacingBefore?: int,
     *     spacingAfter?: int,
     *     lineSpacing?: float,
     *     fontFamily?: string,
     *     fontSize?: float,
     *     color?: string,
     *     bold?: bool,
     *     italic?: bool,
     *     underline?: bool,
     * } $options Array of options for the paragraph. Only applies if $paragraph is a string.
     * Otherwise pass default options to ParagraphBuilder constructor.
     */
    public function addParagraph(string|ParagraphElement $paragraph, array $options = []): self
    {
        $this->document->addElement($this->normalizeParagraph($paragraph, $options));

        return $this;
    }

    public function addPageBreak(): self
    {
        $this->document->addElement(new PageBreakElement());

        return $this;
    }

    /**
     * Calling this method multiple times overwrites the first-page header instead of appending.
     *
     * @param array{
     *     alignment?: 'left'|'right'|'center'|'both',
     *     spacingBefore?: int,
     *     spacingAfter?: int,
     *     lineSpacing?: float,
     *     fontFamily?: string,
     *     fontSize?: float,
     *     color?: string,
     *     bold?: bool,
     *     italic?: bool,
     *     underline?: bool,
     * } $options Array of options for the header. Only applies if $header is a string.
     * Otherwise pass default options to ParagraphBuilder constructor.
     */
    public function addHeader(string|ParagraphElement $header, array $options = []): self
    {
        $this->document->addHeader($this->normalizeParagraph($header, $options));

        return $this;
    }

    /**
     * Calling this method multiple times overwrites the default footer instead of appending.
     *
     * @param array{
     *     alignment?: 'left'|'right'|'center'|'both',
     *     spacingBefore?: int,
     *     spacingAfter?: int,
     *     lineSpacing?: float,
     *     fontFamily?: string,
     *     fontSize?: float,
     *     color?: string,
     *     bold?: bool,
     *     italic?: bool,
     *     underline?: bool,
     * } $options Array of options for the header. Only applies if $header is a string.
     * Otherwise pass default options to ParagraphBuilder constructor.
     */
    public function addFirstPageHeader(string|ParagraphElement $header, array $options = []): self
    {
        $this->document->addFirstPageHeader($this->normalizeParagraph($header, $options));

        return $this;
    }

    /**
     * Calling this method multiple times overwrites the first-page footer instead of appending.
     *
     * @param array{
     *     alignment?: 'left'|'right'|'center'|'both',
     *     spacingBefore?: int,
     *     spacingAfter?: int,
     *     lineSpacing?: float,
     *     fontFamily?: string,
     *     fontSize?: float,
     *     color?: string,
     *     bold?: bool,
     *     italic?: bool,
     *     underline?: bool,
     * } $options Array of options for the footer. Only applies if $footer is a string.
     * Otherwise pass default options to ParagraphBuilder constructor.
     */
    public function addFooter(string|ParagraphElement $footer, array $options = []): self
    {
        $this->document->addFooter($this->normalizeParagraph($footer, $options));

        return $this;
    }

    /**
     * @param array{
     *     alignment?: 'left'|'right'|'center'|'both',
     *     spacingBefore?: int,
     *     spacingAfter?: int,
     *     lineSpacing?: float,
     *     fontFamily?: string,
     *     fontSize?: float,
     *     color?: string,
     *     bold?: bool,
     *     italic?: bool,
     *     underline?: bool,
     * } $options Array of options for the footer. Only applies if $footer is a string.
     * Otherwise pass default options to ParagraphBuilder constructor.
     */
    public function addFirstPageFooter(string|ParagraphElement $footer, array $options = []): self
    {
        $this->document->addFirstPageFooter($this->normalizeParagraph($footer, $options));

        return $this;
    }

    /**
     * @param array<array<string>> $rows
     * @param array{
     *     headerRowCount?: int,
     *     cellOptions?: array{
     *         alignment?: 'left'|'right'|'center'|'both',
     *         spacingBefore?: int,
     *         spacingAfter?: int,
     *         lineSpacing?: float,
     *         fontFamily?: string,
     *         fontSize?: float,
     *         color?: string,
     *         bold?: bool,
     *         italic?: bool,
     *         underline?: bool,
     *     },
     *     headerCellOptions?: array{
     *         alignment?: 'left'|'right'|'center'|'both',
     *         spacingBefore?: int,
     *         spacingAfter?: int,
     *         lineSpacing?: float,
     *         fontFamily?: string,
     *         fontSize?: float,
     *         color?: string,
     *         bold?: bool,
     *         italic?: bool,
     *         underline?: bool,
     *     },
     * } $options
     */
    public function addTable(array $rows, array $options = []): self
    {
        $this->document->addElement(new TableElement($rows, $options));

        return $this;
    }

    /**
     * @param array{
     *     width: int,
     *     height: int,
     *     alignment?: 'left'|'right'|'center',
     *     altText?: string,
     * } $options
     */
    public function addImage(string $imagePath, array $options): self
    {
        $this->document->addElement(new ImageElement($imagePath, $options));

        return $this;
    }

    /**
     * Generates the document and saves it to the specified path.
     * @param string $outputPath Path to which the generated document will be saved.
     * @return void
     */
    public function save(string $outputPath): void
    {
        $renderContext = new RenderContext();

        $xmlContent = $this->document->toXml($renderContext);

        $this->writer->write($outputPath, $xmlContent, $renderContext);
    }

    /**
     * Generates the document and returns the DOCX binary contents as a string.
     *
     * @return string The binary contents of the generated DOCX file.
     */
    final public function getContents(): string
    {
        $renderContext = new RenderContext();

        $xmlContent = $this->document->toXml($renderContext);

        return $this->writer->writeToString($xmlContent, $renderContext);
    }

    /**
     * @param array{
     *     alignment?: 'left'|'right'|'center'|'both',
     *     spacingBefore?: int,
     *     spacingAfter?: int,
     *     lineSpacing?: float,
     *     fontFamily?: string,
     *     fontSize?: float,
     *     color?: string,
     *     bold?: bool,
     *     italic?: bool,
     *     underline?: bool,
     * } $options
     */
    private function normalizeParagraph(string|ParagraphElement $paragraph, array $options): ParagraphElement
    {
        if (is_string($paragraph)) {
            return new ParagraphElement($paragraph, $options);
        }

        return $paragraph;
    }
}
