# Smtp

Создаем объект Smtp класса, передав настройки подключения в конструктор:

```php
$oSmtp = new Smtp([
    "maillogin" => "noreply@falbar.ru", // string Почта с которой отправляем письмо
    "mailpass" => "*********", // string Пароль почты
    "from" => "Фалбар", // string Заголовок от кого
    "host" => "ssl://smtp.yandex.ru", // string Адрес почтового сервера
    "port" => 465 // Порт
]);
```

Отправляем письмо:

```php
$bStatus = $oSmtp->send(
    "akbsit@yandex.ru", // string Почта на которую отправляем письмо
    "Заголовок письма", // string Заголовок письма
    "Текст письма" // string Текст письма
);
```

При успешной отработке возвращает – **true** в другом случае – **false**.

# Статья

[SMTP сервер для отправки писем с сайта если функция mail не работает](http://falbar.ru/article/smtp-server-dlya-otpravki-pisem-s-sajta-esli-funktsiya-mail-ne-rabotaet)