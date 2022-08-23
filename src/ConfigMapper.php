<?php

namespace AetherUpload;

class ConfigMapper
{
    private static $_instance = null;
    private $root_dir;
    private $resource_subdir_rule;
    private $chunk_size;
    private $resource_maxsize;
    private $resource_extensions;
    private $group;
    private $group_dir;
    private $middleware_preprocess;
    private $middleware_uploading;
    private $middleware_display;
    private $middleware_download;
    private $header_storage_disk;
    private $forbidden_extensions;
    private $instant_completion;
    private $route_preprocess;
    private $route_uploading;
    private $route_display;
    private $route_download;
    private $lax_mode;
    private $extra_mime_types;
    private $event_before_upload_complete;
    private $event_upload_complete;
    const PREFIX = 'plugin.peinhu.aetherupload-webman.app.';

    private function __construct()
    {
        //disallow new instance
    }

    private static function instance()
    {
        if ( self::$_instance === null ) {
            self::$_instance = (new self())->applyCommonConfig();
        }

        return self::$_instance;
    }

    private function applyCommonConfig()
    {
        $this->root_dir = config(self::PREFIX.'root_dir');
        $this->chunk_size = config(self::PREFIX.'chunk_size');
        $this->resource_subdir_rule = config(self::PREFIX.'resource_subdir_rule');
        $this->header_storage_disk = config(self::PREFIX.'header_storage_disk');
        $this->forbidden_extensions = config(self::PREFIX.'forbidden_extensions');
        $this->middleware_preprocess = config(self::PREFIX.'middleware_preprocess');
        $this->middleware_uploading = config(self::PREFIX.'middleware_uploading');
        $this->middleware_display = config(self::PREFIX.'middleware_display');
        $this->middleware_download = config(self::PREFIX.'middleware_download');
        $this->instant_completion = config(self::PREFIX.'instant_completion');
        $this->route_preprocess = config(self::PREFIX.'route_preprocess');
        $this->route_uploading = config(self::PREFIX.'route_uploading');
        $this->route_display = config(self::PREFIX.'route_display');
        $this->route_download = config(self::PREFIX.'route_download');
        $this->lax_mode = config(self::PREFIX.'lax_mode');
        $this->extra_mime_types = config(self::PREFIX.'extra_mime_types');

        return $this;
    }

    private function applyGroupConfig($group)
    {
        if ( ! in_array($group, array_keys(config(self::PREFIX.'groups'))) ) {
            throw new \Exception(trans('invalid_operation'));
        }

        $this->group = $group;
        $this->group_dir = config(self::PREFIX.'groups.' . $group . '.group_dir');
        $this->resource_maxsize = config(self::PREFIX.'groups.' . $group . '.resource_maxsize');
        $this->resource_extensions = config(self::PREFIX.'groups.' . $group . '.resource_extensions');
        $this->event_before_upload_complete = config(self::PREFIX.'groups.' . $group . '.event_before_upload_complete');
        $this->event_upload_complete = config(self::PREFIX.'groups.' . $group . '.event_upload_complete');

        return $this;
    }

    public static function get($property)
    {
        return self::instance()->{$property};
    }

    public static function set($property, $value)
    {
        self::instance()->{$property} = $value;
    }

    public static function __callStatic($name, $arguments)
    {
        return self::instance()->{$name}(... $arguments);
    }

}