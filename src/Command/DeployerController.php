<?php
/**
 * Dolphin Help
 */

namespace Dolphin\Command;

use Dolphin\Core\CommandController;

class DeployerController extends CommandController
{

    /**
     * dolphin deployer list
     */
    public function listScripts()
    {
        $this->getPrinter()->info("Deploy Scripts Currently Available");
        $deployer = $this->getDolphin()->getDeployer();
        $scripts = $deployer->getScripts();

        foreach ($scripts as $deploy) {
            $this->getPrinter()->newline();
            $this->getPrinter()->out($deploy['name'], "info");
            $this->getPrinter()->out(' for ', "default");
            $this->getPrinter()->out($deploy['system'], "info");
        }

        $this->getPrinter()->newline();
        $this->getPrinter()->newline();

        $this->getPrinter()->out("You can run a script with: ./dolphin deployer run [script] on [droplet-name]");
        $this->getPrinter()->newline();
    }


    /**
     * dolphin deployer ping droplet-name
     * @param array $arguments
     */
    public function ping(array $arguments)
    {
        $droplet = $arguments[0];

        if (!$droplet) {
            $this->getPrinter()->error("You must provide the droplet name or IP address.");
            exit;
        }

        $deployer = $this->getDolphin()->getDeployer();

        $params = $this->parseArgs($arguments);
        if (isset($params['user'])) {
            $deployer->setAnsibleUser($params['user']);
        }

        $deployer->ping($droplet);
    }

    /**
     * dolphin deployer run lemp on droplet-name
     * @param array $arguments
     */
    public function runDeploy(array $arguments)
    {
        $deployer = $this->getDolphin()->getDeployer();

        $deploy = $arguments[0];
        $system = "ubuntu1804";
        $target = $arguments[2];

        if (!$deployer->playbookExists($deploy, $system)) {
            $this->getPrinter()->error('The specified deploy is invalid or not available for the requested system.');
        }

        $params = $this->parseArgs($arguments);
        if (isset($params['user'])) {
            $deployer->setAnsibleUser($params['user']);
        }

        $deployer->runDeploy($deploy, $system, $target);
    }

    public function getCommandMap()
    {
        return [
            'info' => 'infoCommand',
            'list' => 'listScripts',
            'run'  => 'runDeploy',
            'ping' => 'ping',
        ];
    }

    public function infoCommand()
    {
        $deployer = $this->getDolphin()->getDeployer();
        
        $this->getPrinter()->info('Ansible Version:');
        $deployer->showAnsibleVersion();
        
        $this->getPrinter()->info('Playbooks Dir:');
        $this->getPrinter()->out($deployer->getPlaybooksFolder(), 'info_alt');
        $this->getPrinter()->newline();
    }

    public function defaultCommand()
    {
        $this->infoCommand();
    }
}