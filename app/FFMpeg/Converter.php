<?php

namespace App\FFMpeg;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;


class Converter
{
    public function __construct() {

    }

    public function convertToFlac($source = '',$targetDir = ''){
        if($targetDir == ''){
            $targetDir = 'C:/xampp/htdocs/line-bot-php/storage';
        }
        $name = md5(date("Y-m-d H:i:s")).'.flac';
        $newSource = $targetDir.'/'.$name;

        $ffmpeg = env('FFMPEG_DIR');
        $command = $ffmpeg." -i ".$source." -c:a flac ".$newSource;


        $process = new Process($command);
        $process->run();

        // executes after the command finishes
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return $newSource;
    }
}