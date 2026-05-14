<?php

declare(strict_types=1);

namespace D36Dak\DocxBuilder\Tests\Document\Elements;

use D36Dak\DocxBuilder\Document\Elements\TableElement;
use D36Dak\DocxBuilder\Renderer\RenderContext;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class TableElementTest extends TestCase
{
    public function testRendersTableRows(): void
    {
        $table = new TableElement([
            ['Name', 'Email'],
            ['Ada', 'ada@example.com'],
        ]);

        self::assertSame(
            '<w:tbl><w:tblPr></w:tblPr>'
            . '<w:tr><w:tc><w:p><w:r><w:t>Name</w:t></w:r></w:p></w:tc>'
            . '<w:tc><w:p><w:r><w:t>Email</w:t></w:r></w:p></w:tc></w:tr>'
            . '<w:tr><w:tc><w:p><w:r><w:t>Ada</w:t></w:r></w:p></w:tc>'
            . '<w:tc><w:p><w:r><w:t>ada@example.com</w:t></w:r></w:p></w:tc></w:tr>'
            . '</w:tbl>',
            $table->toXml(new RenderContext())
        );
    }

    public function testRendersRepeatingHeaderRows(): void
    {
        $table = new TableElement([
            ['Name', 'Email'],
            ['Ada', 'ada@example.com'],
            ['Grace', 'grace@example.com'],
        ], [
            'headerRowCount' => 1,
        ]);

        self::assertSame(
            '<w:tbl><w:tblPr></w:tblPr>'
            . '<w:tr><w:trPr><w:tblHeader/></w:trPr>'
            . '<w:tc><w:p><w:r><w:t>Name</w:t></w:r></w:p></w:tc>'
            . '<w:tc><w:p><w:r><w:t>Email</w:t></w:r></w:p></w:tc></w:tr>'
            . '<w:tr><w:tc><w:p><w:r><w:t>Ada</w:t></w:r></w:p></w:tc>'
            . '<w:tc><w:p><w:r><w:t>ada@example.com</w:t></w:r></w:p></w:tc></w:tr>'
            . '<w:tr><w:tc><w:p><w:r><w:t>Grace</w:t></w:r></w:p></w:tc>'
            . '<w:tc><w:p><w:r><w:t>grace@example.com</w:t></w:r></w:p></w:tc></w:tr>'
            . '</w:tbl>',
            $table->toXml(new RenderContext())
        );
    }

    public function testRendersMultipleRepeatingHeaderRows(): void
    {
        $table = new TableElement([
            ['Group'],
            ['Name'],
            ['Ada'],
        ], [
            'headerRowCount' => 2,
        ]);

        self::assertSame(
            '<w:tbl><w:tblPr></w:tblPr>'
            . '<w:tr><w:trPr><w:tblHeader/></w:trPr>'
            . '<w:tc><w:p><w:r><w:t>Group</w:t></w:r></w:p></w:tc></w:tr>'
            . '<w:tr><w:trPr><w:tblHeader/></w:trPr>'
            . '<w:tc><w:p><w:r><w:t>Name</w:t></w:r></w:p></w:tc></w:tr>'
            . '<w:tr><w:tc><w:p><w:r><w:t>Ada</w:t></w:r></w:p></w:tc></w:tr>'
            . '</w:tbl>',
            $table->toXml(new RenderContext())
        );
    }

    public function testRendersCellOptions(): void
    {
        $table = new TableElement([
            ['Name'],
            ['Ada'],
        ], [
            'cellOptions' => [
                'alignment' => 'center',
                'bold' => true,
            ],
        ]);

        self::assertSame(
            '<w:tbl><w:tblPr></w:tblPr>'
            . '<w:tr><w:tc><w:p><w:pPr><w:jc w:val="center"/></w:pPr>'
            . '<w:r><w:rPr><w:b/></w:rPr><w:t>Name</w:t></w:r></w:p></w:tc></w:tr>'
            . '<w:tr><w:tc><w:p><w:pPr><w:jc w:val="center"/></w:pPr>'
            . '<w:r><w:rPr><w:b/></w:rPr><w:t>Ada</w:t></w:r></w:p></w:tc></w:tr>'
            . '</w:tbl>',
            $table->toXml(new RenderContext())
        );
    }

    public function testRendersHeaderCellOptions(): void
    {
        $table = new TableElement([
            ['Name'],
            ['Ada'],
        ], [
            'headerRowCount' => 1,
            'cellOptions' => [
                'italic' => true,
            ],
            'headerCellOptions' => [
                'alignment' => 'center',
                'bold' => true,
            ],
        ]);

        self::assertSame(
            '<w:tbl><w:tblPr></w:tblPr>'
            . '<w:tr><w:trPr><w:tblHeader/></w:trPr><w:tc>'
            . '<w:p><w:pPr><w:jc w:val="center"/></w:pPr>'
            . '<w:r><w:rPr><w:b/></w:rPr><w:t>Name</w:t></w:r></w:p></w:tc></w:tr>'
            . '<w:tr><w:tc><w:p><w:r><w:rPr><w:i/></w:rPr><w:t>Ada</w:t></w:r></w:p></w:tc></w:tr>'
            . '</w:tbl>',
            $table->toXml(new RenderContext())
        );
    }

    public function testRejectsNonIntegerHeaderRowCount(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new TableElement([], [
            'headerRowCount' => '1',
        ]);
    }

    public function testRejectsNegativeHeaderRowCount(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new TableElement([], [
            'headerRowCount' => -1,
        ]);
    }

    public function testRejectsNonArrayCellOptions(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new TableElement([], [
            'cellOptions' => 'bold',
        ]);
    }

    public function testRejectsInvalidHeaderCellOptions(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new TableElement([], [
            'headerCellOptions' => [
                'alignment' => 'middle',
            ],
        ]);
    }
}
