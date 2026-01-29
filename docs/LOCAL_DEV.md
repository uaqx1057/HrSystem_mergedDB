# Local Development Setup (Windows)

This project requires PHP \>= 8.2.0. These steps help you run it reliably and quickly on Windows.

## Quick Start (PHP 8.2 via XAMPP)

1. Use the local project copy (avoid OneDrive paths for speed):
   - Recommended: `C:\Projects\ilabhr_new`
2. Start the server with PHP 8.2:
   - PowerShell: run `start-php82.ps1`
   - CMD: run `start-php82.bat`
3. Open `http://127.0.0.1:8000` and verify `http://127.0.0.1:8000/check.php` shows PHP 8.2+.

Scripts created in repo root:
- `start-php82.ps1`
- `start-php82.bat`

## OneDrive & Performance

Composer autoload generation is slow under OneDrive due to sync overhead. Work from a non-synced folder (e.g., `C:\Projects\ilabhr_new`) and optionally add Defender exclusions:
- `C:\Projects\ilabhr_new`
- `%LOCALAPPDATA%\Composer`

## Composer Tips

- Skip package discovery when you don’t need it:
  ```powershell
  $env:SKIP_PACKAGE_DISCOVER=1
  composer dump-autoload -o --no-plugins
  Remove-Item Env:SKIP_PACKAGE_DISCOVER
  ```
- This repo gates `package:discover` in `composer.json` using `SKIP_PACKAGE_DISCOVER`.

## Apache (XAMPP) VirtualHost (Optional)

Add this to `C:\xampp\apache\conf\extra\httpd-vhosts.conf`:

```
<VirtualHost *:80>
    ServerName ilabhr.local
    DocumentRoot "C:/Projects/ilabhr_new/public"
    <Directory "C:/Projects/ilabhr_new/public">
        AllowOverride All
        Require all granted
    </Directory>
    # Ensure Apache uses XAMPP PHP 8.2
</VirtualHost>
```

Update hosts:

```
127.0.0.1  ilabhr.local
```

Restart Apache and visit `http://ilabhr.local/check.php`.

## Laravel Setup

Run these with PHP 8.2:

```powershell
"C:\xampp\php\php.exe" artisan storage:link
"C:\xampp\php\php.exe" artisan migrate --no-interaction
"C:\xampp\php\php.exe" artisan db:seed --no-interaction
```

## PHP Version Gate

The public bootstrap enforces PHP \>= 8.2 in:
- `public/index.php` (version check)
- `public/check.php` (diagnostics)

If you see a “Lower PHP version” page, you’re hitting a server using PHP 8.1 (or lower). Start Laravel with PHP 8.2 or update your web server to PHP 8.2.
