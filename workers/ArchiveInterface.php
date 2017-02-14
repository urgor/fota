<?php

namespace app\workers;

use app\workers\PathAssembler;

interface ArchiveInterface
{
    public function pack(string $name, PathAssembler $files);
    public function getMimeType();
    public function getFilename();
    public function getFullPathFileName();
    public function clean();


}