<?php

namespace App\Google;

use Google\Cloud\Speech\SpeechClient;
use JamesHeinrich\GetID3\GetID3;
use App\Convertio\Convertio;
use File;
use Illuminate\Support\Facades\Storage;
use App\FFMpeg\Converter;

class SpeechRepository
{
    protected $speech;
    protected $getid3;
    protected $convertio;
    protected $converter;

    public function __construct(GetID3 $getid3,Convertio $convertio, Converter $converter) {
        $configs = [
            'keyFilePath'   => storage_path().'/'.env('GOOGLE_CREDENTIAL_FILE',''),
            'projectId' => env('GOOGLE_PROJECT_ID','')
        ];
        $this->speech =  new SpeechClient($configs);

        $this->getid3 = $getid3;
        $this->convertio = $convertio;
        $this->converter = $converter;
    }

    private function checkAudio($source = ''){
        // Analyze file and store returned data in $ThisFileInfo
        $audio = $this->getid3->analyze($source);
        //return $audio;
        if(!isset($audio['audio'])){
            return false;
        }

        $fileFormat = $audio['audio']['dataformat'];
        if($fileFormat != 'flac'){
            // convert to flac first
            $source = $this->converter->convertToFlac($source, storage_path() .'/app');
            $audio = $this->getid3->analyze($source);
        }

        $name = basename($source);

        $sampleRate = $audio['audio']['sample_rate'];
        $result = [
            'sample_rate'   => $sampleRate,
            'source'        => $source,
            'name'          => $name
        ];
        return $result;
    }

    public function convert($source = ''){
        if($source == ''){
            // sample
            $source = storage_path().'/'.'WhatsApp_Ptt_2017-02-25_at_11.aac';
            $source = $this->platformSlashes($source);
        }

        $audios = $this->checkAudio($source);
        //return $audios;
        if(empty($audios)){
            return false;
        }

        // Recognize the speech in an audio file.
        $results = $this->speech->recognize(
            fopen($audios['source'], 'r'),
            [
                'sampleRate'    => $audios['sample_rate'],
                'languageCode'  => 'id-ID'
            ]
        );
        // delete storage, LARAVEL ISSUE
        @unlink($audios['source']);
        @unlink($source);

        return $results;
    }

    // change windows path backward slash
    private function platformSlashes($path) {
        if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN') {
            $path = str_replace('\\', "/", $path);
        }
        return $path;
    }
}
