<?php

use App\Helpers\DateHelper;
use App\Jobs\DeleteOldWeatherImagesJob;
use Illuminate\Support\Facades\Schedule;
use App\Jobs\FetchGtsMessagesJob;
use App\Jobs\FetchGtsMonitoringDataJob;
use App\Jobs\FetchMonitoringSinoptikJob;
use App\Jobs\FetchWeatherImageJob;
use App\Jobs\SendDataAwsToServerSinoptikJob;

Schedule::command('queue:prune-batches')->daily();

// Fetching data
$fetchSinoptikMinute = setting('excel_server_sinoptik_creation_minute', 52);
Schedule::command('sinoptik:fetch-data')
    ->hourlyAt($fetchSinoptikMinute)
    ->runInBackground();

if (setting('bmkgsabang_awsmaritim_fetch_data', true)) {
    Schedule::command('awsmaritim:fetch-data')
        ->everyMinute()
        ->runInBackground();
}

if (setting('bmkgsabang_sinoptik_fetch_screenshot', true)) {
    Schedule::command('sinoptik:fetch-screenshot')
        ->everyMinute()
        ->runInBackground();
}

// =========================================================================
// 1. GENERATE GTS MESSAGE (Dimajukan ke Menit 58 agar Siap Sebelum Menit 00)
// =========================================================================
if (setting('bmkgsabang_generate_sinoptik', true)) {
    // Default offset dimajukan 2 menit (menit 58)
    $sinoptikOffset = setting('bmkgsatu_sinoptik_creation_mode_offset', -2);
    $sinoptikMinute = (60 + $sinoptikOffset) % 60;

    Schedule::command('gts:generate-sinoptik')
        ->hourlyAt($sinoptikMinute)
        ->runInBackground();
}

if (setting('bmkgsabang_generate_metar', true)) {
    // Default offset dimajukan 2 menit (menit 58)
    $metarOffset = setting('bmkgsatu_metar_creation_mode_offset', -2);
    $metarMinute = (60 + $metarOffset) % 60;

    Schedule::command('gts:generate-metar')
        ->hourlyAt($metarMinute)
        ->runInBackground();
}

if (setting('bmkgsabang_generate_wxrev', true)) {
    $wxrevOffset = setting('bmkgsatu_wxrev_creation_mode_offset', 0);
    $wxrevMinute = (60 + $wxrevOffset) % 60;

    Schedule::command('gts:generate-wxrev')
        ->dailyAt("23:$wxrevMinute")
        ->runInBackground();
}

if (setting('bmkgsabang_generate_dch', true)) {
    $dchOffset = setting('bmkgsatu_dch_creation_mode_offset', 0);
    $dchMinute = (60 + $dchOffset) % 60;

    Schedule::command('gts:generate-dch')
        ->dailyAt("23:$dchMinute")
        ->runInBackground();
}

// =========================================================================
// 2. PENGIRIMAN DATA / SANDI (Diubah Tepat Menit 00)
// =========================================================================

// Kirim Form Meteorologi Sinoptik TEPAT jam 00 (menit ke-0)
if (setting('bmkgsabang_send_meteorologi_sinoptik', true)) {
    Schedule::command('sinoptik:send-meteorologi-sinoptik')
        ->hourlyAt(0)
        ->runInBackground();
}

// Send GTS Message automatically on-time
// Send GTS Message automatically on-time
// Send GTS Message automatically on-time
if (setting('bmkgsabang_gts_send_pending_messages', true)) {
    Schedule::command('gts:send-pending-messages')
        ->cron('0-10 * * * *')
        ->runInBackground();
}

// =========================================================================
// MONITORING & JOB LAINNYA
// =========================================================================

// Fetch monitoring data from bmkgsatu
Schedule::command('gts:fetch-gts-messages')
    ->hourlyAt(1)
    ->runInBackground();

# With whatsapp notification
Schedule::job(new FetchGtsMessagesJob(1, DateHelper::getCurrentHourIsoTimestamp(), DateHelper::getCurrentHourIsoTimestamp()))
    ->hourlyAt(5);

Schedule::job(new FetchGtsMessagesJob(4, DateHelper::getCurrentHourIsoTimestamp(), DateHelper::getCurrentHourIsoTimestamp()))
    ->hourlyAt(5);

Schedule::job(new FetchGtsMessagesJob(6, DateHelper::getCurrentHourIsoTimestamp(), DateHelper::getCurrentHourIsoTimestamp()))
    ->hourlyAt(5);

# Form sinoptik
Schedule::job(new FetchMonitoringSinoptikJob())
    ->hourlyAt(10);

Schedule::job(new FetchGtsMessagesJob(8))
    ->dailyAt('00:05');

Schedule::job(new FetchGtsMessagesJob(53))
    ->dailyAt('00:05');

# Download monitoring gts data of Sinoptik, metar, and TAF
Schedule::job(new FetchGtsMonitoringDataJob(1))
    ->hourlyAt(15);

Schedule::job(new FetchGtsMonitoringDataJob(4))
    ->hourlyAt(15);

Schedule::job(new FetchGtsMonitoringDataJob(6))
    ->hourlyAt(15);

// Download images of radar, satellite, etc
Schedule::job(new FetchWeatherImageJob('https://inderaja.bmkg.go.id/IMAGE/HIMA/H08_EH_Aceh.png', 'satelit'))
    ->everyTwoMinutes();

Schedule::job(new FetchWeatherImageJob('https://inderaja.bmkg.go.id/IMAGE/HIMA/H08_EH_Indonesia.png', 'himawari'))
    ->everyTwoMinutes();

Schedule::job(new FetchWeatherImageJob('https://radar.bmkg.go.id/IMAGE/CMAX_Aceh.png', 'radar'))
    ->everyTwoMinutes();

Schedule::job(new FetchWeatherImageJob('https://dataweb.bmkg.go.id/cuaca/angin/streamline-d10.jpg', 'streamline'))
    ->twiceDailyAt(3, 4);

Schedule::job(new FetchWeatherImageJob('https://dataweb.bmkg.go.id/maritim/gelombang-maritim.png', 'seawave'))
    ->twiceDailyAt(7, 8);

Schedule::job(new FetchWeatherImageJob('https://inderaja.bmkg.go.id/IMAGE/HCAI/CLC/HCAI_CLC_Indonesia.png', 'cloud'))
    ->everyTwoMinutes();

// Resync with excel data
Schedule::command('sinoptik:fetch-data')
    ->hourlyAt(45)
    ->runInBackground();

// Fill excel at server sinoptik, use default minute 50
Schedule::job(new SendDataAwsToServerSinoptikJob(DateHelper::getNearestSentHour()->toDateTimeString()))
    ->hourlyAt(51);

// Delete old satellite images at 19 UTC
Schedule::job(new DeleteOldWeatherImagesJob(['satelit', 'radar', 'himawari', 'streamline', 'cloud', 'seawave']))
    ->dailyAt('19:05');

// Backup database
Schedule::command('backup:clean')
    ->dailyAt('19:00');

Schedule::command('backup:run')
    ->dailyAt('19:30');
