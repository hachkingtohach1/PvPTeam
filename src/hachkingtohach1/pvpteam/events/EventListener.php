<?php

declare(strict_types=1);

namespace hachkingtohach1\pvpteam\events;

use hachkingtohach1\pvpteam\Main;
use hachkingtohach1\pvpteam\Math\Vector3;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\Listener;

class EventListener implements Listener {

    /** @var $plugin */
    public $plugin;
	
	/** @var $arena */
	public $arena;
	
	/** @var $config */
	public $config;

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
			$event->setCancelled(true);
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
	
	public function onDamage(EntityDamageEvent $event) : void
	{
        $player = $event->getEntity();
        if($event->getFinalDamage() >= $player->getHealth()) 
		{
			foreach($this->arena->arenas as $arena)
			{			
                foreach($arena['teams'] as $team)
				{			
			        $players = $team['players'];
			
		            if(!empty($players[$player->getName()])) 
					{
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
		}
	}
	
	public function onBlockPlace(BlockPlaceEvent $event) : void
	{
		$player = $event->getPlayer();
		if($this->arena->inGame($player) === true) 
		{
			$event->setCancelled(true);
		}
	}
	
	public function onQuit(PlayerQuitEvent $event) : void
	{
		$player = $event->getPlayer();
		if($this->arena->inGame($player) === true) 
		{
			$this->arena->onLeaveArena($player, false);
		}
	}
	
	public function onExhaust(PlayerExhaustEvent $event) : void 
	{
		$player = $event->getPlayer();
		if($this->arena->inGame($player) === true) 
		{
			$event->setCancelled(true);
		}
	}
}