```
         ,gggggggggggg,                                                                    
        dP"""88""""""Y8b,               ,dPYb,             ,dPYb,                        
        Yb,  88       `8b,              IP'`Yb             IP'`Yb                        
         `"  88        `8b              I8  8I             I8  8I      gg                
             88         Y8              I8  8'             I8  8'      ""                
             88         d8   ,ggggg,    I8 dP  gg,gggg,    I8 dPgg,    gg    ,ggg,,ggg,  
             88        ,8P  dP"  "Y8ggg I8dP   I8P"  "Yb   I8dP" "8I   88   ,8" "8P" "8, 
             88       ,8P' i8'    ,8I   I8P    I8'    ,8i  I8P    I8   88   I8   8I   8I 
             88______,dP' ,d8,   ,d8'  ,d8b,_ ,I8 _  ,d8' ,d8     I8,_,88,_,dP   8I   Yb,
            888888888P"   P"Y8888P"    8P'"Y88PI8 YY88888P88P     `Y88P""Y88P'   8I   `Y8
                                               I8                                        
                                               I8                                        
                                               I8                                        
                                               I8                                        
                                               I8                                        
                                               I8                                        
```

# Dolphin

Dolphin is a simple command-line PHP application to manage DigitalOcean servers. It's a WORK IN PROGRESS.

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


### Commands
Currently available commands:

`./dolphin ansible inventory`

This will output a dynamically generated Ansible inventory to your terminal.

To save the contents to a file, run:

```
./dolphin ansible inventory > inventory
```

Then you can run Ansible with:

```
ansible-playbook -i inventory myplaybook.yml
```
