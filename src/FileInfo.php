<?php

declare(strict_types=1);

namespace DvachPicScraper;

use function DvachPicScraper\Helpers\takeMimeType;

class FileInfo
{

    public $name;

    public $fullName;

    public $path;

    public $mimeType;

    public function __construct(object $file)
    {
        $this->path = $file->path;
        $this->name = $file->name;
        $this->fullName = $file->fullname;
        $this->mimeType = takeMimeType($this->name);
    }
}