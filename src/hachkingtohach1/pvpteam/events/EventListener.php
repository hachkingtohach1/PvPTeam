<?php

declare(strict_types=1);

namespace hachkingtohach1\pvpteam\events;

use hachkingtohach1\pvpteam\Main;
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
	
	/** @var $data */
	protected $data;

    public function __construct(Main $plugin) 
	{
        $this->plugin = $plugin;
		$this->arena = $this->plugin->getArena();
		$this->data = $this->plugin->getArenasData;
        $plugin->getServer()->getPluginManager()->registerEvents($this, $plugin);
    }

    public function onBlockBreak(BlockBreakEvent $event) : void
	{
		$player = $event->getPlayer();
		$namep = $player->getName();
		$x = $block->getX();
		$y = $block->getY();
		$z = $block->getZ();
		$xyzb = (new Vector3((int)$x, (int)$y, (int)$z))->__toString();
		if($this->arena->inGame($player) === true) {
			$event->setCancelled();
		}
		switch($this->plugin->setup[$namep][2]) {
			case 0:
			    $arena = $this->plugin->setup[$namep][2];
				$team = $this->plugin->setup[$namep][1];
			    $this->data->set($arena['spawnteam'][$team], $xyzb);
				unset($this->plugin->setup[$namep]);
				$player->sendMessage("SETUP: Done!");
			break;
			case 1:
			    $this->data->set($arena['spawnlobby'], $xyzb);
				unset($this->plugin->setup[$namep]);
				$player->sendMessage("SETUP: Done!");
		    break;
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