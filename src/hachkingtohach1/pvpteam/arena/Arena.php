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
	public $countslots = [];
	
	/** @var array */
	public $data = [];
	
	/** @var array */
	public $status = [];
	
	/** @var CONST */
	public const WAITING = 0, PLAYING = 1, RESTARTING = 2;
	
    public function __construct(Main $plugin, string $namearena, array $data, bool $load = \false) 
	{
		$this->plugin = $plugin;
		$this->getdata = $this->plugin->getArenasData();
		if($load == true) {
		    $this->loadDataArena($namearena, $data);
		    $this->LoadLevelArena();
		}
		$this->plugin->getScheduler()->scheduleRepeatingTask
		($this->scheduler = new ArenaScheduler($this), 20);
    }
	
   /**
	* function use to create data on array for arenas from config
	*/
	public function loadDataArena(string $namearena, array $data) 
	{		
		if(empty($this->arenas[$name])) 
		{
		    $this->arenas[$name] = $data;
		} 		
	}

    public function LoadLevelArena() 
	{
		foreach($this->arenas as $name) {
			$world = $name['level'];
		    if(!$this->plugin->getServer()->isLevelGenerated($world)) {
				$this->plugin->getLogger()->warning($name.' can not load level');
                return;
			}
		    if(!$this->plugin->getServer()->isLevelLoaded($world)) {
                $this->plugin->getServer()->loadLevel($world);
			}
		}
	}		
	
	public function onJoinArena(Player $player, string $name, bool $spectator = \false) 
	{		
		$namep = $player->getName();
		if(empty($this->arenas[$name])) 
		{
			$player->sendMessage("Arena isn't found!");
		}
		if(
		    $this->arenas[$name]['minslots'] 
		    == 
			$this->arenas[$name]['maxslots']
		) {
			$player->sendMessage("Arena is full!");
		}
		
        if($spectator === true) 
		{
			$spectator = $this->arenas[$name]['spectator'];
			$spectator[$player->getName()] = $player;
			return;
		}		
		
		$this->arenas[$name]['players'][$player->getName()] = $player;
		
		foreach($this->arenas[$name]['teams'] as $team) 
		{			
			$data = $this->arenas[$name]['teams'];
			$players = $data[$team]['players'];
			
			if(
			    count($team['players']) == 0
			) {						
				$players[$namep] = $player;
				$this->countslots[$name][$team] += 1;
				return;
			}
			if(
			    count($team['players']) == $this->countslots[$name][$team] 
				&& $this->countslots[$name][$team] != $team['maxslots']
			) {				
				$players[$namep] = $player;
				$this->countslots[$name][$team] += 1;
			}
            $player->teleport(
				Position::fromObject(
					Vector3::fromString($this->arenas[$name]['spawnlobby'])
					->add(0.5, 0, 0.5), 
					$this->arenas[$name]['level']
				)
			);			
			$this->sendBroadcastMsg($name, "{$player->getName()} has join the game!");
		}		
	}
	
	public function onLeaveArena(Player $player, bool $spectator = \false) 
	{
		$namep = $player->getName();		
		foreach($this->arenas as $name) 
		{	
            if($spectator === true) 
			{
			    $spectator = $this->arenas[$name]['spectator'];
			    unset($spectator[$player->getName()]);
				$player->sendMessage("You are left the game!");
			    return;
			}
			
            unset($name['players'][$namep]);
			
		    foreach($this->arenas[$name]['teams'] as $team) 
			{	      			
			    $data = $this->arenas[$name]['teams'];
			    $players = $data[$team]['players'];
			
			    if(!empty($players[$namep])) 
				{
				    unset($players[$namep]);
				    $player->sendMessage("You are left the game!");
					$this->sendBroadcastMsg($name, "{$player->getName()} has left the game!");
				}				
		    }
		}
	}
	
	public function startTheGame() 
	{		
		foreach($this->arenas as $name) 
		{
            if($this->arenas[$name]['starttime'] == 0) 
			{			
		        $this->sendBroadcastMsg($name, "Started!");
				$this->status[$name] = self::PLAYING;				
			}
			foreach($this->arenas[$name]['teams'] as $team)
			{
				$this->playersStartGame($name);
				$data = $this->arenas[$name]['teams'];
			    $players = $data[$team]['players'];
				foreach($this->arenas[$name]['players'] as $player) {
				    if(!empty($players[$player->getName])) 
				    {
			            $player->teleport(
					        Position::fromObject(
						        Vector3::fromString($this->arenas[$name]['spawnteam'][$team])
								->add(0.5, 0, 0.5), 
							    $this->arenas[$name]['level']
						    )
					    );
				    }
				}
			}
		}		
	}
	
	public function gameOver(string $name) 
	{		
		$this->sendBroadcastMsg($name, "Game Over!");
		$this->playersGameOver($name);
	    $this->status[$name] = self::RESTARTING;	
	}

    public function inGame(Player $player) : bool
	{
		$namep = $player->getName();		
		foreach($this->arenas as $name) 
		{			
		    foreach($this->arenas[$name]['teams'] as $team) 
			{			
			    $data = $this->arenas[$name]['teams'];
			    $players = $data[$team]['players'];
				if(!empty($players[$namep])) 
				{
					return false;
				} 
				return true;
			}
		}
	}
	
	public function reloadDataArena(string $name) 
	{
        $this->arenas[$name] = $this->getData->get($name);
	}

    public function countPlayers() : int
	{
		foreach($this->arenas as $name) 
		{			
		    $players = $this->arenas[$name]['players'];
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
	
	public function getAllStatus() 
	{
		foreach($this->arenas as $name) 
		{
			return $this->status[$name];
		}
	}
	
	public function playersStartGame(string $name) 
	{
		foreach($this->arenas[$name]['players'] as $player) {		    
            $player->setGamemode($player::ADVENTURE);
            $player->setHealth(20);
            $player->setFood(20);
			$player->removeAllEffects();
            $player->getInventory()->clearAll();
            $player->getArmorInventory()->clearAll();
            $player->getCursorInventory()->clearAll();
		}
	}
	
	public function playersGameOver(string $name) 
	{
		foreach($this->arenas[$name]['players'] as $player) {		    
            $player->setGamemode($this->plugin->getServer()->getDefaultGamemode());
            $player->setHealth(20);
            $player->setFood(20);
			$player->removeAllEffects();
            $player->getInventory()->clearAll();
            $player->getArmorInventory()->clearAll();
            $player->getCursorInventory()->clearAll();
		}
	}
	
	public function __destruct() 
	{
        unset($this->scheduler);
    }
}
	

	