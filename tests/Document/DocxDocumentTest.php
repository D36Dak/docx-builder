<?php

declare(strict_types=1);

namespace D36Dak\DocxBuilder\Tests\Document;

use D36Dak\DocxBuilder\Document\DocxDocument;
use D36Dak\DocxBuilder\Document\Elements\ParagraphElement;
use D36Dak\DocxBuilder\Renderer\RenderContext;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class DocxDocumentTest extends TestCase
{
    public function testRendersDocumentFormatAndMargins(): void
    {
        $document = new DocxDocument([
            'format' => 'a4',
            'margins' => [
                'top' => 720,
                'right' => 1080,
                'bottom' => 720,
                'left' => 1080,
            ],
        ]);

        $xml = $document->toXml(new RenderContext());

        self::assertStringContainsString(
            '<w:sectPr><w:pgSz w:w="11906" w:h="16838"/>'
            . '<w:pgMar w:top="720" w:right="1080" w:bottom="720" w:left="1080"'
            . ' w:header="708" w:footer="708" w:gutter="0"/></w:sectPr>',
            $xml
        );
    }

    public function testRendersUsLetterFormat(): void
    {
        $document = new DocxDocument([
            'format' => 'us-letter',
        ]);

        self::assertStringContainsString(
            '<w:sectPr><w:pgSz w:w="12240" w:h="15840"/></w:sectPr>',
            $document->toXml(new RenderContext())
        );
    }

    public function testRendersOnlyProvidedDocumentMargins(): void
    {
        $document = new DocxDocument([
            'margins' => [
                'top' => 360,
                'bottom' => 360,
            ],
        ]);

        self::assertStringContainsString(
            '<w:pgMar w:top="360" w:right="1440" w:bottom="360" w:left="1440"'
            . ' w:header="708" w:footer="708" w:gutter="0"/>',
            $document->toXml(new RenderContext())
        );
    }

    public function testDocumentMarginsMustBeAnArray(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Document margins must be an array.');

        new DocxDocument([
            'margins' => 360,
        ]);
    }

    public function testDocumentFormatIsCaseSensitive(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported document format "A4".');

        new DocxDocument([
            'format' => 'A4',
        ]);
    }

    public function testOnlyPageMarginsCanBeConfigured(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported document margin "header".');

        new DocxDocument([
            'margins' => [
                'header' => 360,
            ],
        ]);
    }

    public function testRegistersHeaderAndFooterInRenderContext(): void
    {
        $document = new DocxDocument();
        $document->addHeader(new ParagraphElement('Header', [
            'bold' => true,
        ]));
        $document->addFooter(new ParagraphElement('Footer', [
            'alignment' => 'center',
        ]));
        $document->addElement(new ParagraphElement('Body'));

        $context = new RenderContext();
        $xml = $document->toXml($context);

        self::assertStringContainsString(
            '<w:sectPr><w:headerReference w:type="default" r:id="rId5"/>'
            . '<w:footerReference w:type="default" r:id="rId6"/></w:sectPr>',
            $xml
        );

        self::assertSame([
            'header1.xml' => '<w:hdr xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"'
                . ' xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
                . '<w:p><w:r><w:rPr><w:b/></w:rPr><w:t>Header</w:t></w:r></w:p></w:hdr>',
        ], $context->getHeaders());

        self::assertSame([
            'footer1.xml' => '<w:ftr xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"'
                . ' xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
                . '<w:p><w:pPr><w:jc w:val="center"/></w:pPr><w:r><w:t>Footer</w:t></w:r></w:p></w:ftr>',
        ], $context->getFooters());

        self::assertSame([
            'rId5' => [
                'type' => 'http://schemas.openxmlformats.org/officeDocument/2006/relationships/header',
                'target' => 'header1.xml',
            ],
            'rId6' => [
                'type' => 'http://schemas.openxmlformats.org/officeDocument/2006/relationships/footer',
                'target' => 'footer1.xml',
            ],
        ], $context->getRelationships());
    }

    public function testRegistersFirstPageHeaderAndFooterInRenderContext(): void
    {
        $document = new DocxDocument();
        $document->addHeader(new ParagraphElement('Default header'));
        $document->addFirstPageHeader(new ParagraphElement('First header'));
        $document->addFooter(new ParagraphElement('Default footer'));
        $document->addFirstPageFooter(new ParagraphElement('First footer'));
        $document->addElement(new ParagraphElement('Body'));

        $context = new RenderContext();
        $xml = $document->toXml($context);

        self::assertStringContainsString(
            '<w:sectPr><w:titlePg/>'
            . '<w:headerReference w:type="default" r:id="rId5"/>'
            . '<w:headerReference w:type="first" r:id="rId6"/>'
            . '<w:footerReference w:type="default" r:id="rId7"/>'
            . '<w:footerReference w:type="first" r:id="rId8"/></w:sectPr>',
            $xml
        );

        self::assertArrayHasKey('header1.xml', $context->getHeaders());
        self::assertArrayHasKey('header2.xml', $context->getHeaders());
        self::assertArrayHasKey('footer1.xml', $context->getFooters());
        self::assertArrayHasKey('footer2.xml', $context->getFooters());
        self::assertStringContainsString('Default header', $context->getHeaders()['header1.xml']);
        self::assertStringContainsString('First header', $context->getHeaders()['header2.xml']);
        self::assertStringContainsString('Default footer', $context->getFooters()['footer1.xml']);
        self::assertStringContainsString('First footer', $context->getFooters()['footer2.xml']);
    }

    public function testFirstPageUsesDefaultHeaderWhenFirstPageHeaderIsNotSet(): void
    {
        $document = new DocxDocument();
        $document->addHeader(new ParagraphElement('Default header'));
        $document->addFooter(new ParagraphElement('Default footer'));
        $document->addFirstPageFooter(new ParagraphElement('First footer'));

        $context = new RenderContext();
        $xml = $document->toXml($context);

        self::assertStringContainsString(
            '<w:sectPr><w:titlePg/>'
            . '<w:headerReference w:type="default" r:id="rId5"/>'
            . '<w:headerReference w:type="first" r:id="rId5"/>'
            . '<w:footerReference w:type="default" r:id="rId6"/>'
            . '<w:footerReference w:type="first" r:id="rId7"/></w:sectPr>',
            $xml
        );
    }
}
