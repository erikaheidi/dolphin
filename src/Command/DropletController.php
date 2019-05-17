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

    /**
     * @return array
     */
    public function getCommandMap()
    {
        return [
            'list' => 'listDroplets',
        ];
    }

    public function defaultCommand()
    {
        $this->output("Usage: ./dolphin droplet list", "unicorn");
        $this->getPrinter()->newline();
    }

}