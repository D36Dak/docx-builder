<?php

declare(strict_types=1);

namespace D36Dak\DocxBuilder\Document\Elements;

use D36Dak\DocxBuilder\Renderer\RenderContext;

class TableElement extends DocxElement
{
    /**
     * @param array<array<string>> $rows
     */
    public function __construct(
        private readonly array $rows,
    ) {
    }

    public function toXml(RenderContext $context): string
    {
        $xml = '<w:tbl><w:tblPr></w:tblPr>';
        foreach ($this->rows as $row) {
            $xml .= '<w:tr>';
            foreach ($row as $cell) {
                $xml .= sprintf(
                    '<w:tc><w:p><w:r><w:t>%s</w:t></w:r></w:p></w:tc>',
                    htmlspecialchars($cell, ENT_XML1 | ENT_COMPAT, 'UTF-8')
                );
            }
            $xml .= '</w:tr>';
        }
        $xml .= '</w:tbl>';
        return $xml;
    }
}