<?php
/**
 * Droplet Command Namespace
 *
 */

namespace Dolphin\Command;

use Dolphin\DigitalOcean\Droplet;
use Dolphin\Dolphin;

class DropletCommand extends CommandController
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

    /**
     * @return array
     */
    public function getCommandMap()
    {
        return [
            'list' => 'listDroplets',
        ];
    }

}