<?php
/**
 * Ansible Inventory Model
 */

namespace Dolphin\Provider\Ansible;

class Inventory
{
    /** @var Group[] Groups  */
    protected $groups = [];

    /**
     * Inventory constructor.
     * @param array $groups
     */
    public function __construct(array $groups)
    {
        foreach ($groups as $group) {
            $this->addGroup($group);
        }
    }

    /**
     * @param Group $group
     */
    public function addGroup(Group $group)
    {
        $this->groups[] = $group;
    }

    /**
     * @return array
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * @param array $groups
     */
    public function setGroups(array $groups)
    {
        foreach ($groups as $group) {
            $this->addGroup($group);
        }
    }

    public function output()
    {
        $inventory = "";
        
        /** @var Group $group */
        foreach ($this->getGroups() as $group) {
            $inventory .= $group->toInventory();
            
            /** @var Host $host */
            foreach ($group->getHosts() as $host) {
                $inventory .= $host->toInventory();
            }
        }
        
        /** Setting Python 3 for all hosts */
        $inventory .= sprintf("\n[all:vars]\nansible_python_interpreter=/usr/bin/python3\n");

        return $inventory;
    }

    public function __toString()
    {
        return $this->output();
    }
}