<?php

declare(strict_types=1);

namespace D36Dak\DocxBuilder\Tests\Document\Elements;

use D36Dak\DocxBuilder\Document\Elements\FieldTextRunElement;
use D36Dak\DocxBuilder\Renderer\RenderContext;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class FieldTextRunElementTest extends TestCase
{
    public function testRendersPageField(): void
    {
        $textRun = new FieldTextRunElement(FieldTextRunElement::PAGE);

        self::assertSame(
            '<w:fldSimple w:instr="PAGE"><w:r><w:t>1</w:t></w:r></w:fldSimple>',
            $textRun->toXml(new RenderContext())
        );
    }

    public function testRendersTotalPagesFieldWithOptions(): void
    {
        $textRun = new FieldTextRunElement(FieldTextRunElement::NUMPAGES, [
            'bold' => true,
        ]);

        self::assertSame(
            '<w:fldSimple w:instr="NUMPAGES"><w:r><w:rPr><w:b/></w:rPr><w:t>1</w:t></w:r></w:fldSimple>',
            $textRun->toXml(new RenderContext())
        );
    }

    public function testRejectsInvalidOptions(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new FieldTextRunElement(FieldTextRunElement::PAGE, [
            'bold' => 'yes',
        ]);
    }

    public function testRejectsUnsupportedField(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new FieldTextRunElement('AUTHOR');
    }
}
