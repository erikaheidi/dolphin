<?php
/**
 * Droplet Command Namespace
 *
 */

namespace Dolphin\Command;

use Dolphin\CommandController;
use Dolphin\Provider\DigitalOcean\Droplet;

class DropletController extends CommandController
{
    
    public function listDroplets(array $arguments = [])
    {
        $droplets = $this->getDolphin()->getDroplets();

        if ($droplets === null) {
            $this->output("No Droplets found.", "message");
        }
        
        foreach ($droplets as $droplet_info) {
            $droplet = new Droplet($droplet_info);
            $this->output($droplet->name);
        }
    }

    public function listDropletIPs()
    {
        echo "JUST TESTING";
    }

    /**
     * @return array
     */
    public function getCommandMap()
    {
        return [
            'list' => 'listDroplets',
            'listIps' => 'listDropletIPs'
        ];
    }

    public function printHelp()
    {
        echo "Droplet controller";
    }

}