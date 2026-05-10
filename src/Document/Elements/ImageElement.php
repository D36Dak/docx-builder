<?php

declare(strict_types=1);

namespace D36Dak\DocxBuilder\Document\Elements;

use D36Dak\DocxBuilder\Renderer\RenderContext;

class ImageElement extends DocxElement
{
    public function __construct(
        private readonly string $imagePath,
        private readonly int $width,
        private readonly int $height,
    ) {
    }

    public function toXml(RenderContext $context): string
    {
        $renderedImage = $context->addImage($this->imagePath);

        return sprintf(
            '<w:p><w:r><w:drawing><wp:inline>
            <wp:extent cx="%d" cy="%d"/>
            <a:graphic xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main">
            <a:graphicData uri="http://schemas.openxmlformats.org/drawingml/2006/picture">
            <pic:pic xmlns:pic="http://schemas.openxmlformats.org/drawingml/2006/picture">
            <pic:nvPicPr><pic:cNvPr id="%d" name="Image%d"/>
            <pic:cNvPicPr/></pic:nvPicPr>
            <pic:blipFill><a:blip r:embed="%s"/></pic:blipFill>
            <pic:spPr/></pic:pic></a:graphicData></a:graphic></wp:inline></w:drawing></w:r></w:p>',
            $this->width * 9525, // Convert pixels to EMUs
            $this->height * 9525, // Convert pixels to EMUs
            $renderedImage['imageNumber'],
            $renderedImage['imageNumber'],
            $renderedImage['relationshipId']
        );
    }

    public function getImagePath(): string
    {
        return $this->imagePath;
    }
}
