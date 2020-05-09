<?php

declare(strict_types=1);

namespace hachkingtohach1\pvpteam\commands;

use hachkingtohach1\pvpteam\Main;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\plugin\Plugin;
use pocketmine\Player;

class Commands extends Command implements PluginIdentifiableCommand {

    /** @var $plugin */
    protected $plugin;
	
	/** @var $data */
	protected $data;

    public function __construct(Main $plugin) 
	{		
        $this->plugin = $plugin;
		$this->data = $this->plugin->getArenasData;
        parent::__construct("pvpteam", "PvPTeam commands", \null, ["pt"]);
    }

   /**
    * @param CommandSender $sender
    * @param string $commandLabel
    * @param array $args
    * @return mixed or void
    */
    public function execute(CommandSender $sender, string $commandLabel, array $args) 
	{
        if(!isset($args[0]) && $sender->hasPermission('pvp.team.cmd')) 
		{
            $sender->sendMessage("Usage: /pt help");
            return;
        }

        switch ($args[0]) {
            case 'help':
                if(!$sender->hasPermission('pvp.team.cmd.help')) {
                    $sender->sendMessage("You have not permissions to use this command!");
                    break;
                }
                $sender->sendMessage(
				    "PvPTeam commands:\n" .
                    "/pt help : Displays list of commands\n".
                    "/pt create : Create arena\n".
                    "/pt remove : Remove arena\n".
                    "/pt setup : Setup arena\n".
                    "/pt list : Displays list of arenas"
				);
            break;
			case 'create':
			    if(!$sender->hasPermission('pvp.team.cmd.create')) {
                    $sender->sendMessage("You have not permissions to use this command!");
                    break;
                }
				$object = [				    
				    'minslots' => 2,
					'maxslots' => 10,
					'timeend' => 5*60,
					'starttime' => 30,
					'restarttime' => 10,
					'teams' => [],
					'players' => [],
					'spectator' => [],
                    'spawnteam' => [],
                    'spawnlobby' => null,					
                    'level' => null					
				];
				$this->data->set($args[1], $object);
				$sender->sendMessage("Arena with name ".$args[1]." have been created!");
			break;
			case 'remove':
			    if(!$sender->hasPermission('pvp.team.cmd.remove')) {
                    $sender->sendMessage("You have not permissions to use this command!");
                    break;
                }
				$this->data->remove($args[1]);
				$sender->sendMessage("Arena with name ".$args[1]." have been removed!");
			break;
			case 'setup':
			    if(!$sender->hasPermission('pvp.team.cmd.setup')) {
                    $sender->sendMessage("You have not permissions to use this command!");
                    break;
                }
				if(empty($this->data->get($args[1]))) {
					$sender->sendMessage("Arena with name ".$args[1]." not found!");
					break;
				}
				switch($args[2]) {
					case 'minslots':
					    $this->data->set($args[1]['minslots'], $args[3]);
						$sender->sendMessage("SETUP: Minslots for arena ".$args[1]." is ".$args[3]);
					break;
					case 'maxslots':
					    $this->data->set($args[1]['maxslots'], $args[3]);
						$sender->sendMessage("SETUP: Maxslots for arena ".$args[1]." is ".$args[3]);
					break;
					case 'timeend':
					    $this->data->set($args[1]['timeend'], $args[3]);
						$sender->sendMessage("SETUP: Timeend for arena ".$args[1]." is ".$args[3]);
					break;
					case 'starttime':
					    $this->data->set($args[1]['starttime'], $args[3]);
						$sender->sendMessage("SETUP: Starttime for arena ".$args[1]." is ".$args[3]);
					break;
					case 'restarttime':
					    $this->data->set($args[1]['restarttime'], $args[3]);
						$sender->sendMessage("SETUP: Restarttime for arena ".$args[1]." is ".$args[3]);
					break;
					case 'teams':
					    $this->data->set($args[1]['teams'], [$args[3]]);
						$sender->sendMessage("SETUP: New team for arena ".$args[1]." is ".$args[3]);
					break;
					case 'setspawnt':
                        if(empty($this->data->get($args[1]['teams'][$args[3]]))) {
							$sender->sendMessage("SETUP: Team not found!");
							break;
						}							
						$sender->sendMessage("SETUP: Now to break one block to set spawn for team ".$args[3]);
					    $this->plugin->setup[$player->getName()] = [$args[1], $args[3], 0];
					break;
					case 'setspawnlobby':							
						$sender->sendMessage("SETUP: Now to break one block to set spawn for team ".$args[3]);
					    $this->plugin->setup[$player->getName()] = [null, null, 1];
					break;
				}
			break;
			case 'list':
			    if(!$sender->hasPermission('pvp.team.cmd.list')) {
                    $sender->sendMessage("You have not permissions to use this command!");
                    break;
                }
			    foreach($this->plugin->arenas as $name) {
					$status = $name['where'];
					$sender->sendMessage("--- List Arenas ---");
					$sender->sendMessage($name." | ".$status);
				}
			break;
		}
	}

    public function getPlugin(): Plugin {
        return $this->plugin;
    }
}
