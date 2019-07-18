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

Dolphin is a command-line PHP application that can be used as *mission control* for managing DigitalOcean droplets.
Dolphin can be integrated with Ansible setups for provisioning PHP servers.

_Note: This is a WORK IN PROGRESS._

## Requirements

- PHP (cli)
- Composer
- Curl
- Valid DigitalOcean API Key (R+W)

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

Edit the contents of `config.php` and adjust the values accordingly:

```
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
```

Now you can execute Dolphin with:

```
./dolphin [command] [sub-command] [params]
```


## Droplet Commands

The following commands can be used to manage droplets.

### Listing Droplets

```command
./dolphin droplet list
```

This will show a list with your DigitalOcean droplets (ID, name, IP, region and size).

```
ID        NAME                        IP              REGION    SIZE
140295122 ubuntu-1804-01              188.166.115.68  ams3      s-1vcpu-2gb
140295123 ubuntu-1804-02              188.166.123.245 ams3      s-1vcpu-2gb
140295124 ubuntu-1804-03              174.138.13.97   ams3      s-1vcpu-2gb
142352633 mysql-wordpress             165.22.254.246  sgp1      s-2vcpu-4gb
142807570 ubuntu-s-1vcpu-1gb-ams3-01  167.99.217.247  ams3      s-1vcpu-1gb
```


### Getting Information About a Droplet

```
./dolphin droplet info DROPLET_ID
```

Output will be a JSON will all the available information about that droplet.

### Creating a New Droplet
Uses default options from your config file, but you can override any of the API query parameters.
Parameters should be passed as `name=value` items. Only the **name** parameter is mandatory.

```
./dolphin droplet create name=MyDropletName
```

Let's say you want to use a custom region and droplet size:

```
./dolphin droplet create name=MyDropletName size=s-2vcpu-4gb region=nyc2
```

Check the [DigitalOCean API documentation](https://developers.digitalocean.com/documentation/v2/#create-a-new-droplet) for more information on all the parameters you can use.

### Destroying a Droplet
You can obtain the ID of a Droplet by running `droplet list` to list all your droplets.

```
./dolphin droplet destroy DROPLET_ID
```

## Checking for Information

To get a list of all available regions you can use when creating a new Droplet, use:

```
./dolphin available regions
```

To get a list of all available sizes you can use when creating a new Droplet, use:

```
./dolphin available sizes
```

To get a list of all registered SSH Keys you can use when creating a new Droplet, use:

```
./dolphin available keys
```

## Manipulating Cache

To optimize API querying and avoid hitting resource limits, Dolphin uses a simple file caching mechanism.

To force a cache update, include the flag `--force-update`:

```
./dolphin droplet list --force-update
```

If instead you'd like to enforce cache usage and not query for new results even if the cache timeout has been reached, you can use:

```
./dolphin droplet list --force-cache
```

## Ansible Commands

The following commands can be used to facilitate running Ansible on your droplets.

### Using the included Dynamic Inventory Script

The included `hosts.php` script works as a dynamic inventory script that can be used directly with Ansible commands.
This is the most convenient way to use Ansible with Dolphin.


```
ansible all -m ping -i hosts.php
```


### Building a static Inventory File

You can generate dynamic inventories in INI or JSON format. The inventory is dynamically built based on your current active droplets.

To generate a JSON inventory, run:

`./dolphin ansible inventory:json`

Output:

```
{
    "servers": {
        "hosts": [
            "docker02",
            "docker03",
            "docker04",
            "test1"
        ]
    },
    "all": {
        "children": [
            "ungrouped",
            "servers"
        ]
    },
    "_meta": {
        "hostvars": {
            "docker02": {
                "ansible_host": "134.209.82.17",
                "ansible_python_interpreter": "/usr/bin/python3"
            },
            "docker03": {
                "ansible_host": "134.209.205.231",
                "ansible_python_interpreter": "/usr/bin/python3"
            },
            "docker04": {
                "ansible_host": "188.166.46.60",
                "ansible_python_interpreter": "/usr/bin/python3"
            },
            "test1": {
                "ansible_host": "188.166.16.148",
                "ansible_python_interpreter": "/usr/bin/python3"
            }
        }
    }
}

```


To generate an INI inventory, run:

`./dolphin ansible inventory:ini`

Output:

```
[servers]
docker02 ansible_host=134.209.82.17
docker03 ansible_host=134.209.205.231
docker04 ansible_host=188.166.46.60
test1 ansible_host=188.166.16.148

[all:vars]
ansible_python_interpreter=/usr/bin/python3
```

If you want to use this as a static inventory file:

```
./dolphin ansible inventory > inventory
```


Then you can run Ansible with:

```
ansible-playbook -i inventory myplaybook.yml
```
