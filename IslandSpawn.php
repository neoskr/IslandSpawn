<?php

/**
 * @name        IslandSpawn
 * @main        IslandSpawn\IslandSpawn
 * @author      Ne0sW0rld
 * @version     Master - Beta 1
 * @api         3.0.0
 * @description (!) 섬 스폰지점 변경 시스템
 */


namespace IslandSpawn;
    

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;

use pocketmine\utils\Config;
use pocketmine\level\Position;

use pocketmine\Server;

use pocketmine\event\player\PlayerCommandPreprocessEvent;

use ifteam\SimpleArea\database\area\AreaSection;
use ifteam\SimpleArea\database\area\AreaProvider;


class IslandSpawn extends PluginBase implements Listener
{
    

    private static $instance = null;
	

	public $prefix  = '§a§l[§fIsland§a] §r§f';


    public function onLoad ()
    {

        self::$instance = $this;
        
    }


    public static function getInstance ()
    {

        return self::$instance;
        
	}
    
	
    public function onEnable ()
    {

		$this->getServer()->getPluginManager()->registerEvents ($this, $this);

		$this->database = new Config ($this->getDataFolder() . 'islands_spawns.yml', Config::YAML);
		$this->db       = $this->database->getAll();

    }
	
	
	public function onIslandCommand (PlayerCommandPreprocessEvent $event)
	{
		
		$player = $event->getPlayer();
		$name = $player->getName();

		$explode = explode (' ', $event->getMessage());
		
		if (isset ($explode[1]) && $explode[0] === '/섬')
		{
			
			switch ($explode[1])
			{
				
				case '이동':
				
					if (isset ($explode[2]))
					{
						if (isset ($this->db [$explode[2]]))
						{
							
							$event->setCancelled (true);

							$player->teleport ($this->getPosBystring ($this->db [$explode[2]]));
							$player->sendMessage ($this->prefix . $explode[2] . '번 섬으로 이동했습니다.');
							
						}
						
						$player->sendMessage ($this->prefix . '[ /섬 스폰설정 ] 을 입력하여 자신의 섬 스폰지점을 변경할 수 있습니다.');
						
					}
					
				break;
				
				case '스폰설정':
				
					$event->setCancelled (true);
					$area = AreaProvider::getInstance()->getArea ($player->level, $player->x, $player->z);

					if (! $area instanceof AreaSection)
					{
						
						$player->sendMessage ($this->prefix . '해당 위치에서 섬을 찾을 수 없습니다.');
						return true;
						
					}
					
					if ($player->getLevel()->getFolderName() !== 'island')
					{
						
						$player->sendMessage ($this->prefix . '자신의 섬으로 이동한 후 명령어를 입력해주세요.');
						return true;
						
					}

					if (! $area->isOwner ($player))
					{
						
						$player->sendMessage ($this->prefix . '당신은 해당 섬의 주인이 아닙니다.');
						return true;
						
					}
					
					$this->db [$area->getId()] = $this->getStringByPos ($player);
					$player->sendMessage ($this->prefix . '섬의 스폰 지점을 변경했습니다.');
					
					$this->database->setAll ($this->db);
					$this->database->save ();
					
				break;
					
			}
			
		}

	}
	
	
	public function getStringByPos (Position $pos) : string
	{
		
		return (float) $pos->x . ':' . (float) $pos->y . ':' . (float) $pos->z . ':' . $pos->level->getFolderName();
		
	}
	
	
	public function getPosByString (string $pos) : Position
	{
		
		$explode = explode (':', $pos);
		return new Position ((float) $explode[0], (float) $explode[1], (float) $explode[2], Server::getInstance()->getLevelByName ((string) $explode[3]));

	}


}
