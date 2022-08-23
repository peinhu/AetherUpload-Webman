<?php

namespace AetherUpload;


class PartialResource
{
    public $tempName;
    public $group;
    public $groupDir;
    public $groupSubDir;
    public $header;
    public $path;
    public $realPath;
    private $chunkIndex = null;

    public function __construct($tempBaseName, $extension, $groupSubDir)
    {
        $this->tempName = Util::getFileName($tempBaseName, $extension);
        $this->group = ConfigMapper::get('group');
        $this->groupDir = ConfigMapper::get('group_dir');
        $this->groupSubDir = $groupSubDir;
        $this->path = $this->getPath();
        $this->realPath = $this->getRealPath();
        $this->header = new Header($tempBaseName);
    }

    public function create()
    {
        if ( $this->createGroupSubDir() === false ) {
            throw new \Exception(trans('create_subfolder_fail'));
        }

        if ( file_put_contents($this->realPath, '', false) === false ) {
            throw new \Exception(trans('create_resource_fail'));
        }

    }

    public function append($chunkRealPath)
    {
        $handle = @fopen($chunkRealPath, 'rb');

        if ( file_put_contents($this->realPath, $handle, FILE_APPEND) === false ) {
            throw new \Exception(trans('write_resource_fail'));
        }

        fclose($handle);

    }

    public function delete()
    {
        if ( unlink($this->realPath) === false ) {
            throw new \Exception(trans('delete_resource_fail'));
        }

        return true;
    }

    public function rename($completeName)
    {
        $completePath = $this->getCompletePath($completeName);

        if ( file_exists($completePath) ) {

            $this->delete();
        }else{

            if ( rename($this->realPath, $completePath) === false ) {
                throw new \Exception(trans('rename_resource_fail'));
            }
        }
    }

    public function filterBySize($resourceSize)
    {
        $maxSize = (int)ConfigMapper::get('resource_maxsize');

        if ( (int)$resourceSize === 0 || ((int)$resourceSize > $maxSize && $maxSize !== 0) ) {
            throw new \Exception(trans('invalid_resource_size'));
        }

    }

    public function filterByExtension($resourceExt)
    {
        $extensions = ConfigMapper::get('resource_extensions');

        if ( empty($resourceExt) || (empty($extensions) === false && in_array($resourceExt, $extensions) === false) || in_array($resourceExt, ConfigMapper::get('forbidden_extensions')) === true ) {
            throw new \Exception(trans('invalid_resource_type'));
        }
    }

    public function checkSize()
    {
        $this->filterBySize(filesize($this->realPath));
    }

    public function checkMimeType()
    {
        $extension = MimeType::search(mime_content_type($this->realPath));

        if($extension === null){
            throw new \Exception(trans('missing_mimetype'));
        }

        $this->filterByExtension($extension);
    }

    public function exists()
    {
        return file_exists($this->realPath);
    }

    public function createGroupSubDir()
    {
        $groupDir = dirname($groupSubDir = $this->getGroupSubDirPath());

        if ( file_exists($groupDir) === false ) {
            return false;
        }

        if ( file_exists($groupSubDir) === false ) {
            if ( mkdir($groupSubDir, 0755) === false ) {
                return false;
            }
        }

        return true;
    }

    public function calculateHash()
    {
        return md5_file($this->realPath);
    }

    public function getPath()
    {
        return ConfigMapper::get('root_dir') . DIRECTORY_SEPARATOR . $this->groupDir . DIRECTORY_SEPARATOR . $this->groupSubDir . DIRECTORY_SEPARATOR . $this->tempName . '.part';
    }

    public function getRealPath()
    {
        return base_path(). DIRECTORY_SEPARATOR .$this->path;
    }

    public function getCompletePath($name)
    {
        return ConfigMapper::get('root_dir') . DIRECTORY_SEPARATOR . $this->groupDir . DIRECTORY_SEPARATOR . $this->groupSubDir . DIRECTORY_SEPARATOR . $name;
    }

    public function getGroupSubDirPath()
    {
        return ConfigMapper::get('root_dir') . DIRECTORY_SEPARATOR . $this->groupDir . DIRECTORY_SEPARATOR . $this->groupSubDir;
    }

    public function __set($property, $value)
    {
        if ( $property === 'chunkIndex' ) {
            $this->header->write($value);
        }
    }

    public function __get($property)
    {
        if ( $property === 'chunkIndex' ) {
            return $this->header->read();
        }

        return null;
    }

    public function __unset($property)
    {
        if ( $property === 'chunkIndex' ) {
            $this->header->delete();
        }
    }



}