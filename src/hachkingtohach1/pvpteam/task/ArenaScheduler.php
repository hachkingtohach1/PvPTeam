<?php

declare(strict_types=1);

namespace hachkingtohach1\pvpteam\task;

use hachkingtohach1\pvpteam\arena\Arena;
use pocketmine\scheduler\Task;

class ArenaScheduler extends Task {

    /** @var $plugin */
    protected $plugin;
	
	/** @var $minplayers */
	protected $minplayers = 2;
 
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
		
        switch($arenabase->getAllStatus()) 
		{
            case Arena::WAITING:
			    if($arenabase->countPlayers() >= $this->minplayers) 
				{
					foreach($arenabase->arenas as $arena) 
					{
						$arenabase->arenas[$arena['name']]['starttime']--; 
						if($arenabase->arenas[$arena['name']]['starttime'] == 0) 
						{
							$arenabase->startTheGame($arena);
						}
					}
				} else {
					foreach($arenabase->arenas as $arena) 
					{
					    $arenabase->sendBroadcastPopup($arena['name'], "Waiting!");
					}
				}
            break;
			case Arena::PLAYING:
			    foreach($arenabase->arenas as $arena) 
				{
					$arenabase->arenas[$arena['name']]['timeend']--; 
					
					$arenabase->sendBroadcastPopup(
					    $arena['name'], "Time: ", $arenabase->arenas[$arena['name']]['timeend']
					);
					
					if($arenabase->arenas[$arena['name']]['timeend'] == 0) 
				    {
                        $arenabase->gameOver($arena['name']);
                    }
				}
            break;
			case Arena::RESTARTING:
			    foreach($arenabase->arenas as $arena) 
				{
					$arenabase->arenas[$arena['name']]['restarttime']--;
                    $arenabase->sendBroadcastPopup(
					    "Restarting in ", $arenabase->arenabase[$arena['name']]['restarttime']
					);
                    if($arenabase->arenas[$arena['name']]['restarttime'] == 0) 
					{				
					    $arenabase->reloadDataArena($arena['name']);
					}				
				}
			break;
		}
	}
}