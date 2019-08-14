<?php
/**
 * Available Utilities Command Controller
 */

namespace Dolphin\Command;

use Dolphin\Core\CommandController;
use Dolphin\Model\Ansible\Inventory;
use Dolphin\Model\DigitalOcean\Image;
use Dolphin\Model\DigitalOcean\Key;
use Dolphin\Model\DigitalOcean\Region;
use Dolphin\Model\DigitalOcean\Size;

class FetchController extends CommandController
{
    /** @var  Inventory */
    protected $inventory;

    /**
     * Gets available Droplet regions
     * @throws \Dolphin\Exception\APIException
     */
    public function getRegions()
    {
        $force_update = $this->flagExists('--force-update') ? 1 : 0;

        if ($this->flagExists('--force-cache')) {
            $force_update = -1;
        }

        $regions = $this->getDolphin()->getDO()->getRegions($force_update);

        if ($regions === null) {
            $this->getPrinter()->error("No Regions found.");
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

        $this->getPrinter()->printTable($print_table);
    }

    /**
     * Gets available Droplet Sizes
     * @throws \Dolphin\Exception\APIException
     */
    public function getSizes()
    {
        $force_update = $this->flagExists('--force-update') ? 1 : 0;

        if ($this->flagExists('--force-cache')) {
            $force_update = -1;
        }

        $sizes = $this->getDolphin()->getDO()->getSizes($force_update);

        if ($sizes === null) {
            $this->getPrinter()->error("No Sizes found.");
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

        $this->getPrinter()->printTable($print_table);
    }
    
    /**
     * Gets available distro images
     * @throws \Dolphin\Exception\APIException
     */
    public function getImages()
    {
        $force_update = $this->flagExists('--force-update') ? 1 : 0;

        if ($this->flagExists('--force-cache')) {
            $force_update = -1;
        }

        $images = $this->getDolphin()->getDO()->getImages($force_update);

        if ($images === null) {
            $this->getPrinter()->error("No Images found.");
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

        $this->getPrinter()->printTable($print_table);
    }

    public function getKeys()
    {
        $force_update = $this->flagExists('--force-update') ? 1 : 0;

        if ($this->flagExists('--force-cache')) {
            $force_update = -1;
        }

        $keys = $this->getDolphin()->getDO()->getKeys($force_update);

        if ($keys === null) {
            $this->getPrinter()->error("No SSH Keys found.");
            exit;
        }

        $print_table[] = [ 'ID', 'NAME', 'FINGERPRINT' ];

        foreach ($keys as $key_info) {
            $key = new Key($key_info);
            $print_table[] = [
                $key->id,
                $key->name,
                $key->fingerprint,
            ];
        }

        $this->getPrinter()->printTable($print_table);
    }

    public function getCommandMap()
    {
        return [
            'images'  => 'getImages',
            'regions' => 'getRegions',
            'sizes'   => 'getSizes',
            'keys'    => 'getKeys',
        ];
    }

    public function defaultCommand()
    {
        $this->output("Usage: ./dolphin ansible inventory", "info");
        $this->getPrinter()->newline();
    }

}