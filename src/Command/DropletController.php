<?php
/**
 * Droplet Command Namespace
 *
 */

namespace Dolphin\Command;

use Dolphin\CommandController;
use Dolphin\Exception\APIException;
use Dolphin\Provider\DigitalOcean\Droplet;

class DropletController extends CommandController
{
    /**
     * Lists droplets
     * usage: ./dolphin droplet list
     *
     * @param array $arguments
     * @throws APIException
     */
    public function listDroplets(array $arguments = [])
    {
        $this->getPrinter()->newline();

        $params = $this->parseArgs($arguments);

        $force_update = array_key_exists('--force-update', $params) ? true : false;

        if ($force_update) {
            $this->getPrinter()->out("Fetching contents from API...\n");
        }

        $droplets = $this->getDolphin()->getDO()->getDroplets($force_update);

        if ($droplets === null) {
            $this->output("No Droplets found.", "error");
            exit;
        }

        $print_table[] = [ 'ID', 'NAME', 'IMAGE', 'IP', 'REGION', 'SIZE'];
        
        foreach ($droplets as $droplet_info) {
            $droplet = new Droplet($droplet_info);
            $print_table[] = [
                $droplet->id,
                $droplet->name,
                $droplet->image['slug'],
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
     * Gets detailed information about a droplet.
     * usage: ./dolphin droplet info DROPLET_ID [force-update]
     *
     * @param array $arguments
     */
    public function infoDroplet(array $arguments)
    {
        $params = $this->parseArgs($arguments);
        $force_update = array_key_exists('--force-update', $params) ? true : false;

        $droplet_id = $arguments[0];
        if (!$droplet_id) {
            $this->getPrinter()->newline();
            $this->getPrinter()->out("Error: You must provide the droplet ID.", "error_alt");
            $this->getPrinter()->newline();
            exit;
        }

        $this->getPrinter()->newline();
        $this->getPrinter()->out(sprintf("Fetching Droplet info for ID %s...", $droplet_id), "alt");

        try {
            $droplet = $this->getDolphin()->getDO()->getDroplet($droplet_id, $force_update);

            $this->getPrinter()->newline();
            print_r($droplet);

        } catch (APIException $e) {
            $this->getPrinter()->newline();
            $this->getPrinter()->out("An API error occurred.", "error_alt");
            $this->getPrinter()->newline();
            $this->getPrinter()->out("Response Info:");
            $this->getPrinter()->newline();

            print_r($this->getDolphin()->getDO()->getLastResponse());
            exit;
        }
    }

    /**
     * Creates a new droplet using default options from config.php
     * usage: ./dolphin droplet create name=MY_DROPLET_NAME [api_param2=api_value2 api_param3=api_value3]
     *
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
        $this->getPrinter()->out("Creating new Droplet...", 'alt');
        $this->getPrinter()->newline();

        try {
            $response = $this->getDolphin()->getDO()->createDroplet($params);
            $this->getPrinter()->newline();
            $this->getPrinter()->out(
                sprintf("Your new droplet \"%s\" was successfully created. Please notice it might take a few minutes for the network to be ready.\nHere's some info:", $params['name']),
                'success'
            );
            $this->getPrinter()->newline();

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
            $this->getPrinter()->newline();
            $this->getPrinter()->out("Response Info:");
            $this->getPrinter()->newline();

            print_r($this->getDolphin()->getDO()->getLastResponse());
        }

        $this->getPrinter()->newline();
    }

    /**
     * Deletes a Droplet.
     * usage: ./dolphin destroy DROPLET_ID
     *
     * @param array $arguments
     * @throws APIException
     */
    public function destroyDroplet(array $arguments)
    {
        $droplet_id = $arguments[0];
        if (!$droplet_id) {
            $this->getPrinter()->newline();
            $this->getPrinter()->out("Error: You must provide the droplet ID.", "error_alt");
            $this->getPrinter()->newline();
            exit;
        }

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
            'list'    => 'listDroplets',
            'create'  => 'createDroplet',
            'destroy' => 'destroyDroplet',
            'info'    => 'infoDroplet',
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