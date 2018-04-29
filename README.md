# Smtp

Создаем объект Smtp класса, передав настройки подключения в конструктор:

```php
$oSmtp = new Smtp([
    "maillogin" => "noreply@falbar.ru",
    "mailpass" => "*********",
    "from" => "Фалбар",
    "host" => "ssl://smtp.yandex.ru",
    "port" => 465
]);
```

Отправляем письмо:

```php
$bStatus = $oSmtp->send(
    "akbsit@yandex.ru",
    "Заголовок письма",
    "Текст письма"
);
```

При успешной отработке возвращает – **true** в другом случае – **false**.

# Статья

[SMTP сервер для отправки писем с сайта если функция mail не работает](http://falbar.ru/article/smtp-server-dlya-otpravki-pisem-s-sajta-esli-funktsiya-mail-ne-rabotaet)