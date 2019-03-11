# yii2-hawk-auth

Hawk authorization for yii2, use package: [shawm11/hawk-auth-php](https://github.com/shawm11/hawk-auth-php)

## Installation

run:

```
composer require hubeiwei/yii2-hawk-auth 1.0.x-dev
```

or add:

```
"hubeiwei/yii2-hawk-auth": "1.0.x-dev"
```

to the require section of your composer.json file.

## Agreement:

id: AppKey

key: AppSecret

algorithm: sha256

## Usage

### Server

User:

Add AppKey and AppSecret column into your user table

```php
use hubeiwei\yii2Hawk\Auth as HawkAuth;

public static function findIdentityByAccessToken($token, $type = null)
{
    // ...

    if ($type == HawkAuth::class) {
        return self::find()->where(['app_key' => $token])->one();
    }

    // ...
}
```

Controller behaviors:

```php
use hubeiwei\yii2Hawk\Auth as HawkAuth;

public function behaviors()
{
    $behaviors = parent::behaviors();
    $behaviors['authenticator'] = [
        'class' => HawkAuth::class,
        // 'header' => 'Authorization',
        // 'algorithm' => 'sha256',
        // 'keyAttribute' => 'app_secret',
    ];
    return $behaviors;
}
```

### Client

* [js](https://github.com/hueniverse/hawk#usage-example)

* [php](https://github.com/shawm11/hawk-auth-php#client)

* [postman](https://learning.getpostman.com/docs/postman/sending_api_requests/authorization/#hawk-authentication)
