<?php
/**
 * Available Utilities Command Controller
 */

namespace Dolphin\Command;

use Dolphin\CommandController;
use Dolphin\Provider\Ansible\Group;
use Dolphin\Provider\Ansible\Host;
use Dolphin\Provider\Ansible\Inventory;
use Dolphin\Provider\DigitalOcean\Droplet;

class AvailableController extends CommandController
{
    /** @var  Inventory */
    protected $inventory;

    public function getRegions()
    {

    }

    public function getKeys()
    {

    }

    public function getSizes()
    {

    }

    public function getCommandMap()
    {
        return [
            'inventory' => 'buildInventory',
        ];
    }

    public function defaultCommand()
    {
        $this->output("Usage: ./dolphin ansible inventory", "unicorn");
        $this->getPrinter()->newline();
    }

}