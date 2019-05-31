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

    // Optional - SSH key(s) to be added in new droplets. Uncomment and add your own key(s).
    // NOTICE: You should use IDs or fingerprints as obtained from the DO API or from the web panel.
    #'D_SSH_KEYS' => [
    #    'KEY_FINGERPRINT_OR_ID'
    #],

];