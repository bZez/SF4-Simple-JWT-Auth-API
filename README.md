# SF4-Simple-JWT-Auth-API
Simple API Authentication with JWT

# Demo
https://api.bzez.dev/

Admin: admin@demo.com / 123456

Admin Partner: partner@demo.com / 123456

User: user@demo.com / 123456

# Installation
`composer install`

then create databse connection

`php bin/console doctrine:database:create`

and migrate

`php bin/console doctrine:migrations:migrate -- allow-no-migration`

# JWT Structure


```javascript
// Header
{
  "cty": "JWT",
  "Token": "FirstAuthAPI",
  "alg": "HS256",
  "typ": "JWT"
}

// Payload
{
  "iss": "API Authenticator",
  "sub": "api-access-token",
  "aud": "https://yourapi.com",
  "exp": 1604102400,
  "iat": 1572512942,
  "jti": "349c18a0c316835e63fe0c737a6dca68",
  "user": {
    "login": "xxx@xxx.xxx",
    "roles": [
      "ROLE_ADMIN",
      "ROLE_USER"
    ],
    "privileges": [
      "GET",
      "POST",
      "PUT",
      "DELETE"
    ]
  }
}
```

# Support
@bZez | sam@bzez.dev
