<?php
/**
 * Ansible Provider
 */

namespace Dolphin\Provider;

use Dolphin\Wrapper\Ansible;

class AnsibleProvider
{
    /** @var  string $remote_user */
    protected $remote_user;

    /** @var string $inventory_path */
    protected $inventory_path;

    /** @var  string $playbooks_folder */
    protected $playbooks_folder;

    /**
     * AnsibleProvider constructor.
     * @param string $inventory_path
     * @param string $playbooks_folder
     */
    public function __construct($inventory_path, $playbooks_folder)
    {
        $this->inventory_path = $inventory_path;
        $this->playbooks_folder = $playbooks_folder;
    }

    /**
     * @return mixed
     */
    public function getRemoteUser()
    {
        return $this->remote_user;
    }

    /**
     * @param mixed $remote_user
     */
    public function setRemoteUser($remote_user)
    {
        $this->remote_user = $remote_user;
    }

    /**
     * @return string
     */
    public function getPlaybooksFolder()
    {
        return $this->playbooks_folder;
    }

    /**
     * @param string $playbooks_folder
     */
    public function setPlaybooksFolder($playbooks_folder)
    {
        $this->playbooks_folder = $playbooks_folder;
    }

    /**
     * @return mixed
     */
    public function getInventoryPath()
    {
        return $this->inventory_path;
    }

    /**
     * @param mixed $inventory_path
     */
    public function setInventoryPath($inventory_path)
    {
        $this->inventory_path = $inventory_path;
    }

    /**
     * Prints Ansible version.
     */
    public function getVersion()
    {
        Ansible::version();
    }

    /**
     * Pings a host.
     * @param $target
     */
    public function ping($target)
    {
        $connection_options = [];

        if (!empty($this->remote_user)) {
            $connection_options['ansible_user'] = $this->remote_user;
        }

        Ansible::ping($target, $this->inventory_path, $connection_options);
    }

    /**
     * Runs a playbook.
     * @param $playbook
     * @param $target
     */
    public function play($playbook, $target)
    {
        $connection_options = [];

        if (!empty($this->remote_user)) {
            $connection_options['ansible_user'] = $this->remote_user;
        }

        Ansible::play($playbook, $target, $this->inventory_path, $connection_options);
    }
}