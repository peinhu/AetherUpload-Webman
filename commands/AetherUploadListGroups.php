<?php

namespace app\command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use AetherUpload\ConfigMapper;


class AetherUploadListGroups extends Command
{
    protected static $defaultName = 'aetherupload:groups';
    protected static $defaultDescription = 'List and create the directories for the groups';

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $rootDir = base_path().DIRECTORY_SEPARATOR.config(ConfigMapper::PREFIX.'root_dir');
        try {

            if ( ! is_dir($rootDir) ) {
                mkdir($rootDir . DIRECTORY_SEPARATOR . '_header', 0755 ,true);
                $output->writeln('Root directory "' . $rootDir . '" has been created.');
            }

            $directories = array_map(function ($directory) {
                return basename($directory);
            }, $this->getDirs($rootDir));

            $groupDirs = array_map(function ($v) {
                return $v['group_dir'];
            }, config(ConfigMapper::PREFIX.'groups'));

            foreach ( $groupDirs as $groupDir ) {
                if ( in_array($groupDir, $directories) ) {
                    continue;
                } else {
                    if ( mkdir($rootDir . DIRECTORY_SEPARATOR . $groupDir, 0755) ) {
                        $output->writeln('Directory "' . $rootDir . DIRECTORY_SEPARATOR . $groupDir . '" has been created.');
                    } else {
                        throw new \Exception('Fail to create directory "' . $rootDir . DIRECTORY_SEPARATOR . $groupDir . '".');
                    }
                }
            }

            $output->writeln('Group-Directory List:');

            foreach ( config(ConfigMapper::PREFIX.'groups') as $groupName => $groupArr ) {
                if ( is_dir($rootDir . DIRECTORY_SEPARATOR . $groupArr['group_dir']) ) {
                    $output->writeln($groupName . ' - ' . $rootDir.DIRECTORY_SEPARATOR.$groupArr['group_dir']);
                }
            }

        } catch ( \Exception $e ) {

            $output->writeln('Error: '.$e->getMessage());

            return self::FAILURE;
        }

        return self::SUCCESS;
    }


    public function getDirs($path)
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

}