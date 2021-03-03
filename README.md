# 阿里云OSS云储存 Laravel Storage版

基于 https://github.com/aliyun/aliyun-oss-php-sdk 开发

符合Laravel 的Storage用法。

## 安装

- ```composer require qaqzzl/aliyun-laravel-storage```

#### 添加 service provider（optional. if laravel < 5.5 || lumen）
```PHP
// laravel < 5.5
qaqzzl\AliyunStorage\AliyunFilesystemServiceProvider::class,

// lumen
$app->register(qaqzzl\AliyunStorage\AliyunFilesystemServiceProvider::class);
```

#### 配置文件
- ```config/filesystem.php``` 里面的 ```disks```数组加上：
```php
'disks' => [
    // code...
    'ali'=> [
        'driver'    => 'ali',
        'domains' => [
            'default'   => env('ALIYUN_OSS_DOMAIN'), //你的七牛域名
            'https'     => '',         //你的HTTPS域名
            'custom'    => '',     //你的自定义域名
        ],
        'access_key'    => env('ALIYUN_OSS_ACCESS_KEY'),    //AccessKey
        'secret_key'    => env('ALIYUN_OSS_SECRET_KEY'),    //SecretKey
        'endpoint'      => env('ALIYUN_OSS_ENDPOINT'),      //Endpoint
        'bucket'        => env('ALIYUN_OSS_BUCKET'),        //Bucket名字
        'notify_url'    => '',  //持久化处理回调地址
    ],
],
```

## 使用方法
```php
$disk = \Storage::disk('ali');
$disk->exists('file.jpg');                      //文件是否存在
$disk->get('file.jpg');                         //获取文件内容
$disk->put('file.jpg',$contents);               //上传文件
$disk->delete('file.jpg');                      //删除文件
$disk->delete(['file1.jpg', 'file2.jpg']);      //删除文件
$disk->copy('old/file1.jpg', 'new/file1.jpg');  //复制文件到新的路径
$disk->move('old/file1.jpg', 'new/file1.jpg');  //移动文件到新的路径
$disk->size('file1.jpg');                       //取得文件大小
$disk->lastModified('file1.jpg');               //取得最近修改时间 (UNIX)
$disk->files($directory);                       //取得目录下所有文件
$disk->deleteDirectory($directory);             //删除目录，包括目录下所有子文件子目录
```