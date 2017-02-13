<?php

namespace app\worker;

use app\worker\PathAssembler;

interface ArchiveInterface
{
    public function pack(string $name, PathAssembler $files);
    public function getMimeType();
    public function getFilename();
    public function getFullPathFileName();
    public function clean();
    
    
}