<?php

declare(strict_types=1);

namespace D36Dak\DocxBuilder\Tests\Document\Elements;

use D36Dak\DocxBuilder\Document\Elements\PageBreakElement;
use D36Dak\DocxBuilder\Renderer\RenderContext;
use PHPUnit\Framework\TestCase;

final class PageBreakElementTest extends TestCase
{
    public function testRendersPageBreak(): void
    {
        $pageBreak = new PageBreakElement();

        self::assertSame(
            '<w:p><w:r><w:br w:type="page"/></w:r></w:p>',
            $pageBreak->toXml(new RenderContext())
        );
    }
}
