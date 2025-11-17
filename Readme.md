Here's a sample `README.md` file for your **Apix** PHP framework:

---

````markdown
# 🧩 Apix - Lightweight PHP API Framework

**Apix** is a lightweight and powerful PHP micro-framework built on top of [Slim 4](https://www.slimframework.com/) designed specifically for building robust, secure, and scalable APIs. It comes pre-integrated with essential features like **JWT authentication**, **input validation**, **middleware support**, **logging**, and a **PDO wrapper** for database operations — everything you need to get started with your next API project quickly.

---

## ✨ Features

- 🔐 **JWT Authentication** out of the box
- 🧰 **Validation** powered by Rakit\Validation
- ⚙️ **Middleware** for auth, CORS, etc.
- 📦 **PDO Wrapper** for clean and easy DB access, powered by Medoo
- 📝 **Monolog Logger** for debugging and application logs
- 🔄 Sample codes to help you bootstrap your API

---

## 🚀 Getting Started

### 1. Clone the repo

```bash
git clone https://github.com/skriptxadmin/apix.git
cd apix
```
````

### 2. Install dependencies via Composer

```bash
composer install
```

### 3. Setup Environment

Copy `.env.example` to `.env` and update the variables (DB, JWT secret, etc.)

```bash
cp .env.example .env
```

Update `.env`:

```env
APP_ENV=development
APP_DEBUG=true
DB_HOST=localhost
DB_NAME=apix_db
DB_USER=root
DB_PASS=
JWT_SECRET=your_jwt_secret_key
```

---

## 📁 Folder Structure

```
/apix
├── public/             # Entry point (index.php)
├── src/
│   ├── Controllers/     # Route controllers
│   ├── Middleware/      # Custom middleware
│   ├── Helpers/         # Utility classes (e.g., Logger, JWT)
│   ├── Models/          # DB models (optional)
│   └── Routes.php       # API routes
├── logs/                # Log output
├── vendor/              # Composer packages
├── .env                 # Environment configuration
└── composer.json
```

---

## 🧪 Sample Usage

### Sample Route (in `src/Routes.php`)

```php
$app->get('/hello/{name}', function ($request, $response, $args) {
    $name = $args['name'];
    return $response->withJson(['message' => "Hello, $name"]);
});
```

### JWT Middleware

Secure routes with JWT middleware:

```php
$app->group('/user', function (RouteCollectorProxy $group) {

    $group->get('/profile', [App\Controllers\User\ProfileController::class, 'index'])->setName('user.profile');
    $group->put('/profile', [App\Controllers\User\ProfileController::class, 'save'])->setName('user.profile.save');
   
})->add(new UserMiddleware);
```

### Validation Example

```php  
$validator = new \App\Helpers\Validator();
$data      = $request->getParsedBody();
$rules     = [
    'email'  => 'required|email|unique:users,email',
    'fname'  => 'required|min:1|max:49|regex:/^[A-Za-z][A-Za-z\s-]{1,49}$/',
    'phone'  => 'required|unique:profiles,phone|regex:/^[6-9]\d{9}$/',
    'ugroup' => 'required|exists:roles,name|enum:subscriber;administrator',
];
$messages = [
    'email.required' => 'Email is required',
];
$validationResult = $validator->make($data, $rules, $messages);
if ($validationResult !== true) {
    return $this->respond($validationResult, 409);
}
$validData = $validator->validData;
```

### PDO Wrapper Usage

```php
 $user  = (object) $this->db()->get('users', '*', $where);
```

---

## 🛠 Scripts

- **Start Local Server**

```bash
php -S localhost:8080 -t public
```

## Using S3 Storage

```php

```
 $s3 = new \App\Helpers\S3;

$args = [
    'Key'         => "",                      // Folder + filename in S3
    'Body'        => json_encode($data, JSON_PRETTY_PRINT), // Convert PHP array to JSON
    'ContentType' => 'application/json',                               // Tell S3 it's JSON
];

$s3->put($args);
---

## 📚 More Examples

- [x] User Registration & Login with JWT
- [x] Protected Profile Route
- [x] Form Validation Example
- [x] Middleware for Logging Requests

---

## 🧱 Built With

- [Slim 4](https://www.slimframework.com/)
- [Rakit\Validation](https://github.com/rakit/validation)
- [Firebase JWT](https://github.com/firebase/php-jwt)
- [Monolog](https://github.com/Seldaek/monolog)
- [Medoo] (https://github.com/catfan/Medoo)

---

## 🤝 Contributing

Pull requests are welcome. For major changes, please open an issue first.

---

## 📄 License

This project is open-source and available under the [MIT License](LICENSE).

---

## 👤 Author

**Alaksandar Jesus Gene AMS**
Entrepreneur, Developer — [skriptx.com](https://skriptx.com)

---
