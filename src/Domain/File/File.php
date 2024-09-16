<?php

namespace App\Domain\File;

use App\Domain\File\Service\FilePreviewService;
use App\Domain\StorageItem\StorageItem;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
class File extends StorageItem
{
    #[ORM\Column(length: 255)]
    #[Assert\Regex("^[a-zA-Z]+\/[a-zA-Z0-9\-\.\+]+$^")]
    #[Groups(["item_details"])]
    private ?string $mime = null;

    public function getPreviewPath(): ?string
    {
        return $this->folder->getPath() . "/preview-" . $this->getSystemFileName() . "." . FilePreviewService::PREVIEW_FORMAT;
    }

    public function getMime(): ?string
    {
        return $this->mime;
    }

    public function setMime(string $mime): static
    {
        $this->mime = $mime;

        return $this;
    }
}
