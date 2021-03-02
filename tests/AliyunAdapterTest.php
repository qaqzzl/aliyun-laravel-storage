<?php
use League\Flysystem\Filesystem;
use OSS\OssClient;
use qaqzzl\AliyunStorage\AliyunAdapter;
use PHPUnit\Framework\TestCase;
class AliyunAdapterTest extends TestCase
{
    function getAliyunAdapter()
    {
        /*
        $config = [
            'bucket'=>'',
            'endpoint'=>'',
            'access_key'=>'',
            'secret_key'=>'',
        ];
        */

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
        $file_system = $this->getAliyunAdapter();
        $content = file_get_contents('https://novel-h5-ixcc.oss-cn-shanghai.aliyuncs.com/h5/img/Welfare-bao.6fe0bf36.png');
        $this->assertEquals(true, $file_system->put('test1.png', $content));
    }

    public function testPutStream()
    {
        $file_system = $this->getAliyunAdapter();
        $resource = fopen('./qrcode_bg1.jpg','r');
        $this->assertEquals(true, $file_system->putStream('test1.png', $resource));
    }
}