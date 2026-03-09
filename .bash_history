php artisan make:model Company -ms
exit
php artisan make:controller Auth/AuthController
exit
php artisan make:controller Auth/AuthController
php artisan make:controller Admin/AccountController
php artisan make:controller FacultyStaff/LecturerController
php artisan make:controller FacultyStaff/PlanController
php artisan make:controller FacultyStaff/CouncilController
php artisan make:middleware RoleMiddleware
php artisan make:request Auth/LoginRequest
php artisan make:request Auth/ForgotPasswordRequest
php artisan make:request Auth/VerifyOtpRequest
php artisan make:request Auth/ResetPasswordRequest
php artisan make:request Auth/ChangePasswordRequest
php artisan make:request Admin/AccountRequest
php artisan make:request FacultyStaff/LecturerRequest
php artisan make:request FacultyStaff/PlanRequest
php artisan make:request FacultyStaff/CouncilRequest
php artisan make:resource UserResource
php artisan make:resource LecturerResource
php artisan make:resource PlanResource
php artisan make:resource CouncilResource
sstouch routes/auth.php routes/admin.php routes/faculty.php
cd /var/www
php artisan make:controller Auth/AuthController
php artisan make:controller Admin/AccountController
php artisan make:controller FacultyStaff/LecturerController
php artisan make:controller FacultyStaff/PlanController
php artisan make:controller FacultyStaff/CouncilController
php artisan make:middleware RoleMiddleware
php artisan make:request Auth/LoginRequest
php artisan make:request Auth/ForgotPasswordRequest
php artisan make:request Auth/VerifyOtpRequest
php artisan make:request Auth/ResetPasswordRequest
php artisan make:request Auth/ChangePasswordRequest
php artisan make:request Admin/AccountRequest
php artisan make:request FacultyStaff/LecturerRequest
php artisan make:request FacultyStaff/PlanRequest
php artisan make:request FacultyStaff/CouncilRequest
php artisan make:resource UserResource
php artisan make:resource LecturerResource
php artisan make:resource PlanResource
php artisan make:resource CouncilResource
touch routes/auth.php routes/admin.php routes/faculty.phpphp artisan make:controller Auth/AuthController
php artisan make:controller Admin/AccountController
php artisan make:controller FacultyStaff/LecturerController
php artisan make:controller FacultyStaff/PlanController
php artisan make:controller FacultyStaff/CouncilController
php artisan make:middleware RoleMiddleware
php artisan make:request Auth/LoginRequest
php artisan make:request Auth/ForgotPasswordRequest
php artisan make:request Auth/VerifyOtpRequest
php artisan make:request Auth/ResetPasswordRequest
php artisan make:request Auth/ChangePasswordRequest
php artisan make:request Admin/AccountRequest
php artisan make:request FacultyStaff/LecturerRequest
php artisan make:request FacultyStaff/PlanRequest
php artisan make:request FacultyStaff/CouncilRequest
php artisan make:resource UserResource
php artisan make:resource LecturerResource
php artisan make:resource PlanResource
php artisan make:resource CouncilResource
touch routes/auth.php routes/admin.php routes/faculty.phpd
cd /var/www
php artisan make:controller Auth/AuthController
ls /var/www
ls /var/www/app/Http/Controllers/
mkdir -p /var/www/app/Http/Controllers/Auth
mkdir -p /var/www/app/Http/Controllers/Admin
mkdir -p /var/www/app/Http/Controllers/FacultyStaff
mkdir -p /var/www/app/Http/Requests/Auth
mkdir -p /var/www/app/Http/Requests/Admin
mkdir -p /var/www/app/Http/Requests/FacultyStaff
mkdir -p /var/www/app/Http/Resourcesdocker exec -u root -w /var/www backend-api bash -c "
mkdir -p app/Http/Controllers/Auth &&
mkdir -p app/Http/Controllers/Admin &&
mkdir -p app/Http/Controllers/FacultyStaff &&
mkdir -p app/Http/Requests/Auth &&
mkdir -p app/Http/Requests/Admin &&
mkdir -p app/Http/Requests/FacultyStaff &&
mkdir -p app/Http/Resources &&
chown -R www-data:www-data app/Http &&
echo OK
"
docker exec -u root -w /var/www backend-api bash -c "
mkdir -p app/Http/Controllers/Auth &&
mkdir -p app/Http/Controllers/Admin &&
mkdir -p app/Http/Controllers/FacultyStaff &&
mkdir -p app/Http/Requests/Auth &&
mkdir -p app/Http/Requests/Admin &&
mkdir -p app/Http/Requests/FacultyStaff &&
mkdir -p app/Http/Resources &&
chown -R www-data:www-data app/Http &&
echo OK
"
exit
