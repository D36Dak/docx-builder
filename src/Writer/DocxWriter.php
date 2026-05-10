<?php

declare(strict_types=1);

namespace D36Dak\DocxBuilder\Writer;

use D36Dak\DocxBuilder\Renderer\RenderContext;
use DOMDocument;
use DOMElement;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use SplFileInfo;
use ZipArchive;

class DocxWriter
{
    public function write(string $outputPath, string $documentXml, RenderContext $context): void
    {
        $sourceDir = __DIR__ . '/../Resources/docx';

        if (!is_dir($sourceDir)) {
            throw new RuntimeException(sprintf('Default DOCX package directory does not exist: %s', $sourceDir));
        }

        $outputDir = dirname($outputPath);

        if (!is_dir($outputDir) && !mkdir($outputDir, 0775, true) && !is_dir($outputDir)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created.', $outputDir));
        }

        $zip = new ZipArchive();

        if ($zip->open($outputPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException(sprintf('Could not create DOCX file: %s', $outputPath));
        }

        $this->addDefaultPackageFiles($zip, $sourceDir);
        $this->replaceDocumentXml($zip, $documentXml);
        $this->addImageRelationships($zip, $sourceDir, $context);
        $this->addImageFiles($zip, $context);

        if (!$zip->close()) {
            throw new RuntimeException(sprintf('Could not close DOCX file: %s', $outputPath));
        }
    }

    private function addDefaultPackageFiles(ZipArchive $zip, string $sourceDir): void
    {
        $sourceRoot = realpath($sourceDir);

        if ($sourceRoot === false) {
            throw new RuntimeException(sprintf('Could not resolve default DOCX package directory: %s', $sourceDir));
        }

        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($sourceRoot, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        /** @var SplFileInfo $file */
        foreach ($files as $file) {
            if (!$file->isFile()) {
                continue;
            }

            $filePath = $file->getRealPath();

            if ($filePath === false) {
                continue;
            }

            $relativePath = str_replace('\\', '/', substr($filePath, strlen($sourceRoot) + 1));

            if ($relativePath === 'word/document.xml') {
                continue;
            }

            $zip->addFile($filePath, $relativePath);
        }
    }

    private function replaceDocumentXml(ZipArchive $zip, string $documentXml): void
    {
        $zip->addFromString('word/document.xml', $documentXml);
    }

    private function addImageRelationships(ZipArchive $zip, string $sourceDir, RenderContext $context): void
    {
        $relationships = $context->getRelationships();

        if ($relationships === []) {
            return;
        }

        $relationshipsPath = $sourceDir . '/word/_rels/document.xml.rels';

        if (!is_file($relationshipsPath)) {
            throw new RuntimeException(sprintf('Document relationships file does not exist: %s', $relationshipsPath));
        }

        $document = new DOMDocument();
        $document->preserveWhiteSpace = false;
        $document->formatOutput = true;

        if (!$document->load($relationshipsPath)) {
            throw new RuntimeException(sprintf('Could not load document relationships file: %s', $relationshipsPath));
        }

        $root = $document->documentElement;

        if (!$root instanceof DOMElement) {
            throw new RuntimeException(sprintf('Document relationships file has no root element: %s', $relationshipsPath));
        }

        $namespace = 'http://schemas.openxmlformats.org/package/2006/relationships';
        $existingIds = [];

        foreach ($root->getElementsByTagNameNS($namespace, 'Relationship') as $relationship) {
            $existingIds[$relationship->getAttribute('Id')] = true;
        }

        foreach ($relationships as $relationshipId => $targetPath) {
            if (isset($existingIds[$relationshipId])) {
                throw new RuntimeException(sprintf('Relationship id already exists in document relationships: %s', $relationshipId));
            }

            $relationship = $document->createElementNS($namespace, 'Relationship');
            $relationship->setAttribute('Id', $relationshipId);
            $relationship->setAttribute('Type', 'http://schemas.openxmlformats.org/officeDocument/2006/relationships/image');
            $relationship->setAttribute('Target', $targetPath);

            $root->appendChild($relationship);
        }

        $xml = $document->saveXML();

        if ($xml === false) {
            throw new RuntimeException('Could not serialize document relationships XML.');
        }

        $this->replaceZipString($zip, 'word/_rels/document.xml.rels', $xml);
    }

    private function addImageFiles(ZipArchive $zip, RenderContext $context): void
    {
        foreach ($context->getImages() as $targetPath => $sourcePath) {
            if (!is_file($sourcePath)) {
                throw new RuntimeException(sprintf('Image file does not exist: %s', $sourcePath));
            }

            if (!$zip->addFile($sourcePath, 'word/' . $targetPath)) {
                throw new RuntimeException(sprintf('Could not add image file to DOCX package: %s', $sourcePath));
            }
        }
    }

    private function replaceZipString(ZipArchive $zip, string $path, string $contents): void
    {
        if ($zip->locateName($path) !== false && !$zip->deleteName($path)) {
            throw new RuntimeException(sprintf('Could not replace DOCX package file: %s', $path));
        }

        if (!$zip->addFromString($path, $contents)) {
            throw new RuntimeException(sprintf('Could not add DOCX package file: %s', $path));
        }
    }
}
