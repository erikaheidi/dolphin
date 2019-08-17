<?php
/**
 * Deployer
 */

namespace Dolphin;

use Dolphin\Provider\AnsibleProvider;

class Deployer
{
    public static $DEFAULT_PLAYBOOK = 'default';

    /** @var  AnsibleProvider */
    protected $ansible_provider;

    /**
     * Deployer constructor.
     * @param string $inventory_path
     * @param string $playbooks_folder
     */
    public function __construct($inventory_path, $playbooks_folder)
    {
        $this->ansible_provider = new AnsibleProvider($inventory_path, $playbooks_folder);
    }

    /**
     * Sets the remote user
     * @param $user
     */
    public function setAnsibleUser($user)
    {
        $this->ansible_provider->setRemoteUser($user);
    }

    /**
     * Runs a Playbook / Deploy Script
     * @param $deploy
     * @param $system
     * @param $target
     */
    public function runDeploy($deploy, $system, $target)
    {
        if ($this->playbookExists($deploy, $system)) {
            $playbook_file = $this->getPlaybookPath($deploy, $system);
            $this->ansible_provider->play($playbook_file, $target);
        }
    }

    /**
     * Pings a host
     * @param $target
     */
    public function ping($target)
    {
        $this->ansible_provider->ping($target);
    }

    /**
     * @return array
     */
    public function getScripts()
    {
        $scripts = [];

        foreach (glob($this->getPlaybooksFolder() . '/*') as $deploy) {

            $name = basename($deploy);

            foreach (glob($deploy . '/*.yml') as $playbook) {
                $file = explode('.', basename($playbook));
                $scripts[] = ['name' => $name, 'system' => $file[0]];
            }

        }

        return $scripts;
    }

    /**
     * @param $deploy
     * @param $system
     * @return bool
     */
    public function playbookExists($deploy, $system)
    {
        return is_file($this->getPlaybookPath($deploy, $system));
    }

    /**
     * @param $deploy
     * @param $system
     * @return string
     */
    public function getPlaybookPath($deploy, $system)
    {
        return $this->getPlaybooksFolder() . '/' . $deploy . '/' . $system . '.yml';
    }

    /**
     * @return string
     */
    public function getPlaybooksFolder()
    {
        return $this->ansible_provider->getPlaybooksFolder();
    }

    /**
     * Shows the current Ansible version on the system
     */
    public function showAnsibleVersion()
    {
        $this->ansible_provider->getVersion();
    }
}