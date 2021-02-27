<?php
namespace qaqzzl\AliyunStorage\Plugins;

use League\Flysystem\FilesystemInterface;
use League\Flysystem\Plugin\AbstractPlugin;

class UploadToken extends AbstractPlugin {
    /**
     * Get the method name.
     *
     * @return string
     */
    public function getMethod()
    {
        return 'uploadToken';
    }

    public function handle($path = null, $expires = 3600, $policy = null, $strictPolicy = true)
    {
        return $this->filesystem->getAdapter()->uploadToken($path, $expires, $policy, $strictPolicy);
    }
}