<?php

namespace App\Providers;

use App\Services\Line\Client;
use Illuminate\Support\ServiceProvider;
use LINE\LINEBot;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;

class LineServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(LINEBot::class, function ($app) {
            $access_token = config('line.channel_access_token');
            $secret = config('line.channel_secret');
            $curl_client = new CurlHTTPClient($access_token);
            return new LINEBot($curl_client, ['channelSecret' => $secret]);
        });

        $this->app->singleton(Client::class, function ($app) {
            return new Client($app->make(LINEBot::class));
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
