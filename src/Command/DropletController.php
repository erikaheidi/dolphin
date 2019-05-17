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
        $droplets = $this->getDolphin()->getDO()->getDroplets();

        if ($droplets === null) {
            $this->output("No Droplets found.", "error");
        }

        $print_table[] = [ 'ID', 'NAME', 'IP', 'REGION', 'SIZE'];
        
        foreach ($droplets as $droplet_info) {
            $droplet = new Droplet($droplet_info);
            $print_table[] = [
                $droplet->id,
                $droplet->name,
                $droplet->networks['v4'][0]['ip_address'],
                $droplet->region['slug'],
                $droplet->size_slug,
            ];
        }

        $this->getPrinter()->newline();
        $this->getPrinter()->printTable($print_table);
        $this->getPrinter()->newline();
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

    public function defaultCommand()
    {
        $this->output('./dolphin droplet [ list | listIp ]', "info");
    }

}