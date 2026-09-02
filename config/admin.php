<?php

/*
 * Boshqaruv paneli (/admin) uchun kirish ma'lumotlari — .env dan olinadi.
 * Parol o'zgartirilsa, keyingi kirishda avtomatik yangilanadi.
 */
return [
    'login' => env('ADMIN_LOGIN', 'admin'),
    'password' => env('ADMIN_PASSWORD'),
    'name' => env('ADMIN_NAME', 'Administrator'),
];
