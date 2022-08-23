<?php

namespace app\command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use AetherUpload\ConfigMapper;
use AetherUpload\SavedPathResolver;
use AetherUpload\RedisSavedPath;


class AetherUploadBuildRedisHashes extends Command
{

    protected static $defaultName = 'aetherupload:build';
    protected static $defaultDescription = 'Rebuild the correlations between hashes and file storage paths in Redis';

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $savedPathArr = [];
        $output->writeln('Start rebuilding the correlations...');
        try {

            RedisSavedPath::deleteAll();

            foreach ( config(ConfigMapper::PREFIX.'groups') as $groupName => $group ) {

                $path = base_path().DIRECTORY_SEPARATOR.ConfigMapper::get('root_dir') . DIRECTORY_SEPARATOR . $group['group_dir'];

                $subDirNames = $this->getDirsPath($path);

                foreach ( $subDirNames as $subDirName ) {
                    $fileNames = $this->getFilesPath($path.DIRECTORY_SEPARATOR.$subDirName);
                    foreach ( $fileNames as $fileName ) {
                        if ( pathinfo($fileName, PATHINFO_EXTENSION) === 'part' ) {
                            continue;
                        }

                        $savedPathArr[RedisSavedPath::getKey($groupName, pathinfo($fileName, PATHINFO_FILENAME))] = SavedPathResolver::encode($group['group_dir'], basename($subDirName), basename($fileName));

                    }
                }
            }

            RedisSavedPath::setMulti($savedPathArr);

            $output->writeln(count($savedPathArr) . ' items have been set in Redis.');
            $output->writeln('Done.');
        } catch ( \Exception $e ) {

            $output->writeln('Error: '.$e->getMessage());

            return self::FAILURE;
        }

        return self::SUCCESS;

    }

    public function getDirsPath($path)
    {
        $arr = array();
        $data = scandir($path);
        foreach ($data as $value){
            if($value != '.' && $value != '..'){
                if(is_dir($path.DIRECTORY_SEPARATOR.$value)){
                    $arr[] = $value;
                }
            }
        }
        return $arr;
    }

    public function getFilesPath($path)
    {
        $arr = array();
        $data = scandir($path);
        foreach ($data as $value){
            if($value != '.' && $value != '..'){
                if(is_file($path.DIRECTORY_SEPARATOR.$value)){
                    $arr[] = $value;
                }
            }
        }
        return $arr;
    }


}
