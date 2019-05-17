<?php
/**
 * Ansible Command Controller
 */

namespace Dolphin\Command;

use Dolphin\CommandController;
use Dolphin\Provider\Ansible\Group;
use Dolphin\Provider\Ansible\Host;
use Dolphin\Provider\Ansible\Inventory;
use Dolphin\Provider\DigitalOcean\Droplet;

class AnsibleController extends CommandController
{
    /** @var  Inventory */
    protected $inventory;

    public function buildInventory()
    {
        $droplets = $this->dolphin->getDO()->getDroplets();

        if ($droplets !== null) {

            $hosts = [];
            foreach ($droplets as $droplet_info) {
                $droplet = new Droplet($droplet_info);
                
                $hosts[] = new Host($droplet->name, $droplet->networks['v4'][0]['ip_address'], $droplet->tags);
            }

            $groups[] = new Group($this->getConfig('DEFAULT_SERVER_GROUP'), $hosts);

            $this->inventory = new Inventory($groups);

            $this->output($this->inventory);
        }
    }

    public function getCommandMap()
    {
        return [
            'inventory' => 'buildInventory',
        ];
    }

    public function printHelp()
    {
        echo "Ansible controller";
    }

}