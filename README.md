# Smtp

Создаем объект Smtp класса, передав настройки подключения в конструктор:

```php
$obj = new Smtp([
    "maillogin" => "noreply@falbar.ru",
    "mailpass" => "*********",
    "from" => "Фалбар",
    "host" => "ssl://smtp.yandex.ru",
    "port" => 465
]);
```

Отправляем письмо:

```php
$result = $obj->send(
    "akbsit@yandex.ru",
    "Заголовок письма",
    "Текст письма"
);
```

# Статья

[SMTP сервер для отправки писем с сайта если функция mail не работает](http://falbar.ru/article/smtp-server-dlya-otpravki-pisem-s-sajta-esli-funktsiya-mail-ne-rabotaet)