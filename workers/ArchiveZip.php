<?php

namespace app\workers;

use Yii;
use app\workers\FileSystem as FS;
use app\workers\PathAssembler;

class ArchiveZip implements ArchiveInterface
{
    protected $contentDir;
    protected $filename;
    protected $ext = '.zip';
    protected $mimeType = 'application/zip, application/octet-stream'; // application/x-tar

    public function pack(string $name, PathAssembler $assembler)
    {
        $this->filename = FS::normalizeFilename($name);
        $this->contentDir = FS::createTemporaryDir($this->filename);

        foreach ($assembler->prepareFiles() as $file) {
            FS::symlink(
				FS::buildPath([$file->getFullPath(), $file->getOriginalName()]),
				FS::implodeDirs([$this->contentDir, $this->filename, $file->getOriginalName()])
			);
        }
        $this->createArchive();
    }

    protected function createArchive()
    {
        FS::chdir($this->contentDir);
        $cmd = 'zip -0 -r \'' . $this->getFullPathFileName() . '\' \'' . $this->filename . '\'';
		$returnCode = 0;
        $output = '';
        exec($cmd, $output, $returnCode);

		if (0 !== $returnCode) {
			$this->clean($this->contentDir);
            Yii::error('Cant create temporary archive "' . $this->filename . '" : ' . implode("\n", $output));
			throw new \Exception('Cant create temporary archive.');
		}
		if (!FS::isFileExists($this->getFullPathFileName())
                || !FS::isFile($this->getFullPathFileName())
                || 0 === FS::filesize($this->getFullPathFileName())) {
			$this->clean($this->contentDir);
			throw new \Exception("Something wrong with temporary archive");
		}
    }

    public function getFilename()
    {
        return $this->filename . $this->ext;
    }

    public function getMimeType()
    {
        return $this->mimeType;
    }

    public function getFullPathFileName()
    {
        return FS::implodeDirs([$this->contentDir, $this->filename . $this->ext]);
    }

    public function clean() {
		FS::delTree($this->contentDir);
	}

}