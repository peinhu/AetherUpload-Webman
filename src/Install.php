<?php
namespace AetherUpload;

class Install
{
    const WEBMAN_PLUGIN = true;

    /**
     * @var array
     */
    protected static $pathRelation = array (
      '../config' => 'config/plugin/peinhu/aetherupload-webman',
      '../assets' => 'public/vendor/aetherupload/js',
      '../commands' => 'app/command',
      '../translations' => 'resource/translations/aetherupload',
    );

    /**
     * Install
     * @return void
     */
    public static function install()
    {
        @mkdir(base_path()."/storage/app/aetherupload/file", 0755 ,true);
        @mkdir(base_path()."/storage/app/aetherupload/_header", 0755 ,true);
        static::installByRelation();
    }

    /**
     * Uninstall
     * @return void
     */
    public static function uninstall()
    {
        self::uninstallByRelation();
    }

    /**
     * installByRelation
     * @return void
     */
    public static function installByRelation()
    {
        foreach (static::$pathRelation as $source => $dest) {
            if ($pos = strrpos($dest, '/')) {
                $parent_dir = base_path().'/'.substr($dest, 0, $pos);
                if (!is_dir($parent_dir)) {
                    mkdir($parent_dir, 0777, true);
                }
            }
            //symlink(__DIR__ . "/$source", base_path()."/$dest");
            copy_dir(__DIR__ . "/$source", base_path()."/$dest");
        }
    }

    /**
     * uninstallByRelation
     * @return void
     */
    public static function uninstallByRelation()
    {
        foreach (static::$pathRelation as $source => $dest) {
            /*if (is_link(base_path()."/$dest")) {
                unlink(base_path()."/$dest");
            }*/
            remove_dir(base_path()."/$dest");
        }
    }
    
}
