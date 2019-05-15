# Dolphin

Dolphin is a simple command-line PHP application to dynamically generate Ansible inventories for DigitalOcean servers.

## Requirements

- PHP 7+ cli
- Composer

## Installation

### Via Git
First, clone this repository with:

```
git clone
```

Now go to Dolphin's directory and set the permissions for the executable:

```
cd dolphin
chmod +x dolphin
```

Run `composer install` to set up autoload:

```
composer install
```

## Usage

Copy the contents of `config_sample.php` to `config.php` and adjust the values accordingly:

```

return $dolphin_config = [


    // DigitalOcean API Token
    'DO_API_TOKEN' => 'YOUR_DIGITALOCEAN_API_TOKEN',

    //Default group
    'DEFAULT_SERVER_GROUP' => 'servers',

    ///Enables local cache
    'LOCAL_CACHE_ENABLED' => true,

    //Cache location relative to doc root */
    'CACHE_DIR' => 'cache',

    //Cache expiry time in minutes
    'CACHE_EXPIRY' => 60,

];
```

Now you can execute Dolphin with:

```
./dolphin
```


This will output the inventory to your terminal.

To save the contents to a file, run:

```
./dolphin > inventory
```

Then you can run Ansible with:

```
ansible-playbook -i inventory myplaybook.yml
```