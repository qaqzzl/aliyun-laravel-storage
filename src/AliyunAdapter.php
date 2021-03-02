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

    /**
     * 获取阿里云 OssClient
     *
     * @return OssClient
     */
    public function getClient()
    {
        return $this->client;
    }

    private function logError(OssException $error, $extra = null)
    {
        \Log::error('Qiniu: ' . $error->getCode() . ' ' . $error->getMessage() . '. ' . $extra);
    }

    /**
     * 获取上传文件凭证
     * @param $string_to_sign_ordered
     * @return string
     */
    public function uploadToken($string_to_sign_ordered)
    {
        $signature = base64_encode(hash_hmac('sha1', $string_to_sign_ordered, $this->accessKeySecret, true));
        $authorization = 'OSS ' . $this->accessKeyId . ':' . $signature;
        return $authorization;
    }

    /**
     * Write a new file.
     *
     * @param string $path
     * @param string $contents
     * @param Config $config
     * @return array|false false on failure file meta data on success
     */
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

    /**
     * Write a new file using a stream.
     *
     * @param string   $path
     * @param resource $resource
     * @param Config   $config   Config object
     *
     * @return array|false false on failure file meta data on success
     */
    public function writeStream($path, $resource, Config $config)
    {
        $contents = stream_get_contents($resource);

        return $this->write($path, $contents, $config);
    }

    /**
     * Update a file.
     *
     * @param string $path
     * @param string $contents
     * @param Config $config   Config object
     *
     * @return array|false false on failure file meta data on success
     */
    public function update($path, $contents, Config $config)
    {
        $object = $this->applyPathPrefix($path);
        $this->delete($path);
        return $this->write($object, $contents, $config);
    }

    /**
     * Update a file using a stream.
     *
     * @param string   $path
     * @param resource $resource
     * @param Config   $config   Config object
     *
     * @return array|false false on failure file meta data on success
     */
    public function updateStream($path, $resource, Config $config)
    {
        $contents = stream_get_contents($resource);
        return $this->update($path, $contents, $config);
    }

    /**
     * Rename a file.
     *
     * @param string $path
     * @param string $newpath
     *
     * @return bool
     */
    public function rename($path, $newpath)
    {
        if ($this->copy($path, $newpath)) {
            return $this->delete($path);
        }
        return false;
    }

    /**
     * Copy a file.
     *
     * @param string $path
     * @param string $newpath
     *
     * @return bool
     */
    public function copy($path, $newpath)
    {
        $path = $this->applyPathPrefix($path);
        $newpath = $this->applyPathPrefix($newpath);
        return $this->client->copyObject($this->bucket, $path, $this->bucket, $newpath);
    }

    /**
     * Delete a file.
     *
     * @param string $path
     *
     * @return bool
     */
    public function delete($path)
    {
        $path = $this->applyPathPrefix($path);
        return $this->client->deleteObject($this->bucket, $path);
    }


    /**
     * @inheritDoc
     */
    public function deleteDir($dirname)
    {
        $dirname = rtrim($this->applyPathPrefix($dirname), '/').'/';
        $dirObjects = $this->listContents($dirname, true);

        if(!empty($dirObjects['objects'])){

            foreach($dirObjects['objects'] as $object) {
                $objects[] = $object['Key'];
            }

            try {
                $this->client->deleteObjects($this->bucket, $objects);
            } catch (OssException $e) {
                $this->logError($e);
                return false;
            }

        }

        try {
            $this->client->deleteObject($this->bucket, $dirname);
        } catch (OssException $e) {
            $this->logError($e);
            return false;
        }

        return true;
    }


    /**
     * Create a directory.
     *
     * @param string $dirname directory name
     * @param Config $config
     *
     * @return array|false
     */
    public function createDir($dirname, Config $config)
    {
        $object = $this->applyPathPrefix($dirname);
        $options = $config->get('options');
        $this->client->createObjectDir($this->bucket, $object, $options);
        return ['dirname' => $dirname];
    }

    /**
     * Set the visibility for a file.
     *
     * @param string $path
     * @param string $visibility
     *
     * @return array|false file meta data
     */
    public function setVisibility($path, $visibility)
    {
        $object = $this->applyPathPrefix($path);
        try {
            $this->client->putObjectAcl($this->bucket, $object, $visibility);
        } catch (OssException $e) {
            $this->logError($e);
            return false;
        }
        return compact('visibility');
    }

    /**
     * Check whether a file exists.
     *
     * @param string $path
     *
     * @return array|bool|null
     */
    public function has($path)
    {
        $object = $this->applyPathPrefix($path);
        return $this->client->doesObjectExist($this->bucket, $object);
    }

    /**
     * Read a file.
     *
     * @param string $path
     *
     * @return array|false
     */
    public function read($path)
    {
        $object = $this->applyPathPrefix($path);
        try{
            $content = $this->client->getObject($this->bucket, $object);
        } catch(OssException $e) {
            $this->logError($e);
            return false;
        }
        return compact('content');
    }

    /**
     * @inheritDoc
     */
    public function readStream($path)
    {
        $object = $this->applyPathPrefix($path);
        $file_dir = sys_get_temp_dir().$object;
        $options = array(
            OssClient::OSS_FILE_DOWNLOAD => $file_dir
        );
        $content = $this->client->getObject($this->bucket, $object, $options);
        $stream = fopen($object);
        return compact('content', 'stream', 'file_dir');
    }

    /**
     * @inheritDoc
     */
    public function listContents($directory = '', $recursive = false)
    {
        $nextMarker = '';
        $result = [];
        while (true) {
            try {
                $options = array(
                    'delimiter' => '',
                    'marker' => $nextMarker,
                );
                $listObjectInfo = $this->client->listObjects($this->client, $options);
            } catch (OssException $e) {
                $this->logError($e);
                return false;
            }
            // 得到nextMarker，从上一次listObjects读到的最后一个文件的下一个文件开始继续获取文件列表。
            $nextMarker = $listObjectInfo->getNextMarker();
            $listObject = $listObjectInfo->getObjectList();
            $listPrefix = $listObjectInfo->getPrefixList();

            if (!empty($listObject)) {
                foreach ($listObject as $objectInfo) {
                    $object['Directory']       = $directory;
                    $object['Key']          = $objectInfo->getKey();
                    $object['LastModified'] = $objectInfo->getLastModified();
                    $object['ETag']         = $objectInfo->getETag();
                    $object['Type']         = $objectInfo->getType();
                    $object['Size']         = $objectInfo->getSize();
                    $object['StorageClass'] = $objectInfo->getStorageClass();
                    $result['objects'][] = $object;
                }
            }

            if (!empty($listPrefix)) {
                foreach ($listPrefix as $prefixInfo) {
                    $result['prefix'][] = $prefixInfo->getPrefix();
                }
            }

            //递归查询子目录所有文件
            if($recursive && !empty($result['prefix'])){
                foreach( $result['prefix'] as $pfix){
                    $next  =  $this->listContents($pfix , $recursive);
                    $result["objects"] = array_merge($result['objects'], $next["objects"]);
                }
            }

//            if ($listObjectInfo->getIsTruncated() !== "true") {
//                break;
//            }
            if ($nextMarker === '') {
                break;
            }
        }
        return $result;
    }

    /**
     * @inheritDoc
     */
    public function getMetadata($path)
    {
        $object = $this->applyPathPrefix($path);

        try {
            $objectMeta = $this->client->getObjectMeta($this->bucket, $object);
        } catch (OssException $e) {
            $this->logError($e);
            return false;
        }

        return $objectMeta;
    }

    /**
     * @inheritDoc
     */
    public function getSize($path)
    {
        $metadata = $this->getMetadata($path);
        return $metadata;
    }

    /**
     * @inheritDoc
     */
    public function getMimetype($path)
    {
        $metadata = $this->getMetadata($path);
        return $metadata;
    }

    /**
     * @inheritDoc
     */
    public function getTimestamp($path)
    {
        $metadata = $this->getMetadata($path);
        return $metadata;
    }

    /**
     * @inheritDoc
     */
    public function getVisibility($path)
    {
        $metadata = $this->getMetadata($path);
        return $metadata;
    }


}