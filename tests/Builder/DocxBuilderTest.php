<?php

declare(strict_types=1);

namespace D36Dak\DocxBuilder\Tests\Builder;

use D36Dak\DocxBuilder\Builder\DocxBuilder;
use D36Dak\DocxBuilder\Builder\ParagraphBuilder;
use PHPUnit\Framework\TestCase;
use ZipArchive;

final class DocxBuilderTest extends TestCase
{
    public function testAddPageBreakIsChainable(): void
    {
        $builder = new DocxBuilder();

        self::assertSame($builder, $builder->addPageBreak());
    }

    public function testHeaderAndFooterMethodsAreChainable(): void
    {
        $builder = new DocxBuilder();

        self::assertSame($builder, $builder->addHeader('Header'));
        self::assertSame($builder, $builder->addFirstPageHeader('First header'));
        self::assertSame($builder, $builder->addFooter('Footer'));
        self::assertSame($builder, $builder->addFirstPageFooter('First footer'));
    }

    public function testGeneratesHeaderAndFooterParts(): void
    {
        $outputPath = tempnam(sys_get_temp_dir(), 'docx-builder-');
        if ($outputPath === false) {
            self::fail('Could not create temporary output file.');
        }

        $footer = (new ParagraphBuilder([
            'alignment' => 'center',
        ]))
            ->addTextRun('Footer')
            ->build();

        (new DocxBuilder())
            ->addHeader('Header', [
                'bold' => true,
            ])
            ->addFooter($footer)
            ->addParagraph('Body')
            ->save($outputPath);

        $zip = new ZipArchive();
        self::assertTrue($zip->open($outputPath));

        $documentXml = $zip->getFromName('word/document.xml');
        $relationshipsXml = $zip->getFromName('word/_rels/document.xml.rels');
        $contentTypesXml = $zip->getFromName('[Content_Types].xml');
        $headerXml = $zip->getFromName('word/header1.xml');
        $footerXml = $zip->getFromName('word/footer1.xml');

        $zip->close();
        unlink($outputPath);

        self::assertIsString($documentXml);
        self::assertIsString($relationshipsXml);
        self::assertIsString($contentTypesXml);
        self::assertIsString($headerXml);
        self::assertIsString($footerXml);

        self::assertStringContainsString('<w:headerReference w:type="default" r:id="rId5"/>', $documentXml);
        self::assertStringContainsString('<w:footerReference w:type="default" r:id="rId6"/>', $documentXml);
        self::assertStringContainsString(
            'Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/header"',
            $relationshipsXml
        );
        self::assertStringContainsString('Target="header1.xml"', $relationshipsXml);
        self::assertStringContainsString(
            'Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/footer"',
            $relationshipsXml
        );
        self::assertStringContainsString('Target="footer1.xml"', $relationshipsXml);
        self::assertStringContainsString(
            'ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.header+xml"',
            $contentTypesXml
        );
        self::assertStringContainsString(
            'ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.footer+xml"',
            $contentTypesXml
        );
        self::assertStringContainsString('<w:b/>', $headerXml);
        self::assertStringContainsString('<w:t>Header</w:t>', $headerXml);
        self::assertStringContainsString('<w:jc w:val="center"/>', $footerXml);
        self::assertStringContainsString('<w:t>Footer</w:t>', $footerXml);
    }

    public function testGetContentsReturnsGeneratedDocxBinary(): void
    {
        $contents = (new DocxBuilder())
            ->addHeader('Header')
            ->addParagraph('Body')
            ->getContents();

        self::assertNotSame('', $contents);

        $outputPath = tempnam(sys_get_temp_dir(), 'docx-builder-contents-');
        if ($outputPath === false) {
            self::fail('Could not create temporary output file.');
        }

        file_put_contents($outputPath, $contents);

        $zip = new ZipArchive();
        self::assertTrue($zip->open($outputPath));

        $documentXml = $zip->getFromName('word/document.xml');
        $headerXml = $zip->getFromName('word/header1.xml');

        $zip->close();
        unlink($outputPath);

        self::assertIsString($documentXml);
        self::assertIsString($headerXml);
        self::assertStringContainsString('<w:t>Body</w:t>', $documentXml);
        self::assertStringContainsString('<w:t>Header</w:t>', $headerXml);
    }

    public function testGeneratesFirstPageHeaderAndFooterParts(): void
    {
        $outputPath = tempnam(sys_get_temp_dir(), 'docx-builder-');
        if ($outputPath === false) {
            self::fail('Could not create temporary output file.');
        }

        (new DocxBuilder())
            ->addHeader('Default header')
            ->addFirstPageHeader('First header')
            ->addFooter('Default footer')
            ->addFirstPageFooter('First footer')
            ->addParagraph('Body')
            ->save($outputPath);

        $zip = new ZipArchive();
        self::assertTrue($zip->open($outputPath));

        $documentXml = $zip->getFromName('word/document.xml');
        $header1Xml = $zip->getFromName('word/header1.xml');
        $header2Xml = $zip->getFromName('word/header2.xml');
        $footer1Xml = $zip->getFromName('word/footer1.xml');
        $footer2Xml = $zip->getFromName('word/footer2.xml');

        $zip->close();
        unlink($outputPath);

        self::assertIsString($documentXml);
        self::assertIsString($header1Xml);
        self::assertIsString($header2Xml);
        self::assertIsString($footer1Xml);
        self::assertIsString($footer2Xml);

        self::assertStringContainsString('<w:titlePg/>', $documentXml);
        self::assertStringContainsString('<w:headerReference w:type="default" r:id="rId5"/>', $documentXml);
        self::assertStringContainsString('<w:headerReference w:type="first" r:id="rId6"/>', $documentXml);
        self::assertStringContainsString('<w:footerReference w:type="default" r:id="rId7"/>', $documentXml);
        self::assertStringContainsString('<w:footerReference w:type="first" r:id="rId8"/>', $documentXml);
        self::assertStringContainsString('<w:t>Default header</w:t>', $header1Xml);
        self::assertStringContainsString('<w:t>First header</w:t>', $header2Xml);
        self::assertStringContainsString('<w:t>Default footer</w:t>', $footer1Xml);
        self::assertStringContainsString('<w:t>First footer</w:t>', $footer2Xml);
    }
}
