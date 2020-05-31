## Documentation 
## Setting up twilio-php

**twilio-php** is available on Packagist as 
[`Packagist: twilio/sdk`](https://packagist.org/packages/twilio/sdk) 

And github as [`Github: twilio/sdk`](https://github.com/twilio/twilio-php)
```
composer require twilio/sdk
```
Set the Twilio Account Details in .env
```
TWILIO_ACCOUNT_SID = "account_sid"
TWILIO_AUTH_TOKEN = "auth_token"
TWILIO_PHONE = "phone"
```

Add Twilio Config File  ```config/twilio.php```

Configure webhooks urls in twilio account

e.g. ```http://127.0.0.1:8000/admin/twilio```
