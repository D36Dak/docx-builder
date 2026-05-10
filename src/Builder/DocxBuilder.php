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

    public function addParagraph(string $text): self
    {
        $this->document->addElement(new ParagraphElement($text));

        return $this;
    }

    /**
     * @param array<array<string>> $rows
     */
    public function addTable(array $rows): self
    {
        $this->document->addElement(new TableElement($rows));

        return $this;
    }

    public function addImage(string $imagePath, int $pixelWidth, int $pixelHeight): self
    {
        $this->document->addElement(new ImageElement($imagePath, $pixelWidth, $pixelHeight));

        return $this;
    }

    public function generate(string $outputPath): void
    {
        $renderContext = new RenderContext();

        $xmlContent = $this->document->toXml($renderContext);

        $this->writer->write($outputPath, $xmlContent, $renderContext);
    }
}
