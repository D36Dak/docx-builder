<?php

declare(strict_types=1);

namespace D36Dak\DocxBuilder\Tests\Builder;

use D36Dak\DocxBuilder\Builder\ParagraphBuilder;
use D36Dak\DocxBuilder\Document\Elements\ParagraphElement;
use D36Dak\DocxBuilder\Renderer\RenderContext;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class ParagraphBuilderTest extends TestCase
{
    public function testBuildsParagraphWithTextRuns(): void
    {
        $paragraph = (new ParagraphBuilder())
            ->addTextRun('Hello ')
            ->addTextRun('world')
            ->build();

        self::assertSame(
            '<w:p><w:r><w:t xml:space="preserve">Hello </w:t></w:r><w:r><w:t>world</w:t></w:r></w:p>',
            $paragraph->toXml(new RenderContext())
        );
    }

    public function testAppliesDefaultOptionsToTextRuns(): void
    {
        $paragraph = (new ParagraphBuilder([
            'alignment' => 'center',
            'bold' => true,
            'color' => '336699',
        ]))
            ->addTextRun('Hello ')
            ->addTextRun('world', [
                'bold' => false,
            ])
            ->build();

        self::assertSame(
            '<w:p><w:pPr><w:jc w:val="center"/></w:pPr>'
            . '<w:r><w:rPr><w:color w:val="336699"/><w:b/></w:rPr>'
            . '<w:t xml:space="preserve">Hello </w:t></w:r>'
            . '<w:r><w:rPr><w:color w:val="336699"/><w:b w:val="false"/></w:rPr>'
            . '<w:t>world</w:t></w:r></w:p>',
            $paragraph->toXml(new RenderContext())
        );
    }

    public function testRejectsInvalidDefaultOptions(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ParagraphBuilder([
            'alignment' => 'middle',
        ]);
    }

    public function testRejectsInvalidTextRunOptions(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new ParagraphBuilder())->addTextRun('Hello', [
            'bold' => 'yes',
        ]);
    }

    public function testBuildsParagraphElement(): void
    {
        $paragraph = (new ParagraphBuilder([
                'italic' => true,
            ]))
            ->addTextRun('Hello')
            ->build();

        self::assertInstanceOf(ParagraphElement::class, $paragraph);
        self::assertSame(
            '<w:p><w:r><w:rPr><w:i/></w:rPr><w:t>Hello</w:t></w:r></w:p>',
            $paragraph->toXml(new RenderContext())
        );
    }
}
