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
use Dolphin\Provider\DigitalOcean\Image;
use Dolphin\Provider\DigitalOcean\Region;
use Dolphin\Provider\DigitalOcean\Size;

class AvailableController extends CommandController
{
    /** @var  Inventory */
    protected $inventory;

    public function getRegions(array $arguments = [])
    {
        $params = $this->parseArgs($arguments);
        $force_update = array_key_exists('--force-update', $params) ? 1 : 0;

        if (array_key_exists('--cached', $params)) {
            $force_update = -1;
        }

        $regions = $this->getDolphin()->getDO()->getRegions($force_update);

        if ($regions === null) {
            $this->output("No Regions found.", "error");
            exit;
        }

        $print_table[] = [ 'NAME', 'SLUG', 'AVAILABLE'];

        foreach ($regions as $region_info) {
            $region = new Region($region_info);
            $print_table[] = [
                $region->name,
                $region->slug,
                $region->available,
            ];
        }

        $this->getPrinter()->newline();
        $this->getPrinter()->printTable($print_table);
        $this->getPrinter()->newline();       
    }

    public function getSizes(array $arguments = [])
    {
        $params = $this->parseArgs($arguments);
        $force_update = array_key_exists('--force-update', $params) ? 1 : 0;

        if (array_key_exists('--cached', $params)) {
            $force_update = -1;
        }

        $sizes = $this->getDolphin()->getDO()->getSizes($force_update);

        if ($sizes === null) {
            $this->output("No Sizes found.", "error");
            exit;
        }

        $print_table[] = [ 'SLUG', 'MEMORY', 'VCPUS', 'DISK', 'TRANSFER', 'PRICE/MONTH'];

        foreach ($sizes as $size_info) {
            $size = new Size($size_info);
            $print_table[] = [
                $size->slug,
                $size->memory . 'MB',
                $size->vcpus,
                $size->disk . 'GB',
                $size->transfer . 'TB',
                '$' .$size->price_monthly
            ];
        }

        $this->getPrinter()->newline();
        $this->getPrinter()->printTable($print_table);
        $this->getPrinter()->newline();
    }
    
    /**
     * Gets available distro images
     * @param array $arguments
     * @throws \Dolphin\Exception\APIException
     */
    public function getImages(array $arguments = [])
    {
        $params = $this->parseArgs($arguments);
        $force_update = array_key_exists('--force-update', $params) ? 1 : 0;

        if (array_key_exists('--cached', $params)) {
            $force_update = -1;
        }

        $images = $this->getDolphin()->getDO()->getImages($force_update);

        if ($images === null) {
            $this->output("No Images found.", "error");
            exit;
        }

        $print_table[] = [ 'ID', 'NAME', 'DIST', 'SLUG', 'TYPE', 'MIN_DISK_SIZE', 'VISIBILITY'];

        foreach ($images as $image_info) {
            $image = new Image($image_info);
            $print_table[] = [
                $image->id,
                $image->name,
                $image->distribution,
                $image->slug,
                $image->type,
                $image->min_disk_size ? $image->min_disk_size . 'GB' : '-',
                $image->public ? 'public' : 'private',
            ];
        }

        $this->getPrinter()->newline();
        $this->getPrinter()->printTable($print_table);
        $this->getPrinter()->newline();
    }

    public function getKeys()
    {

    }

    public function getCommandMap()
    {
        return [
            'images'  => 'getImages',
            'regions' => 'getRegions',
            'sizes'   => 'getSizes',
        ];
    }

    public function defaultCommand()
    {
        $this->output("Usage: ./dolphin ansible inventory", "unicorn");
        $this->getPrinter()->newline();
    }

}