<?php
/**
 * Droplet Command Namespace
 *
 */

namespace Dolphin\Command;

use Dolphin\CommandController;
use Dolphin\Exception\APIException;
use Dolphin\Exception\InvalidArgumentCountException;
use Dolphin\Provider\DigitalOcean\Droplet;

class DropletController extends CommandController
{
    /**
     * Lists droplets
     * @param array $arguments
     * @throws APIException
     */
    public function listDroplets(array $arguments = [])
    {
        $this->getPrinter()->newline();

        $params = $this->parseArgs($arguments);

        $force_update = array_key_exists('force-update', $params) ? true : false;

        if ($force_update) {
            $this->getPrinter()->out("Fetching contents from API...\n");
        }

        $droplets = $this->getDolphin()->getDO()->getDroplets($force_update);

        if ($droplets === null) {
            $this->output("No Droplets found.", "error");
            exit;
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
     * Creates a new droplet
     * @param array $arguments
     * @throws \Dolphin\Exception\MissingArgumentException
     */
    public function createDroplet(array $arguments)
    {
        $params = $this->parseArgs($arguments);

        if (!isset($params['name'])) {
            $this->getPrinter()->out("You must provide a droplet name in the following format: name=MyDropletName.", "error_alt");
            $this->getPrinter()->newline();
            exit;
        }

        $this->getPrinter()->newline();
        $this->getPrinter()->out("Creating new Droplet...", 'info_alt');
        $this->getPrinter()->newline();

        try {
            $response = $this->getDolphin()->getDO()->createDroplet($params);
            $this->getPrinter()->out(
                sprintf("Your new droplet %s was successfully created. Here's some info:", $params['name']),
                'success_alt'
            );

            $response_body = json_decode($response['body'], true);
            $droplet = new Droplet($response_body['droplet']);

            $table[] = [ 'id', 'name', 'region', 'size', 'image', 'created at' ];
            $table[] = [
                $droplet->id,
                $droplet->name,
                $droplet->region['slug'],
                $droplet->size_slug,
                $droplet->image['slug'],
                $droplet->created_at,
            ];

            $this->getPrinter()->newline();
            $this->getPrinter()->printTable($table);

        } catch (APIException $e) {
            $this->getPrinter()->out("An API error has ocurred.", "error_alt");
        }

        $this->getPrinter()->newline();
    }

    /**
     * Deletes a Droplet.
     * @param array $arguments
     * @throws APIException
     */
    public function destroyDroplet(array $arguments)
    {
        $droplet_id = $arguments[0];
        
        $this->getPrinter()->newline();
        $this->getPrinter()->out(sprintf("Destroying Droplet ID %s ...", $droplet_id), 'info_alt');
        $this->getPrinter()->newline();

        if ($this->getDolphin()->getDO()->destroyDroplet($droplet_id)) {
            $this->getPrinter()->out("Droplet successfully destroyed.\n\n", 'info');
        }
    }

    /**
     * @return array
     */
    public function getCommandMap()
    {
        return [
            'list'   => 'listDroplets',
            'create' => 'createDroplet',
            'destroy' => 'destroyDroplet'
        ];
    }

    /**
     * Default command to be executed when no extra arguments are passed.
     */
    public function defaultCommand()
    {
        $this->output("Usage: ./dolphin droplet [list|create|destroy]", "unicorn");
        $this->getPrinter()->newline();
    }
}