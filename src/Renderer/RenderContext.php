<?php

declare(strict_types=1);

namespace D36Dak\DocxBuilder\Renderer;

use RuntimeException;

final class RenderContext
{
    /** @var int starting at 5 because 1-4 are already reserved for other resources */
    private int $nextRelationshipNumber = 5;
    private int $nextImageNumber = 1;

    /** @var array<string, string> target path => source path */
    private array $images = [];

    /** @var array<string, string> relationship id => target path */
    private array $relationships = [];

    /**
     * @param string $imagePath
     * @return array{
     *     'relationshipId': string,
     *     'imageNumber': int,
     * }
     */
    public function addImage(string $imagePath): array
    {
        if (!is_file($imagePath)) {
            throw new RuntimeException(sprintf('Image file does not exist: %s', $imagePath));
        }

        $extension = strtolower(pathinfo($imagePath, PATHINFO_EXTENSION));

        if ($extension === '') {
            throw new RuntimeException(sprintf('Image file has no extension: %s', $imagePath));
        }

        $relationshipId = 'rId' . $this->nextRelationshipNumber++;
        $imageNumber = $this->nextImageNumber++;
        $targetPath = 'media/image' . $imageNumber . '.' . $extension;

        $this->images[$targetPath] = $imagePath;
        $this->relationships[$relationshipId] = $targetPath;

        return ['relationshipId' => $relationshipId, 'imageNumber' => $imageNumber];
    }

    /**
     * @return array<string, string>
     */
    public function getImages(): array
    {
        return $this->images;
    }

    /**
     * @return array<string, string>
     */
    public function getRelationships(): array
    {
        return $this->relationships;
    }
}
