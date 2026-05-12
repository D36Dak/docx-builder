<?php

declare(strict_types=1);

namespace D36Dak\DocxBuilder\Tests\Document\Elements;

use D36Dak\DocxBuilder\Document\Elements\ImageElement;
use D36Dak\DocxBuilder\Renderer\RenderContext;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class ImageElementTest extends TestCase
{
    private string $imagePath;

    protected function setUp(): void
    {
        $imagePath = tempnam(sys_get_temp_dir(), 'docx-builder-image-');
        if ($imagePath === false) {
            self::fail('Could not create temporary image file.');
        }

        $pathWithExtension = $imagePath . '.png';
        rename($imagePath, $pathWithExtension);
        $this->imagePath = $pathWithExtension;
    }

    protected function tearDown(): void
    {
        if (is_file($this->imagePath)) {
            unlink($this->imagePath);
        }
    }

    public function testRendersImageOptions(): void
    {
        $image = new ImageElement($this->imagePath, [
            'width' => 100,
            'height' => 50,
            'alignment' => 'center',
            'altText' => 'Diagram & chart',
        ]);

        $xml = $image->toXml(new RenderContext());

        self::assertStringContainsString('<w:p><w:pPr><w:jc w:val="center"/></w:pPr><w:r>', $xml);
        self::assertStringContainsString('<wp:extent cx="952500" cy="476250"/>', $xml);
        self::assertStringContainsString('<wp:docPr id="1" name="Image1" descr="Diagram &amp; chart"/>', $xml);
    }

    public function testRendersImageWithoutOptionalOptions(): void
    {
        $image = new ImageElement($this->imagePath, [
            'width' => 100,
            'height' => 50,
        ]);

        $xml = $image->toXml(new RenderContext());

        self::assertStringStartsWith('<w:p><w:r>', $xml);
        self::assertStringContainsString('<wp:docPr id="1" name="Image1"/>', $xml);
    }

    public function testRequiresWidth(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ImageElement($this->imagePath, [
            'height' => 50,
        ]);
    }

    public function testRejectsInvalidHeight(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ImageElement($this->imagePath, [
            'width' => 100,
            'height' => 0,
        ]);
    }

    public function testRejectsInvalidAlignment(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ImageElement($this->imagePath, [
            'width' => 100,
            'height' => 50,
            'alignment' => 'both',
        ]);
    }
}
