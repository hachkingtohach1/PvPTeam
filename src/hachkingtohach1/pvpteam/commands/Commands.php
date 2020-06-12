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
    public $plugin;
	
	/** @var $data */
	protected $data;
	
	/** @var $config */
	public $config;

    public function __construct(Main $plugin) 
	{		
        $this->plugin = $plugin;
		$this->config = $this->plugin->configArena();
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
					"/pt join : Join arena\n".
                    "/pt list : Displays list of arenas"
				);
            break;
			case 'create':
			    if(!$sender->hasPermission('pvp.team.cmd.create')) {
                    $sender->sendMessage("You have not permissions to use this command!");
                    break;
                }
				if(!isset($args[1])) {
					$sender->sendMessage("Usage: /pt create [name]");
					break;
				}
				$object = [		                   			
				    'minslots' => 2,
					'maxslots' => 10,
					'timeend' => 5*60,
					'starttime' => 30,
					'restarttime' => 10,
					'status' => 0,
					'maxslotsperteam' => 2,
					'teams' => [],
					'points' => [],
					'players' => [],
					'spectators' => [],
                    'spawnteam' => [],
                    'spawnlobby' => null,
                    'spawnspectator' => null,					
                    'level' => null,
					'name' => $args[1],						
                    'enable' => false					
				];
				$this->data->set($args[1], $object);
				$this->data->save();
				$sender->sendMessage("Arena with name ".$args[1]." have been created!");
			break;
			case 'remove':
			    if(!$sender->hasPermission('pvp.team.cmd.remove')) {
                    $sender->sendMessage("You have not permissions to use this command!");
                    break;
                }
				if(!isset($args[1])) {
					$sender->sendMessage("Usage: /pt remove [name]");
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
				if(!isset($args[1])) {
					$sender->sendMessage("Usage: /pt setup [name]");
					break;
				}
				if(empty($this->data->get($args[1]))) {
					$sender->sendMessage("Arena with name ".$args[1]." not found!");
					break;
				}
				if(!isset($args[2])) {					
					$sender->sendMessage(
					    "/pt setup [name] level [name_level]\n".
					    "/pt setup [name] minslots [int_slots]\n".
					    "/pt setup [name] maxslots [int_slots]\n".
					    "/pt setup [name] timeend [int_time]\n".
					    "/pt setup [name] starttime [int_time]\n".
					    "/pt setup [name] restarttime [int_time]\n".
					    "/pt setup [name] teams [name]\n".
					    "/pt setup [name] setspawnlobby\n".
					    "/pt setup [name] spawnspectator\n".
						"/pt setup [name] maxslotsperteam [int_slots]\n".
						"/pt setup [name] enable [true/false]"
					);
					break;
				}
				switch($args[2]) {
					case 'level':
					    if(!isset($args[3])) {
							$sender->sendMessage("Usage: /pt setup [name] level [name_level]");
							break;
						}
					    $this->config->changeDataArena($sender, $args[1], 'level', $args[3]);
						$sender->sendMessage("SETUP: Level for arena ".$args[1]." is ".$args[3]);
					break;
					case 'minslots':
					    if(!isset($args[3])) {
							$sender->sendMessage("Usage: /pt setup [name] minslots [int_slots]");
							break;
						}
						$this->config->changeDataArena($sender, $args[1], 'minslots', $args[3]);
						$sender->sendMessage("SETUP: Minslots for arena ".$args[1]." is ".$args[3]);
					break;
					case 'maxslots':
					    if(!isset($args[3])) {
							$sender->sendMessage("Usage: /pt setup [name] maxslots [int_slots]");
							break;
						}
						$this->config->changeDataArena($sender, $args[1], 'maxslots', $args[3]);
						$sender->sendMessage("SETUP: Maxslots for arena ".$args[1]." is ".$args[3]);
					break;
					case 'timeend':
					    if(!isset($args[3])) {
							$sender->sendMessage("Usage: /pt setup [name] timeend [int_time]");
							break;
						}
						$this->config->changeDataArena($sender, $args[1], 'timeend', $args[3]);
						$sender->sendMessage("SETUP: Timeend for arena ".$args[1]." is ".$args[3]);
					break;
					case 'starttime':
					    if(!isset($args[3])) {
							$sender->sendMessage("Usage: /pt setup [name] starttime [int_time]");
							break;
						}
						$this->config->changeDataArena($sender, $args[1], 'starttime', $args[3]);
						$sender->sendMessage("SETUP: Starttime for arena ".$args[1]." is ".$args[3]);
					break;
					case 'enable':
					    if(!isset($args[3]) or !in_array($args[3], ['true', 'false'])) {
							$sender->sendMessage("Usage: /pt setup [name] enable [true/false]");
							break;
						}
						if($this->config->checkDataConfig($args[1]) === false) {
							$sender->sendMessage("SETUP: The data check seems like you didn't finish it right!");
							break;
						}
						$this->config->changeDataArena($sender, $args[1], 'starttime', $args[3]);
						$sender->sendMessage("SETUP: Starttime for arena ".$args[1]." is ".$args[3]);
					break;
					case 'restarttime':
					    if(!isset($args[3])) {
							$sender->sendMessage("Usage: /pt setup [name] restarttime [int_time]");
							break;
						}
						$this->config->changeDataArena($sender, $args[1], 'restarttime', $args[3]);
						$sender->sendMessage("SETUP: Restarttime for arena ".$args[1]." is ".$args[3]);
					break;
					case 'teams':
					    if(!isset($args[3]) or !isset($args[4])) {
							$sender->sendMessage("Usage: /pt setup [name] teams [name_team] [color_team]");
							break;
						}
						$this->config->addTeamArena($sender, $args[1], $args[3], $args[4]);
						$sender->sendMessage("SETUP: New team for arena ".$args[1]." is ".$args[3]);
						$sender->sendMessage("SETUP: Now to break one block to set spawn for team ".$args[3]);
					    $this->plugin->setup[$sender->getName()] = [$args[1], $args[4], $args[3], 0];
					break;
					case 'maxslotsperteam':
					    if(!isset($args[3])) {
							$sender->sendMessage("Usage: /pt setup [name] maxslotsperteam [int_slots]");
							break;
						}
						$this->config->changeDataArena($sender, $args[1], 'maxslotsperteam', $args[3]);
						$sender->sendMessage("SETUP: New team for arena ".$args[1]." is ".$args[3]);
					break;
					case 'setspawnt':
					    if(!isset($args[3])) {
							$sender->sendMessage("Usage: /pt setup [name] setspawnt [name]");
							break;
						}
                        if(empty($this->data->get([$args[1]]['teams'][$args[3]]))) {
							$sender->sendMessage("SETUP: Team not found!");
							break;
						}							
						$sender->sendMessage("SETUP: Now to break one block to set spawn for team ".$args[3]);
					    $this->plugin->setup[$sender->getName()] = [$args[1], $args[3], null, 0];
					break;
					case 'setspawnlobby':					
						$sender->sendMessage("SETUP: Now to break one block to set spawn lobby ");
					    $this->plugin->setup[$sender->getName()] = [$args[1], null, null, 1];
					break;
					case 'setspawnspectator':
					    $sender->sendMessage("SETUP: Now to break one block to set spawn spectator");
					    $this->plugin->setup[$sender->getName()] = [$args[1], null, null, 2];
					break;
				}
			break;
			case 'join':
			    if(!$sender->hasPermission('pvp.team.cmd.join')) {
                    $sender->sendMessage("You have not permissions to use this command!");
                    break;
                }
				if(!isset($args[1])) {
					$sender->sendMessage("Usage: /pt join [name]");
					break;
				}
				if(empty($this->data->get($args[1]))) {
					$sender->sendMessage("Arena can not found!");
					break;
				}
				$this->plugin->getArena()->onJoinArena($sender, $args[1], true);
			break;
			case 'list':
			    if(!$sender->hasPermission('pvp.team.cmd.list')) {
                    $sender->sendMessage("You have not permissions to use this command!");
                    break;
                }
			    foreach($this->plugin->getArena()->arenas as $name) {
					$status = $name['enable'];
					$sender->sendMessage("--- List Arenas ---");
					$sender->sendMessage($name['name']." | ".$status['enable']);
				}
			break;
		}
	}

    public function getPlugin(): Plugin {
        return $this->plugin;
    }
}
