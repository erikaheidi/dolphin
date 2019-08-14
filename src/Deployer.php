<?php
/**
 * Deployer
 */

namespace Dolphin;

use Dolphin\Provider\Ansible;

class Deployer
{
    public static $DEFAULT_PLAYBOOK = 'default';

    /** @var  string remote user */
    protected $ansible_user;

    /** @var  string Inventory file path */
    protected $inventory;

    /** @var  string Playbooks folder */
    protected $playbooks_folder;

    /**
     * Deployer constructor.
     * @param string $inventory
     * @param string $playbooks_folder
     */
    public function __construct($inventory, $playbooks_folder)
    {
        $this->inventory = $inventory;
        $this->playbooks_folder = $playbooks_folder;
    }

    /**
     * @return string
     */
    public function getAnsibleUser()
    {
        return $this->ansible_user;
    }

    /**
     * @param string $ansible_user
     */
    public function setAnsibleUser($ansible_user)
    {
        $this->ansible_user = $ansible_user;
    }

    /**
     * Runs a Playbook
     * @param $deploy
     * @param $system
     * @param $target
     * @param array $connection_options
     */
    public function runDeploy($deploy, $system, $target, array $connection_options = [])
    {
        if ($this->playbookExists($deploy, $system)) {
            $playbook_file = $this->getPlaybookPath($deploy, $system);

            $options = array_merge([
                'ansible_user' => $this->getAnsibleUser(),
            ], $connection_options);

            Ansible::play($playbook_file, $target, $this->getInventory(), $options);
        }
    }

    /**
     * Pings a host
     * @param $target
     * @param array $connection_options
     */
    public function ping($target, array $connection_options = [])
    {
        $options = array_merge([
            'ansible_user' => $this->getAnsibleUser(),
        ], $connection_options);

        Ansible::ping($target,$this->getInventory(), $options);
    }

    /**
     * Gets the Inventory File Path
     * @return mixed
     */
    public function getInventory()
    {
        return $this->inventory;
    }

    /**
     * @return array
     */
    public function getScripts()
    {
        $scripts = [];

        foreach (glob($this->playbooks_folder . '/*') as $deploy) {

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
        return $this->playbooks_folder . '/' . $deploy . '/' . $system . '.yml';
    }
}