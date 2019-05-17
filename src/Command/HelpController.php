<?php
/**
 * Dolphin Help
 */

namespace Dolphin\Command;

use Dolphin\CommandController;

class HelpController extends CommandController
{

    public function defaultCommand()
    {
        $this->getPrinter()->printUsage();
        $this->printCheatSheet();
    }

    /**
     * Print commands usage
     */
    public function printCheatSheet()
    {
        $printer = $this->getPrinter();
        
        $help_text = "\n";
        $help_text .= $printer->format("Commands & Sub-commands", 'info');

        $registered_commands = $this->getDolphin()->getCommandRegistry()->getRegisteredCommands();

        foreach ($registered_commands as $namespace => $commands) {
            $help_text .= $this->getPrinter()->format("$namespace", "unicorn_alt");

            foreach ($commands as $command => $callback) {
                $help_text .= $this->getPrinter()->format(" - $command", "default");
            }

            $help_text .= "\n";
        }

        echo $help_text;
    }

}