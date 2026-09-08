<?php
use Illuminate\Support\Facades\Schedule;

// Revisa fragancias con stock bajo o agotado y notifica a los admins.
// Corre todos los días a las 8:00 am (hora del servidor, ver APP_TIMEZONE en .env).
Schedule::command('inventario:verificar-stock-bajo')->dailyAt('08:00');

