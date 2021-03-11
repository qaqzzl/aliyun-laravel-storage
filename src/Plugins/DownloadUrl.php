<?php
/**
 *
 * User: zerozz
 * Date: 2021/3/11
 * Email: <erppcc@163.com>
 **/

namespace qaqzzl\AliyunStorage\Plugins;


use League\Flysystem\Plugin\AbstractPlugin;

class DownloadUrl extends AbstractPlugin
{

    public function getMethod()
    {
        return 'downloadUrl';
    }

    public function handle($path = null, $expires = 3600)
    {
        return $this->filesystem->getAdapter()->downloadUrl($path, $expires);
    }

}