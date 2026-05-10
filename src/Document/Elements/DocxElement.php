<?php

declare(strict_types=1);

namespace D36Dak\DocxBuilder\Document\Elements;

use D36Dak\DocxBuilder\Renderer\RenderContext;

abstract class DocxElement
{
    abstract public function toXml(RenderContext $context): string;
}