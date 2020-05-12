<?php

declare(strict_types=1);

namespace hachkingtohach1\pvpteam\events;

use hachkingtohach1\pvpteam\Main;
use hachkingtohach1\pvpteam\Math\Vector3;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Listener;

class EventListener implements Listener {

    /** @var $plugin */
    public $plugin;
	
	/** @var $arena */
	protected $arena;
	
	/** @var $config */
	protected $config;

    public function __construct(Main $plugin) 
	{
        $this->plugin = $plugin;
		$this->arena = $this->plugin->getArena();
		$this->config = $this->plugin->configArena();
        $plugin->getServer()->getPluginManager()->registerEvents($this, $plugin);
    }

    public function onBlockBreak(BlockBreakEvent $event) : void
	{
		$player = $event->getPlayer();
		$namep = $player->getName();
		$block = $event->getBlock();
		$x = $block->getX();
		$y = $block->getY();
		$z = $block->getZ();
		$xyzb = (new Vector3((int)$x, (int)$y, (int)$z))->__toString();
		if($this->arena->inGame($player) === true) {
			$event->setCancelled();
		}				
		
		if(isset($this->plugin->setup[$namep])) {
			
			$arena = $this->plugin->setup[$namep][0];
		    $team = $this->plugin->setup[$namep][1];
			$color = $this->plugin->setup[$namep][2];
			
		    switch($this->plugin->setup[$namep][3]) {
			    case 0:			    				
				    $this->config->changeSpawnTeamArena($player, $arena, $team, $color, $xyzb);				
				    unset($this->plugin->setup[$namep]);				
				    $player->sendMessage("SETUP: Done!");
			    break;
			    case 1:
				    $this->config->changeDataArena($player, $arena, 'spawnlobby', $xyzb);
				    unset($this->plugin->setup[$namep]);
				    $player->sendMessage("SETUP: Done!");
		        break;
			    case 2:
				    $this->config->changeDataArena($player, $arena, 'spawnspectator', $xyzb);
				    unset($this->plugin->setup[$namep]);
				    $player->sendMessage("SETUP: Done!");
		        break;
		    }
		}
	}
	
	public function onBlockPlace(BlockPlaceEvent $event) : void
	{
		$player = $event->getPlayer();
		if($this->arena->inGame($player) === true) {
			$event->setCancelled();
		}
	}
}