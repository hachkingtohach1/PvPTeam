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
	
	/** @var $language */
	public $language;
	
    public function onEnable() : void
	{
		$this->saveDefaultConfig();
		$this->newConfigFile();
		$this->loadArenas();
		$this->checkLanguage();
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
		$this->getArenasData = $this->newConfig($datafolder.'arenas.yml');
	}
	
	public function checkLanguage()
	{
		$datafolder = $this->getDataFolder();
		$lang = $this->getConfig()->get('name_lang'); // This is for yml
		$this->language = $this->newConfig($datafolder."language/".$lang);
	}

    public function configArena() { return (new ConfigArena($this)); }		
	
	public function loadArenas() 
	{
		foreach($this->getArenasData->getAll() as $name => $data) 
		{    
		    if($data['enable'] === true) 
			{
			    $new = new Arena($this, $data['name'], $data, true);
				$this->getLogger()->warning(
				    $data['name'].' can not load data!'
				);
			} else {
				$this->getLogger()->info(
				    $data['name'].' loaded!'
				);
			}
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
	

	