<?php

declare(strict_types=1);

namespace D36Dak\DocxBuilder\Builder;

use D36Dak\DocxBuilder\Document\Elements\ParagraphElement;
use D36Dak\DocxBuilder\Document\Elements\TextRunElement;

class ParagraphBuilder
{
    /** @var array<TextRunElement> */
    private array $textRuns = [];

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
     * } $defaultOptions
     */
    public function __construct(
        private readonly array $defaultOptions = [],
    ) {
        new ParagraphElement('', $defaultOptions);
    }

    /**
     * @param array{
     *     fontFamily?: string,
     *     fontSize?: float,
     *     color?: string,
     *     bold?: bool,
     *     italic?: bool,
     *     underline?: bool,
     * } $options
     */
    public function addTextRun(string $text, array $options = []): self
    {
        $this->textRuns[] = new TextRunElement($text, $options);

        return $this;
    }

    public function build(): ParagraphElement
    {
        return ParagraphElement::fromTextRuns($this->textRuns, $this->defaultOptions);
    }
}
