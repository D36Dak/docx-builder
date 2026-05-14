<?php

declare(strict_types=1);

namespace D36Dak\DocxBuilder\Builder;

use D36Dak\DocxBuilder\Document\DocxDocument;
use D36Dak\DocxBuilder\Document\Elements\ImageElement;
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
        if (is_string($paragraph)) {
            $paragraph = new ParagraphElement($paragraph, $options);
        }

        $this->document->addElement($paragraph);

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

    public function generate(string $outputPath): void
    {
        $renderContext = new RenderContext();

        $xmlContent = $this->document->toXml($renderContext);

        $this->writer->write($outputPath, $xmlContent, $renderContext);
    }
}
