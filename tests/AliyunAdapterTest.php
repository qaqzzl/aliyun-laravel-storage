<?php
use League\Flysystem\Filesystem;
use OSS\OssClient;
use qaqzzl\AliyunStorage\AliyunAdapter;
use PHPUnit\Framework\TestCase;
use qaqzzl\AliyunStorage\Plugins\UploadToken;

class AliyunAdapterTest extends TestCase
{
    function getAliyunAdapter()
    {
        $config = require_once('./config.php');
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
        $file_system->addPlugin(new UploadToken());

        return $file_system;
    }


    /**
     * 上传测试
     */
    public function testPut()
    {
        $file_system = $this->getAliyunAdapter();
        $content = 'test.test';
        $this->assertEquals(true, $file_system->put('test1.png', $content));
    }

    public function testPutStream()
    {
        $file_system = $this->getAliyunAdapter();
        $resource = fopen('./test.test','w');
        $this->assertEquals(true, $file_system->putStream('test1.png', $resource));
    }

    public function testUpdate()
    {
        $file_system = $this->getAliyunAdapter();
        $content = 'test.test';
        $this->assertEquals(true, $file_system->update('test1.png', $content));
    }

    public function testRename()
    {
        $file_system = $this->getAliyunAdapter();
        $this->assertEquals(true, $file_system->rename('test1.png', 'test2.png'));
    }

    public function testCopy()
    {
        $file_system = $this->getAliyunAdapter();
        $this->assertEquals(true, $file_system->copy('test2.png', 'test1.png'));
    }

    public function testDelete()
    {
        $file_system = $this->getAliyunAdapter();
        $this->assertEquals(true, $file_system->delete('test2.png'));
    }

    public function testCreateDir()
    {
        $file_system = $this->getAliyunAdapter();
        $this->assertEquals(true, $file_system->createDir('test'));
    }

    public function testDeleteDir()
    {
        $file_system = $this->getAliyunAdapter();
        $this->assertEquals(true, $file_system->deleteDir('test'));
    }

    public function testSetVisibility()
    {
        $file_system = $this->getAliyunAdapter();
        // private | public
        $this->assertEquals(true, $file_system->setVisibility('test1.png','private'));
    }

    public function testHas()
    {
        $file_system = $this->getAliyunAdapter();
        $has = $file_system->has('test1.png');
        echo $has;
        $this->assertIsBool(true, $has);
    }

    public function testRead()
    {
        $file_system = $this->getAliyunAdapter();
        $res = $file_system->read('test1.png');
        $this->assertNotEquals(null, $res);
    }

    public function testReadStream()
    {
        $file_system = $this->getAliyunAdapter();
        $res = $file_system->readStream('test1.png');
        $this->assertNotEquals(false, $res);
    }

    public function testGetMetadata()
    {
        $file_system = $this->getAliyunAdapter();
        $res = $file_system->getMetadata('test1.png');
        var_dump($res);
        $this->assertNotEquals(false, $res);
    }

    public function testGetSize()
    {
        $file_system = $this->getAliyunAdapter();
        $res = $file_system->getSize('test1.png');
        var_dump($res);
        $this->assertNotEquals(false, $res);
    }

    public function testGetVisibility()
    {
        $file_system = $this->getAliyunAdapter();
        $res = $file_system->getVisibility('test1.png');
        var_dump($res);
        $this->assertNotEquals(false, $res);
    }

    public function testUploadToken()
    {
        $file_system = $this->getAliyunAdapter();
        $res = $file_system->uploadToken();
        var_dump($res);
        $this->assertIsString($res);
    }
}