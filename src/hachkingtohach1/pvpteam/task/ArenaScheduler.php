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
        $arena = $this->plugin;
        
        if($arena->setup) return;
        
        switch($arena->getAllStatus()) 
		{
            case Arena::WAITING:
			    if($arena->countPlayers() >= $this->minplayers) 
				{
					foreach($arena->arenas as $name) 
					{
						$arena->arenas[$name]['starttime']--; 
						if($arena->arenas[$name]['starttime'] == 0) 
						{
							$arena->startTheGame($name);
						}
					}
				} else {
					foreach($arena->arenas as $name) 
					{
					    $arena->sendBroadcastPopup($name, "Waiting!");
					}
				}
            break;
			case Arena::PLAYING:
			    foreach($arena->arenas as $name) 
				{
					$arena->arenas[$name]['timeend']--; 
					
					$arena->sendBroadcastPopup(
					    $name, "Time: ", $arena->arenas[$name]['timeend']
					);
					
					if($arena->arenas[$name]['timeend'] == 0) 
				    {
                        $arena->gameOver($name);
                    }
				}
            break;
			case Arena::RESTARTING:
			    foreach($arena->arenas as $name) 
				{
					$arena->arenas[$name]['timeend']--;
                    $arena->sendBroadcastPopup(
					    "Restarting in ", $arena->arenas[$name]['timeend']
					);
                    if($this->restartingtime == 0) 
					{				
					    $arena->reloadDataArena($name);
					}				
				}
			break;
		}
	}
}