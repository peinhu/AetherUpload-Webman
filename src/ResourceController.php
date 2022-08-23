<?php

namespace AetherUpload;

use \Webman\Http\Request;

class ResourceController
{

    public function display(Request $request, $uri)
    {

        try {

            $params = SavedPathResolver::decode($uri);

            ConfigMapper::applyGroupConfig($params->group);

            $resource = new Resource($params->group, ConfigMapper::get('group_dir'), $params->groupSubDir, $params->resourceName);

            if ( $resource->exists() === false ) {
                throw new \Exception;
            }

        } catch ( \Exception $e ) {

            return response('display fail', 404);
        }

        return response()->file($resource->realPath);
    }

    public function download(Request $request, $uri, $newName = null)
    {

        try {

            $params = SavedPathResolver::decode($uri);

            ConfigMapper::applyGroupConfig($params->group);

            $resource = new Resource($params->group, ConfigMapper::get('group_dir'), $params->groupSubDir, $params->resourceName);

            if ( $resource->exists() === false ) {
                throw new \Exception;
            }

            $newResource = Util::getFileName($newName, pathinfo($resource->name, PATHINFO_EXTENSION));

        } catch ( \Exception $e ) {

            return response('download fail', 404);
        }

        return response()->download($resource->realPath,$newResource);
    }


}
