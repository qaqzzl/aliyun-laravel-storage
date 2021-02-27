<?php


namespace qaqzzl\AliyunStorage;

use League\Flysystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use OSS\OssClient;

class AliyunFilesystemServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Storage::extend('ali', function ($app, $config) {
            $isCname   = empty($config['isCName']) ? false : $config['isCName'];
            $bucket   = $config['bucket'];
            $endpoint = $config['endpoint'];
            $access_key = $config['access_key'];
            $secret_key = $config['access_key'];
            $oss_client = new OssClient(
                $config['access_key'],
                $config['secret_key'],
                $config['endpoint'],
                $isCname,
                isset($config['securityToken']) ? $config['securityToken'] : null,
                isset($config['requestProxy']) ? $config['requestProxy'] : null
            );
            $qiniu_adapter = new AliyunAdapter($oss_client, $bucket, $access_key, $secret_key);
            $file_system = new Filesystem($qiniu_adapter);
//            $file_system->addPlugin(new PrivateDownloadUrl());

            return $file_system;
        });
    }

    public function register()
    {
        //
    }
}