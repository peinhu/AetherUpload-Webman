<?php

namespace AetherUpload;

class Util
{
    /**
     * The rule of naming a temporary file
     * @return string
     */
    public static function generateTempName()
    {
        return time() . mt_rand(100000, 999999);
    }

    public static function getFileName($baseName, $ext)
    {
        return $baseName . '.' . $ext;
    }

    public static function generateSubDirName()
    {
        switch ( ConfigMapper::get('resource_subdir_rule') ) {
            case "year":
                $name = @date("Y", time());
                break;
            case "month":
                $name = @date("Ym", time());
                break;
            case "date":
                $name = @date("Ymd", time());
                break;
            case "const":
                $name = "subdir";
                break;
            default :
                $name = @date("Ym", time());
                break;
        }

        return $name;
    }

    public static function getDisplayLink($savedPath)
    {

        return ConfigMapper::get('route_display') . '/' . $savedPath;
    }

    public static function getDownloadLink($savedPath, $newName)
    {

        return ConfigMapper::get('route_download') . '/' . $savedPath . '/' . $newName;
    }

    public static function deleteResource($savedPath)
    {
        $params = SavedPathResolver::decode($savedPath);

        try {

            ConfigMapper::applyGroupConfig($params->group);

            $resource = new Resource($params->group, ConfigMapper::get('group_dir'), $params->groupSubDir, $params->resourceName);

            return $resource->delete();

        } catch ( \Exception $e ) {

            return false;
        }
    }

    public static function getResource($savedPath)
    {

        $params = SavedPathResolver::decode($savedPath);

        try {

            ConfigMapper::applyGroupConfig($params->group);

            $resource = new Resource($params->group, ConfigMapper::get('group_dir'), $params->groupSubDir, $params->resourceName);

            return $resource;

        } catch ( \Exception $e ) {

            return false;
        }
    }

    public static function deleteRedisSavedPath($savedPath)
    {
        $params = SavedPathResolver::decode($savedPath);

        try {

            $savedPathKey = RedisSavedPath::getKey($params->group, pathinfo($params->resourceName, PATHINFO_FILENAME));

            return RedisSavedPath::delete($savedPathKey);

        } catch ( \Exception $e ) {

            return false;

        }
    }


}
