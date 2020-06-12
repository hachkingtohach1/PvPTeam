<?php

declare(strict_types=1);

namespace hachkingtohach1\pvpteam\task;

use hachkingtohach1\pvpteam\arena\Arena;
use pocketmine\scheduler\Task;

class ArenaScheduler extends Task {

    /** @var $plugin */
    public $plugin;
	
	/** @var $minplayers */
	public $minplayers = 1; // This is testing if you needed!
	
	/** @var bool $gotostart */
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
			       	if(count($arenabase->arenas[$arena['name']]['players']) >= $this->minplayers 
					    || $this->gotostart
					) {	
                        $arenabase->sendBroadcastPopup($arena['name'], "Starting in ".$arena['starttime']);
						$arenabase->arenas[$arena['name']]['starttime']--;					
						if($arena['starttime'] == 0) 
						{
							$arenabase->startTheGame($arena['name']);
						}													
					} else {						
						$arenabase->sendBroadcastPopup($arena['name'], "Waiting!");
					}
                break;
			    case Arena::PLAYING:
					$arenabase->arenas[$arena['name']]['timeend']--;
					$arenabase->sendBroadcastPopup($arena['name'], "Time end: ".$arenabase->arenas[$arena['name']]['timeend']);
					if($arenabase->arenas[$arena['name']]['timeend'] == 0) 
					{						
						$arenabase->gameOver($arenabase->arenas[$arena['name']]);
					}
                break;
			    case Arena::RESTARTING:
			        $arenabase->arenas[$arena['name']]['restarttime']--;
					$arenabase->sendBroadcastPopup($arena['name'], "Restarting in ".$arenabase->arenas[$arena['name']]['restarttime']);
					if($arenabase->arenas[$arena['name']]['restarttime'] == 0) 
					{
						foreach($arenabase->arenas[$arena['name']]['players'] as $player) 
						{
						    $player->teleport(
							    $arenabase->plugin->getServer()->getDefaultLevel()->getSpawnLocation()
							);
						}
						$arenabase->reloadDataArena($arenabase->arenas[$arena['name']]);
					}
			    break;
			}
		}
	}
}