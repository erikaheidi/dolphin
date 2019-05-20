<?php
/**
 * Default Configuration options
 */

return $dolphin_config = [


    // DigitalOcean API Token
    'DO_API_TOKEN' => 'YOUR_DIGITALOCEAN_API_TOKEN',

    //Default Ansible server group
    'DEFAULT_SERVER_GROUP' => 'servers',

    //Cache location relative to doc root */
    'CACHE_DIR' => 'cache',

    //Cache expiry time in minutes
    'CACHE_EXPIRY' => 60,

    //Default Droplet Settings
    'D_REGION' => 'nyc3',
    'D_IMAGE'  => 'ubuntu-18-04-x64',
    'D_SIZE'   => 's-1vcpu-1gb',
    'D_TAGS'   => [ 'dolphin' ],

];