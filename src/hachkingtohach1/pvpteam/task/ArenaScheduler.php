<?php

declare(strict_types=1);

namespace hachkingtohach1\pvpteam\task;

use hachkingtohach1\pvpteam\arena\Arena;
use pocketmine\scheduler\Task;

class ArenaScheduler extends Task {

    /** @var $plugin */
    public $plugin;
	
	/** @var $minplayers */
	public $minplayers = 2;
	
	public $gotostart = false;
 
    public function __construct(Arena $plugin) 
	{
        $this->plugin = $plugin;       
    }

   /**
    * @param int $currentTick
    */
    public function onRun(int $currentTick) 
	{
        $arenabase = $this->plugin;
		
        if($arenabase->loaded === false) return;
		
		foreach($arenabase->arenas as $arena) 
		{
			$status = $arenabase->arenas[$arena['name']]['status'];	
			
            switch($status) 
		    {
                case Arena::WAITING:
			       	if(count($arena['players']) >= $this->minplayers 
					    || $this->gotostart
					) {
						
						$arena['starttime'] -= 1;
						$this->sendBroadcastPopup($arena['name'], "Starting in ".$arena['starttime']);
						if($arena['starttime'] == 0) 
						{
							
						}
					} else {						
						$arenabase->sendBroadcastPopup($arena['name'], "Waiting!");
					}
                break;
			    case Arena::PLAYING:
					$arena['timeend'] -= 1;
					$arenabase->sendBroadcastPopup($arena['name'], "Time end: ".$arena['timeend']);
					if($arena['timeend'] == 0) {
						$arenabase->gameOver($arena['name']);
					}
                break;
			    case Arena::RESTARTING:
			        $arena['restarttime'] -= 1;
					$arenabase->sendBroadcastPopup($arena['name'], "Restarting in ".$arena['restarttime']);
					if($arena['restarttime'] == 0) {
						foreach($arena['players'] as $player) {
						    $player->teleport(
							    $arenabase->plugin->getServer()->getDefaultLevel()->getSpawnLocation()
							);
						}
						$arenabase->reloadDataArena($arena['name']);
					}
			    break;
			}
		}
	}
}