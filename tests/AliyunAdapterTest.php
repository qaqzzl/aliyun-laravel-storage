<?php
use League\Flysystem\Filesystem;
use OSS\OssClient;
use qaqzzl\AliyunStorage\AliyunAdapter;
use PHPUnit\Framework\TestCase;
class AliyunAdapterTest extends TestCase
{
    function getAliyunAdapter()
    {
        $config = [
            'bucket'=>'novel-ixcc',
            'endpoint'=>'oss-cn-shanghai.aliyuncs.com',
            'access_key'=>'LTAI4OkiPtl1oEcx',
            'secret_key'=>'eNh5v5KNF0bs4vAWsv4XJp63AdN6CV',
        ];

        $isCname   = empty($config['isCName']) ? false : $config['isCName'];
        $bucket   = $config['bucket'];
        $endpoint = $config['endpoint'];
        $access_key = $config['access_key'];
        $secret_key = $config['secret_key'];
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
    }


    /**
     * 上传测试
     */
    public function testPut()
    {
        $storage = $this->getAliyunAdapter();
        $content = file_get_contents('https://novel-h5-ixcc.oss-cn-shanghai.aliyuncs.com/h5/img/Welfare-bao.6fe0bf36.png');
        $this->assertEquals(true, $storage->put('test1.png', $content));
    }
}