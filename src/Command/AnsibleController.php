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

    /**
     * Outputs inventory in INI format
     */
    public function outputInventory()
    {
        $inventory = $this->getInventory();

        if ($inventory === null) {
            $this->output("ERROR: unable to create inventory.", "error");
            $this->getPrinter()->newline();
        }

        echo $inventory->output();
        $this->getPrinter()->newline();
    }

    /**
     * Outputs inventory in JSON format
     */
    public function dynamicInventory()
    {
        $inventory = $this->getInventory();

        if ($inventory === null) {
            $this->output("ERROR: unable to create inventory.", "error");
            $this->getPrinter()->newline();
        }

        print $inventory->getJson();
        $this->getPrinter()->newline();
    }

    public function getCommandMap()
    {
        return [
            'inventory:json' => 'dynamicInventory',
            'inventory:ini'  => 'outputInventory',
            'inventory'      => 'outputInventory',
        ];
    }

    public function defaultCommand()
    {
        $this->output("Usage: ./dolphin ansible inventory", "unicorn");
        $this->getPrinter()->newline();
    }

    /**
     * @return Inventory|null
     * @throws \Dolphin\Exception\APIException
     */
    protected function getInventory()
    {
        $droplets = $this->dolphin->getDO()->getDroplets();

        if ($droplets !== null) {

            $hosts = [];
            foreach ($droplets as $droplet_info) {
                $droplet = new Droplet($droplet_info);

                $hosts[] = new Host($droplet->name, $droplet->networks['v4'][0]['ip_address'], $droplet->tags);
            }

            $groups[] = new Group($this->getConfig('DEFAULT_SERVER_GROUP'), $hosts);

            return new Inventory($groups);
        }

        return null;
    }
}