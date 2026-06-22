# JWT Setup Instructions

## 1. Install JWT Package

Run the following command to install the JWT authentication package:

```bash
composer require php-open-source-saver/jwt-auth
```

## 2. Publish JWT Config

Run the following command to publish the JWT configuration:

```bash
php artisan vendor:publish --provider="PHPOpenSourceSaver\JWTAuth\Providers\LaravelServiceProvider"
```

## 3. Generate JWT Secret

Run the following command to generate a JWT secret key:

```bash
php artisan jwt:secret
```

## 4. Add to .env

Add the following variables to your `.env` file:

```env
JWT_SECRET=your-secret-key-here
JWT_TTL=60
JWT_REFRESH_TTL=20160
JWT_BLACKLIST_ENABLED=true
JWT_ALGO=HS256
```

## 5. Run Migrations

Run the following command to create the database tables:

```bash
php artisan migrate
```

## 6. Run Seeders

Run the following command to seed the database with roles and default user:

```bash
php artisan db:seed --class=RoleSeeder
php artisan db:seed --class=UserSeeder
```

## Default User Credentials

- Email: admin@example.com
- Password: Admin123!
- Role: Super Admin
