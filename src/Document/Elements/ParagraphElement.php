<?php

declare(strict_types=1);

namespace D36Dak\DocxBuilder\Document\Elements;

use D36Dak\DocxBuilder\Renderer\RenderContext;

class ParagraphElement extends DocxElement
{
    public function __construct(
        private readonly string $text,
    ) {
    }

    public function toXml(RenderContext $context): string
    {
        return sprintf(
            '<w:p><w:r><w:t>%s</w:t></w:r></w:p>',
            htmlspecialchars($this->text, ENT_XML1 | ENT_COMPAT, 'UTF-8')
        );
    }
}