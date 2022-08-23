<?php

use Webman\Route;


if ( config('app.debug') ) {

    Route::get('/aetherupload', [\AetherUpload\UploadController::class, 'getExamplePage']);

    Route::post('/aetherupload', [\AetherUpload\UploadController::class, 'postExamplePage']);

    Route::get('/aetherupload/example_source', [\AetherUpload\UploadController::class, 'examplePageSource']);

}


Route::post(\AetherUpload\ConfigMapper::get('route_preprocess'), [\AetherUpload\UploadController::class, 'preprocess'])->middleware(\AetherUpload\ConfigMapper::get('middleware_preprocess'));

Route::post(\AetherUpload\ConfigMapper::get('route_uploading'), [\AetherUpload\UploadController::class, 'saveChunk'])->middleware(\AetherUpload\ConfigMapper::get('middleware_uploading'));

Route::get(\AetherUpload\ConfigMapper::get('route_display').'/{uri}', [\AetherUpload\ResourceController::class, 'display'])->middleware(\AetherUpload\ConfigMapper::get('middleware_display'));

Route::get(\AetherUpload\ConfigMapper::get('route_download').'/{uri}/{newName}',  [\AetherUpload\ResourceController::class, 'download'])->middleware(\AetherUpload\ConfigMapper::get('middleware_download'));

//Route::add(['OPTIONS'],\AetherUpload\ConfigMapper::get('route_uploading'), [\AetherUpload\UploadController::class, 'options']);
//Route::options(\AetherUpload\ConfigMapper::get('route_preprocess'),  [\AetherUpload\UploadController::class, 'options']);



