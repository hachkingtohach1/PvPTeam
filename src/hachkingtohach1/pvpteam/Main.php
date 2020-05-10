<?php

declare(strict_types=1);

namespace hachkingtohach1\pvpteam;

use hachkingtohach1\pvpteam\arena\Arena;
use hachkingtohach1\pvpteam\config\ConfigArena;
use hachkingtohach1\pvpteam\commands\Commands;
use hachkingtohach1\pvpteam\events\EventListener;
use pocketmine\utils\Config;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;

class Main extends PluginBase {
	
	/** @var $setup */
	public $setup;
	
	/** @var $change */
	public $change;
	
	/** @var $eventListener */
	public $eventListener;
	
    public function onEnable() : void
	{
		$this->newConfigFile();
		$this->loadArenas();
		$this->eventListener = new EventListener($this);
		$this->getServer()->getCommandMap()->register("pvpteam", new Commands($this));
	}
	
	public function onDisable() : void
	{
		$this->loadArenas();		
	}
	
	public function newConfig(string $name)
	{
		$new = new Config($name, Config::YAML);
		return $new;
	}
	
	public function newConfigFile() 
	{
		$datafolder = $this->getDataFolder();
		$this->getArenasData = $this->newConfig($datafolder."arenas.yml");
	}

    public function configArena() { return (new ConfigArena($this)); }		
	
	public function loadArenas() 
	{
		foreach($this->getArenasData->getAll() as $name => $data) 
		{
			$new = new Arena($this, $data['name'], $data, true);
		}
	}
	
	public function getArena() 
	{
		foreach($this->getArenasData->getAll() as $name => $data) 
		{
			$new = new Arena($this, $name, $data, false);
			return $new;
		}
	}
}
	

	