<?php

namespace AetherUpload;


class Header
{
    public $path;
    public $name;
    public $realPath;

    public function __construct($tempBaseName)
    {
        $this->name = $tempBaseName;
        $this->path = $this->getRelativePath();
        $this->realPath = $this->getRealPath();
    }

    public function create()
    {
        if ( file_put_contents($this->realPath, 0, false) === false ) {
            throw new \Exception(trans('create_header_fail'));
        }
    }

    public function write($content)
    {
        if ( file_put_contents($this->realPath, $content, false) === false ) {
            throw new \Exception(trans('write_header_fail'));
        }
    }

    public function read()
    {
        if ( ($content = file_get_contents($this->realPath)) === false ) {
            throw new \Exception(trans('read_header_fail'));
        }

        return $content;
    }

    public function delete()
    {
        if ( unlink($this->realPath) === false ) {
            throw new \Exception(trans('delete_header_fail'));
        }
    }

    private function getRelativePath()
    {
        return ConfigMapper::get('root_dir') . DIRECTORY_SEPARATOR . '_header' . DIRECTORY_SEPARATOR . $this->name;
    }

    public function getRealPath()
    {
        return base_path(). DIRECTORY_SEPARATOR .$this->path;
    }

    public function exists()
    {
        return file_exists($this->realPath);
    }


}