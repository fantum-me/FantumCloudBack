<?php

namespace App\Entity;

use App\Service\StorageItem\FilePreviewService;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
class File extends StorageItem
{
    #[ORM\Column(length: 255)]
    #[Groups(["item_details"])]
    private ?string $ext = null;

    #[ORM\Column(length: 255)]
    #[Assert\Regex("^[a-zA-Z]+\/[a-zA-Z0-9\-\.\+]+$^")]
    #[Groups(["item_details"])]
    private ?string $mime = null;

    public function getFullSystemFileName(): ?string
    {
        return $this->getSystemFileName() . "." . $this->getExt();
    }

    public function getPreviewPath(): ?string
    {
        return $this->folder->getPath() . "/preview-" . $this->getSystemFileName(
            ) . "." . FilePreviewService::PREVIEW_FORMAT;
    }

    public function getExt(): ?string
    {
        return $this->ext;
    }

    public function setExt(string $ext): static
    {
        $this->ext = $ext;

        return $this;
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
