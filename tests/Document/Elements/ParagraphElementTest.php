<?php

declare(strict_types=1);

namespace D36Dak\DocxBuilder\Tests\Document\Elements;

use D36Dak\DocxBuilder\Document\Elements\ParagraphElement;
use D36Dak\DocxBuilder\Document\Elements\TextRunElement;
use D36Dak\DocxBuilder\Renderer\RenderContext;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class ParagraphElementTest extends TestCase
{
    public function testRendersStringAsSingleTextRun(): void
    {
        $paragraph = new ParagraphElement('Hello world');

        self::assertSame(
            '<w:p><w:r><w:t>Hello world</w:t></w:r></w:p>',
            $paragraph->toXml(new RenderContext())
        );
    }

    public function testRendersMultipleTextRuns(): void
    {
        $paragraph = ParagraphElement::fromTextRuns([
            new TextRunElement('Hello '),
            new TextRunElement('world'),
        ]);

        self::assertSame(
            '<w:p><w:r><w:t xml:space="preserve">Hello </w:t></w:r><w:r><w:t>world</w:t></w:r></w:p>',
            $paragraph->toXml(new RenderContext())
        );
    }

    public function testPreservesLeadingAndTrailingTextRunWhitespace(): void
    {
        $paragraph = ParagraphElement::fromTextRuns([
            new TextRunElement(' leading'),
            new TextRunElement('middle'),
            new TextRunElement('trailing '),
        ]);

        self::assertSame(
            '<w:p><w:r><w:t xml:space="preserve"> leading</w:t></w:r>'
            . '<w:r><w:t>middle</w:t></w:r>'
            . '<w:r><w:t xml:space="preserve">trailing </w:t></w:r></w:p>',
            $paragraph->toXml(new RenderContext())
        );
    }

    public function testEscapesTextRuns(): void
    {
        $paragraph = ParagraphElement::fromTextRuns([
            new TextRunElement('Tom & Jerry'),
            new TextRunElement('<tag>'),
        ]);

        self::assertSame(
            '<w:p><w:r><w:t>Tom &amp; Jerry</w:t></w:r><w:r><w:t>&lt;tag&gt;</w:t></w:r></w:p>',
            $paragraph->toXml(new RenderContext())
        );
    }

    public function testRendersTextRunOptions(): void
    {
        $paragraph = ParagraphElement::fromTextRuns([
            new TextRunElement('Styled', [
                'fontFamily' => 'Arial',
                'fontSize' => 12.5,
                'color' => '#ff00aa',
                'bold' => true,
                'italic' => true,
                'underline' => true,
            ]),
        ]);

        self::assertSame(
            '<w:p><w:r><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial"/>'
            . '<w:sz w:val="25"/><w:color w:val="FF00AA"/><w:b/><w:i/>'
            . '<w:u w:val="single"/></w:rPr><w:t>Styled</w:t></w:r></w:p>',
            $paragraph->toXml(new RenderContext())
        );
    }

    public function testParagraphTextRunOptionsApplyToAllRuns(): void
    {
        $paragraph = ParagraphElement::fromTextRuns([
            new TextRunElement('Hello '),
            new TextRunElement('world'),
        ], [
            'fontFamily' => 'Times New Roman',
            'fontSize' => 11.0,
            'color' => '336699',
            'bold' => true,
        ]);

        self::assertSame(
            '<w:p><w:r><w:rPr><w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman"/>'
            . '<w:sz w:val="22"/><w:color w:val="336699"/><w:b/></w:rPr>'
            . '<w:t xml:space="preserve">Hello </w:t></w:r><w:r><w:rPr>'
            . '<w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman"/>'
            . '<w:sz w:val="22"/><w:color w:val="336699"/><w:b/></w:rPr>'
            . '<w:t>world</w:t></w:r></w:p>',
            $paragraph->toXml(new RenderContext())
        );
    }

    public function testTextRunOptionsOverrideParagraphOptions(): void
    {
        $paragraph = ParagraphElement::fromTextRuns([
            new TextRunElement('plain', [
                'bold' => false,
                'color' => '000000',
            ]),
        ], [
            'bold' => true,
            'italic' => true,
            'color' => 'FF0000',
        ]);

        self::assertSame(
            '<w:p><w:r><w:rPr><w:color w:val="000000"/><w:b w:val="false"/><w:i/></w:rPr><w:t>plain</w:t></w:r></w:p>',
            $paragraph->toXml(new RenderContext())
        );
    }

    public function testRendersParagraphOptions(): void
    {
        $paragraph = new ParagraphElement('Hello world', [
            'alignment' => 'center',
            'spacingBefore' => 120,
            'spacingAfter' => 240,
            'lineSpacing' => 1.15,
        ]);

        self::assertSame(
            '<w:p><w:pPr><w:jc w:val="center"/>'
            . '<w:spacing w:before="120" w:after="240" w:line="276" w:lineRule="auto"/>'
            . '</w:pPr><w:r><w:t>Hello world</w:t></w:r></w:p>',
            $paragraph->toXml(new RenderContext())
        );
    }

    public function testRendersBothAlignment(): void
    {
        $paragraph = new ParagraphElement('Hello world', [
            'alignment' => 'both',
        ]);

        self::assertSame(
            '<w:p><w:pPr><w:jc w:val="both"/></w:pPr><w:r><w:t>Hello world</w:t></w:r></w:p>',
            $paragraph->toXml(new RenderContext())
        );
    }

    public function testRejectsNonTextRunArrayValues(): void
    {
        $this->expectException(InvalidArgumentException::class);

        ParagraphElement::fromTextRuns(['not a text run']);
    }

    public function testRejectsInvalidAlignment(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ParagraphElement('Hello world', [
            'alignment' => 'middle',
        ]);
    }

    public function testRejectsNonIntegerParagraphSpacingOptions(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ParagraphElement('Hello world', [
            'spacingBefore' => '120',
        ]);
    }

    public function testRejectsNonNumericLineSpacing(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ParagraphElement('Hello world', [
            'lineSpacing' => '1.15',
        ]);
    }

    public function testRejectsNonPositiveLineSpacing(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ParagraphElement('Hello world', [
            'lineSpacing' => 0.0,
        ]);
    }

    public function testRejectsInvalidTextRunOptionTypes(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new TextRunElement('Hello world', [
            'bold' => 'yes',
        ]);
    }
}
