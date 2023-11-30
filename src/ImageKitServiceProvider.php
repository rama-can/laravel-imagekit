<?php

namespace RamaCan\ImageKit;

use ImageKit\ImageKit;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use RamaCan\ImageKit\ImageKitAdapter;

class ImageKitServiceProvider extends ServiceProvider
{
    public function register()
    {
        Storage::extend('imagekit', function ($app, $config) {
            $imageKit = new ImageKit(
                $config['public_key'],
                $config['private_key'],
                $config['url_endpoint']
                
            );

            $options = [ // Optional
                'purge_cache_update'    => [
                    'enabled'       => true,
                    'endpoint_url'  => 'your_endpoint_url'
                ]
            ] ;

            return new ImageKitAdapter(
              $imageKit, $config
            );
        });
    }
}