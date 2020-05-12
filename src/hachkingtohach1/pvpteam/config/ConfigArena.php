<?php

namespace hachkingtohach1\pvpteam\config;

use hachkingtohach1\pvpteam\Main;
use pocketmine\Player;

class ConfigArena {
	
	/** @var $plugin */
	public $plugin;
	
	/** @var $data */
	public $data;
	
    public function __construct(Main $plugin) 
	{
		$this->plugin = $plugin;
		$this->data = $this->plugin->getArenasData;
    }
	
	public function getDataConfig(string $name) { return $this->data->get($name); }
	
	public function saveDataInArray(Player $player, $array, $data, $change) 
	{
		$this->change[$player->getName()] = $array;
		$this->change[$player->getName()][$change] = $data;
	}

    public function changeDataArena(Player $player, string $namedata, string $change, $data)
	{
		$this->saveDataInArray(
		    $player,
			$this->getDataConfig($namedata),
			$data,
			$change
		);
		$this->data->set($namedata, $this->change[$player->getName()]);
		$this->data->save();
	}
	
	public function changeSpawnTeamArena(Player $player, string $namedata, string $team, string $color, $data) 
	{
		$this->change[$player->getName()] 
		    = 
		$this->getDataConfig($namedata);
		$this->change[$player->getName()]['spawnteam'][$color] = $data;
		$this->data->set($namedata, $this->change[$player->getName()]);
		$this->data->save();
	}	
	
	public function addTeamArena(Player $player, string $namedata, string $team, string $color) 
	{
		$this->change[$player->getName()] 
		    = 
		$this->getDataConfig($namedata);
		
		$this->change[$player->getName()]['teams'][$team] = 
		[
		  'players' => 0,
		  'color' => $color
		];
		
		$this->data->set($namedata, $this->change[$player->getName()]);
		$this->data->save();
	}
	
	public function checkDataConfig(string $name) : bool
	{
		if(
		    !is_array($this->data->get("teams")) ||
			!is_array($this->data->get("spawnteam")) ||
			$this->data->get("spawnteam") === null ||
			$this->data->get("spawnspectator") === null ||
			$this->data->get("level") === null ||
		) return false;
		return true;
	}
}
	

	