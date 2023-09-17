# helper-smtp, [Packagist](https://packagist.org/packages/akbsit/helper-smtp)

Creating an Smtp class object by passing the connection settings to the constructor:

```php
$oSmtpHelper = new SmtpHelper([
    'maillogin' => 'noreply@falbar.ru',
    'mailpass'  => '*********',
    'from'      => 'Falbar',
    'host'      => 'ssl://smtp.yandex.ru', 
    'port'      => 465
]);
```

Send Email:

```php
$bStatus = $oSmtpHelper->send(
    'akbsit@yandex.ru',
    'Mail title',
    'Mail text'
);
```

If successful, it returns – **true** in another case – **false**.
