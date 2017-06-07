<?php

namespace pxgamer\ETTV_Torrents\Config;

class App
{
    // App Configuration
    const APP_NAME = 'ETTV Torrents Backup';

    // Environment Configuration
    const ENV_PRODUCTION = 'production';
    const ENV_DEVELOPMENT = 'development';
    const ENV_MODE = self::ENV_DEVELOPMENT;

    // Logging Configuration
    const LOG_DIRECTORY = ROOT_PATH . 'logs';
    const LOG_ERROR = 'errors.log';

    // Torrents Configuration
    const DEFAULT_TRACKERS = [
        'udp://9.rarbg.com:2710/announce',
        'udp://tracker.publicbt.com/announce',
        'udp://open.demonii.com/1337'
    ];
    const CRON_USER = 'https://torrents.katmirrors.com/api/users/ETTV';
    const TMDB_API_KEY = '9aa9ae1007cc199dff0d82dfc8758a7f';
}