# IIS Setup (Windows, PHP 8.2)

This project includes IIS rewrite rules in [public/web.config](public/web.config), so once PHP 8.2 FastCGI is configured, Laravel routes will work.

## Requirements
- PHP 8.2 FastCGI (use C:\xampp\php\php-cgi.exe)
- URL Rewrite module for IIS (required for Laravel routing)
  - https://www.microsoft.com/en-us/download/details.aspx?id=47337

## Steps
1. Install URL Rewrite (if not installed).
2. Add a site pointing to the public folder:
   - IIS Manager → Sites → Add Website
   - Site name: ilabhr
   - Physical path: C:\Projects\ilabhr_new\public
   - Binding: http on *:80
3. Configure PHP 8.2 FastCGI:
   - IIS Manager → Handler Mappings → Add Module Mapping
   - Request path: *.php
   - Module: FastCgiModule
   - Executable: C:\xampp\php\php-cgi.exe
   - Name: PHP 8.2 (XAMPP)
4. Apply settings and recycle the app pool.
5. Browse http://localhost/check.php and verify PHP 8.2+ is reported by the page.

## Notes
- The version gate is enforced in [public/index.php](public/index.php#L8-L18). If you see a "Lower PHP version" page, IIS is still using an older PHP.
- Ensure file permissions allow IIS to read from C:\Projects\ilabhr_new\public.
