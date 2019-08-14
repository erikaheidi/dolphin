<?php
/**
 * Dolphin Help
 */

namespace Dolphin\Command;

use Dolphin\Core\CommandController;

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
        
        $printer->newline();
        $printer->out("Commands & Sub-commands\n\n", 'info');

        $registered_commands = $this->getDolphin()->getCommandRegistry()->getRegisteredCommands();

        foreach ($registered_commands as $namespace => $commands) {
            $printer->out("$namespace", "info_alt");
            $printer->newline();

            foreach ($commands as $command => $callback) {
                $printer->out(" + $command", "default");
                $printer->newline();
            }

           $printer->newline();
        }
    }

}