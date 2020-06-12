<?php

declare(strict_types=1);

namespace hachkingtohach1\pvpteam\arena;

use hachkingtohach1\pvpteam\Main;
use hachkingtohach1\pvpteam\math\Vector3;
use hachkingtohach1\pvpteam\task\ArenaScheduler;
use pocketmine\Player;
use pocketmine\level\Level;
use pocketmine\level\Position;

class Arena {
	
	/** @var $plugin */
	public $plugin;
	
	/** @var string*/
	public $namearena;
	
	/** @var $getdata*/
	public $getdata;
	
	/** @var $scheduler */
	public $scheduler;
	
	/** @var array */
	public $arenas = [];
	
	/** @var array */
	public $data = [];
	
	public $players = [];
	
	/** @var bool $loaded */
	public $loaded = false;
	
	/** @var CONST */
	public const WAITING = 0, PLAYING = 1, RESTARTING = 2;
	
    public function __construct(Main $plugin, string $namearena, array $data, bool $load = \false) 
	{
		$this->plugin = $plugin;
		$this->getdata = $this->plugin->getArenasData;
		if(!$load) 
		{		
		    $this->arenas[$namearena] = $this->getdata->get($namearena);
			$this->LoadLevelArena();
			$this->loaded = true;
		}
		$this->plugin->getScheduler()->scheduleRepeatingTask
		($this->scheduler = new ArenaScheduler($this), 20);
    }		

    public function LoadLevelArena() 
	{
		foreach($this->arenas as $name) {					
			
			$world = $name['level'];
			$name = $name['name'];
			if($world === null) return;
		    if(!$this->plugin->getServer()->isLevelGenerated($world)) 
			{
				$this->plugin->getLogger()->warning($name.' can not load level');
                return;
			}
		    if(!$this->plugin->getServer()->isLevelLoaded($world)) 
			{
                $this->plugin->getServer()->loadLevel($world);
			}
		}
	}		
	
	public function onJoinArena(Player $player, string $name, bool $spectator = \false) 
	{
		$namep = $player->getName();
	    $arena = $this->arenas[$name];
			
		if($arena['name'] == $name) 
		{
			
		    if(
		        count($this->arenas[$arena['name']]['players'])
		        == 
			    $this->arenas[$arena['name']]['maxslots']
		    ) {
			    $player->sendMessage("Arena is full!");
		    }
			
            if(!$spectator) 
		    {
			    $spectator = $this->arenas[$arena['name']]['spectators'];
			    $spectator[$player->getName()] = $player;
			    $player->teleport(
				    Position::fromObject(
					    Vector3::fromString($this->arenas[$arena['name']]['spawnspectator'])
					    ->add(0.5, 0, 0.5), 
					    $this->getLevel($this->arenas[$arena['name']]['level'])
				    )
			    );
			    return;
		    }	

			$this->arenas[$arena['name']]['players'][$player->getName()] = $player;			
		
		    foreach($this->arenas[$arena['name']]['teams'] as $team) 
		    {			
			    $players = $team['players'];
			
			    if(
			        count($players) == 0
			    ) {						
				    $players[$namep] = $player; 				    
			    }
			    if(
			        count($players) == count($players) 
				    && count($players) != $this->arenas[$arena['name']]['maxslotsperteam']
			    ) {				
				    $players[$namep] = $player;				    
			    }  
                $player->teleport(
				    Position::fromObject(
					    Vector3::fromString($this->arenas[$arena['name']]['spawnlobby'])
					    ->add(0.5, 0, 0.5), 
					    $this->getLevel($this->arenas[$arena['name']]['level'])						
				    )
			    );
                $player->sendMessage("You are joining the game!");				
			    $this->sendBroadcastMsg($name, "{$player->getName()} has join the game!");				
			}
		}		
	}
	
	public function onLeaveArena(Player $player, bool $spectator = \false) 
	{
		$namep = $player->getName();			        
		
		foreach($this->arenas as $arena) 
		{	
            if(!$spectator) 
			{
			    $spectator = $this->arenas[$arena['name']]['spectators'];
			    unset($spectator[$player->getName()]);
				$player->sendMessage("You are left the game!");
			    return;
			}
			
            unset($this->arenas[$arena['name']]['players'][$namep]);
			
		    foreach($this->arenas[$arena['name']]['teams'] as $team) 
			{	      			
			    $data = $this->arenas[$arena['name']]['teams'];
			    $players = $data[$team]['players'];
			
			    if(!empty($players[$namep])) 
				{
				    unset($players[$namep]);
				    $player->sendMessage("You are lefting the game!");
					$this->sendBroadcastMsg($name, "{$player->getName()} has left the game!");
				}				
		    }
		}
	}
	
	public function startTheGame(string $name) 
	{		
	    $this->arenas[$name]['status'] = self::PLAYING;
		
	    foreach($this->arenas[$name]['teams'] as $team)
		{					
			$players = $team['players'];
			
		    foreach($players as $player) 
			{				
				$this->playersGame($name);
				
			    $player->teleport(
					Position::fromObject(
						Vector3::fromString($this->arenas[$name]['spawnteam'][$team['Color']])
						->add(0.5, 0, 0.5), 
					    $this->getLevel($this->arenas[$name]['level'])
					)
				);	
			}
		}	       		
	}
	
	public function gameOver(string $name) 
	{		
		$this->sendBroadcastMsg($name, "Game Over!");
		$this->playersGame($name);
		$this->arenas[$name]['status'] = self::RESTARTING;			
	}

    public function inGame(Player $player) : bool
	{
		$namep = $player->getName();
		
        if(!isset($this->player[$namep])) {
			return false;	
		}
		
		foreach($this->arenas as $arena) 
		{					
			$players = $this->arenas[$arena['name']]['players'];
			if(!empty($players[$namep])) 
			{
				return true;
			} 				
		}
		return false;
	}
	
	public function reloadDataArena(string $name) 
	{
        $this->arenas[$name] = $this->getdata->get($name);
	}

    public function countPlayers() : int
	{
		foreach($this->arenas as $arena) 
		{			
		    $players = $this->arenas[$arena['name']]['players'];
            return count($players);			
		}		
	}		

	public function sendBroadcastMsg(string $name, string $text) 
	{
		$players = $this->arenas[$name]['players'];
		foreach($players as $player) 
		{
			$player->sendMessage($text);
		}
	}
	
	public function sendBroadcastPopup(string $name, string $text) 
	{
		$players = $this->arenas[$name]['players'];
		foreach($players as $player) 
		{
			$player->sendPopup($text);
		}
	}
	
	public function playersGame(string $name) 
	{
		foreach($this->arenas[$name]['players'] as $player) {	
            switch($arena->getAllStatus()) 
			{
				case self::WAITING:
				    $player->setGamemode($player::ADVENTURE);
				break;
				case self::PLAYING:
				    $player->setGamemode($this->plugin->getServer()->getDefaultGamemode());
                break;				
			}				                
            $player->setHealth((int)20);
            $player->setFood((int)20);
			$player->removeAllEffects();
            $player->getInventory()->clearAll();
            $player->getArmorInventory()->clearAll();
            $player->getCursorInventory()->clearAll();
		}
	}
	
	public function getLevel(string $name) 
	{ 
	    return $this->plugin->getServer()->getLevelByName($name);
	}
	
	public function __destruct() 
	{
        unset($this->scheduler);
    }
}
	

	