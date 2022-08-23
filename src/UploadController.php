<?php

namespace AetherUpload;

use support\Translation;

class UploadController
{
    use ExamplePageTrait,SimpleValidateTrait;

    public function __construct()
    {
        Translation::addResource('phpfile',config('translation')['path'].DIRECTORY_SEPARATOR.'aetherupload'.DIRECTORY_SEPARATOR.'zh'.DIRECTORY_SEPARATOR.'messages.php','zh');
        Translation::addResource('phpfile',config('translation')['path'].DIRECTORY_SEPARATOR.'aetherupload'.DIRECTORY_SEPARATOR.'en'.DIRECTORY_SEPARATOR.'messages.php','en');
    }

    /**
     * Preprocess the upload request
     */
    public function preprocess()
    {
        locale(request()->input('locale', 'en'));

        $result = [
            'error'                => 0,
            'chunkSize'            => 0,
            'groupSubDir'          => '',
            'resourceTempBaseName' => '',
            'resourceExt'          => '',
            'savedPath'            => '',
        ];

        if($this->validatedWithError(request(), [
            'resource_name' => 'required',
            'resource_size' => 'required',
            'group'         => 'required',
            'resource_hash' => 'present',
        ])){
            return Responser::reportError($result, trans('invalid_resource_params'));
        }

        try {

            $resourceName = request()->input('resource_name');
            $resourceSize = request()->input('resource_size');
            $resourceHash = request()->input('resource_hash');
            $group = request()->input('group');

            ConfigMapper::applyGroupConfig($group);

            $result['resourceTempBaseName'] = $resourceTempBaseName = Util::generateTempName();
            $result['resourceExt'] = $resourceExt = strtolower(pathinfo($resourceName, PATHINFO_EXTENSION));
            $result['groupSubDir'] = $groupSubDir = Util::generateSubDirName();
            $result['chunkSize'] = ConfigMapper::get('chunk_size');

            $partialResource = new PartialResource($resourceTempBaseName, $resourceExt, $groupSubDir);

            $partialResource->filterBySize($resourceSize);

            $partialResource->filterByExtension($resourceExt);

            // determine if this upload meets the condition of instant completion
            if ( ConfigMapper::get('instant_completion') === true && ! empty($resourceHash) && RedisSavedPath::exists($savedPathKey = RedisSavedPath::getKey($group, $resourceHash)) === true ) {
                $result['savedPath'] = RedisSavedPath::get($savedPathKey);

                return Responser::returnResult($result);
            }

            $partialResource->create();

            $partialResource->chunkIndex = 0;

        } catch ( \Exception $e ) {

            return Responser::reportError($result, $e->getMessage());
        }

        return Responser::returnResult($result);
    }

    /**
     * Handle and save the uploaded chunks
     */
    public function saveChunk()
    {
        locale(request()->input('locale', 'en'));

        $result = ['error' => 0, 'savedPath' => ''];

        if($this->validatedWithError(request(), [
            'chunk_total'            => 'required',
            'chunk_index'            => 'required',
            'resource_temp_basename' => 'required',
            'resource_ext'           => 'required',
            'group_subdir'           => 'required',
            'group'                  => 'required',
            'resource_hash'          => 'present',
        ])){
            return Responser::reportError($result, trans('invalid_resource_params'));
        }

        $chunkTotalCount = request()->input('chunk_total');
        $chunkIndex = request()->input('chunk_index');
        $resourceTempBaseName = request()->input('resource_temp_basename');
        $resourceExt = request()->input('resource_ext');
        $chunk = request()->file('resource_chunk');
        $groupSubDir = request()->input('group_subdir');
        $resourceHash = request()->input('resource_hash');
        $group = request()->input('group');
        $savedPathKey = RedisSavedPath::getKey($group, $resourceHash);
        $partialResource = null;

        try{

            ConfigMapper::applyGroupConfig($group);

            $partialResource = new PartialResource($resourceTempBaseName, $resourceExt, $groupSubDir);

            // do a check to prevent security intrusions
            if ( $partialResource->exists() === false ) {
                throw new \Exception(trans('invalid_operation'));
            }

            // determine if this upload meets the condition of instant completion
            if ( ConfigMapper::get('instant_completion') === true && ! empty($resourceHash) && RedisSavedPath::exists($savedPathKey) === true ) {

                unlink($partialResource->realPath);

                unset($partialResource->chunkIndex);

                $result['savedPath'] = RedisSavedPath::get($savedPathKey);

                return Responser::returnResult($result);
            }

            if ( $chunk->isValid() === false ) {
                throw new \Exception(trans('upload_error'));
            }

            // validate the data in header file to avoid the errors when network issue occurs
            if ( (int)($partialResource->chunkIndex) !== (int)$chunkIndex - 1 ) {
                return Responser::returnResult($result);
            }

            $partialResource->append($chunk->getRealPath());

            $partialResource->chunkIndex = $chunkIndex;

            // determine if the resource file is completed
            if ( $chunkIndex === $chunkTotalCount ) {

                $partialResource->checkSize();

                $partialResource->checkMimeType();

                // trigger the event before an upload completes
                if ( ConfigMapper::get('event_before_upload_complete') === true ) {
                    \Webman\Event\Event::emit('aetherupload.before_upload_complete', $partialResource);
                }

                $resourceRealHash = $partialResource->calculateHash();

                if ( ConfigMapper::get('lax_mode') === false && $resourceHash !== $resourceRealHash ) {
                    throw new \Exception(trans('upload_error'));
                }

                $partialResource->rename($completeName = Util::getFileName($resourceRealHash, $resourceExt));

                $savedPath = SavedPathResolver::encode($group, $groupSubDir, $completeName);

                if ( ConfigMapper::get('instant_completion') === true ) {
                    RedisSavedPath::set($savedPathKey, $savedPath);
                }

                unset($partialResource->chunkIndex);

                // trigger the event when an upload completes
                if ( ConfigMapper::get('event_upload_complete') === true ) {
                    \Webman\Event\Event::emit('aetherupload.upload_complete', new Resource($group, ConfigMapper::get('group_dir'), $groupSubDir, $completeName));
                }

                $result['savedPath'] = $savedPath;
            }

        } catch ( \Exception $e ) {

            @unlink($partialResource->realPath);

            unset($partialResource->chunkIndex);

            return Responser::reportError($result, $e->getMessage());
        }

        return Responser::returnResult($result);

    }



}
