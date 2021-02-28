<?php


namespace qaqzzl\AliyunStorage;

use League\Flysystem\Adapter\AbstractAdapter;
use League\Flysystem\Config;
use League\Flysystem\Util;
use OSS\Core\OssException;
use OSS\OssClient;

class AliyunAdapter extends AbstractAdapter
{
    //Aliyun OSS Client OssClient
    protected $client;
    //bucket name
    protected $bucket;
    protected $accessKeyId;
    protected $accessKeySecret;


    public function __construct(OssClient $client, $bucket, $accessKeyId, $accessKeySecret)
    {
        $this->client = $client;
        $this->bucket = $bucket;
        $this->accessKeyId = $accessKeyId;
        $this->accessKeySecret = $accessKeySecret;
    }

    public function getBucket()
    {
        return $this->bucket;
    }

    public function getClient()
    {
        return $this->client;
    }

    private function logError(OssException $error, $extra = null)
    {
        \Log::error('Qiniu: ' . $error->getCode() . ' ' . $error->getMessage() . '. ' . $extra);
    }

    public function uploadToken($string_to_sign_ordered)
    {
        $signature = base64_encode(hash_hmac('sha1', $string_to_sign_ordered, $this->accessKeySecret, true));
        $authorization = 'OSS ' . $this->accessKeyId . ':' . $signature;
        return $authorization;
    }

    public function write($path, $contents, Config $config)
    {
        $object = $this->applyPathPrefix($path);
        $options = $config->get('options');

        if (! isset($options[OssClient::OSS_LENGTH])) {
            $options[OssClient::OSS_LENGTH] = Util::contentSize($contents);
        }
        if (! isset($options[OssClient::OSS_CONTENT_TYPE])) {
            $options[OssClient::OSS_CONTENT_TYPE] = Util::guessMimeType($path, $contents);
        }
        try {
            $this->client->putObject($this->bucket, $object, $contents, $options);
        } catch (OssException $e) {
            $this->logError($e);
            return false;
        }
        return ['path'=>$path];
    }

    public function writeStream($path, $resource, Config $config)
    {

    }

    public function update($path, $contents, Config $config)
    {
        // TODO: Implement update() method.
    }

    public function updateStream($path, $resource, Config $config)
    {
        $contents = stream_get_contents($resource);
        return $this->update($path, $contents, $config);
    }

    public function rename($path, $newpath)
    {
        // TODO: Implement rename() method.
    }

    public function copy($path, $newpath)
    {
        // TODO: Implement copy() method.
    }

    public function delete($path)
    {
        return $this->client->deleteObject($this->bucket, $path);
    }

    public function deleteDir($dirname)
    {
        // TODO: Implement deleteDir() method.
    }

    public function createDir($dirname, Config $config)
    {
        // TODO: Implement createDir() method.
    }

    public function setVisibility($path, $visibility)
    {
        // TODO: Implement setVisibility() method.
    }

    public function has($path)
    {
        // TODO: Implement has() method.
    }

    public function read($path)
    {
        // TODO: Implement read() method.
    }

    public function readStream($path)
    {
        // TODO: Implement readStream() method.
    }

    public function listContents($directory = '', $recursive = false)
    {
        // TODO: Implement listContents() method.
    }

    public function getMetadata($path)
    {
        // TODO: Implement getMetadata() method.
    }

    public function getSize($path)
    {
        // TODO: Implement getSize() method.
    }

    public function getMimetype($path)
    {
        // TODO: Implement getMimetype() method.
    }

    public function getTimestamp($path)
    {
        // TODO: Implement getTimestamp() method.
    }

    public function getVisibility($path)
    {
        // TODO: Implement getVisibility() method.
    }


}