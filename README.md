# sLetter

e-mail send library

## install

`composer require shadoll/sletter`

## usage

```
require __DIR__.'/../vendor/autoload.php';

$letter = (new shadoll\sLetter);
```

set mail variables:

```
$letter->set([
    'sender' => 'mailgun', // sender - support mail or mailgun-service
    'logoUri' => "https://site.com/logo.png", // link to logo that showing in letter
    'fromMail' => "site@site.com", // sender email
    'fromName' => "SiteLetter", // sender name
    'toMail' => "info@site.com", // resipient email
    'senderIP' => $_SERVER['REMOTE_ADDR'],
    'mailgun_apikey' => 'key',
    'mailgun_domain' => 'mg.site.com',
]);
```

adding fields titles:

```
$letter->setLang([
    'order' => 'Замовлення',
    'message' => 'Повідомлення',
    'comment' => 'Повідомлення',
    'date' => 'Дата',
    'time' => 'Час',
    'type' => 'Тип',
    'doors' => 'Кількість дверцят',
    'width' => 'Ширина',
    'depth' => 'Глибина',
]);
```

set fields from form:

```
$letter->setData([
    'name' => !empty($_REQUEST['name'])?($_REQUEST['name']):'',
    'phone' => !empty($_REQUEST['phone'])?($_REQUEST['phone']):'',
    'email' => !empty($_REQUEST['email'])?($_REQUEST['email']):'',
    'message' => !empty($_REQUEST['message'])?($_REQUEST['message']):'',
]);
```

fields list to validate before send (not working now):

```
$letter
    ->validate([
        'name',
        'phone',
        'email',
    ])
```

sending email

```
$letter
    ->detect()
    ->send()
    ->state();
```

detect - get sender info from his IP

send - if no error send email

state - action result (not fully working now)
