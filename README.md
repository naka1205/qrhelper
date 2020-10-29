# qrhelper

### 安装
```
composer require naka507/qrhelper
```

### 解析二维码
```php
require __DIR__ . '/vendor/autoload.php';

use QrHelper\QrReader;

$qr = new QrReader('./wemini.png');
echo $qr->text();
```