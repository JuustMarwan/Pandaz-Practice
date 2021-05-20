<?php

declare(strict_types=1);

namespace Zinkil\Pandaz\listeners;

use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\event\server\QueryRegenerateEvent;
use pocketmine\event\plugin\PluginDisableEvent;
use Zinkil\Pandaz\Core;
use Zinkil\Pandaz\tasks\onetime\RestartTask;

class ServerListener implements Listener{
	
	public $plugin;
	
	public function __construct(Core $plugin){
		$this->plugin=$plugin;
	}
	/**
	* @priority HIGHEST
	*/
	public function onQuery(QueryRegenerateEvent $event){
        $event->setMaxPlayerCount(100);
	}
	/**
	* @priority HIGHEST
	*/
	public function onPluginDisable(PluginDisableEvent $event){
		$plugin=$event->getPlugin();
		if($plugin->getDescription()->getAuthors() !== ["Zinkil"] || $plugin->getDescription()->getName() !== "Pandaz"){
			foreach($this->plugin->getServer()->getOnlinePlayers() as $player){
				if($this->plugin->getDuelHandler()->isInDuel($player)){
					$duel=$this->plugin->getDuelHandler()->getDuel($player);
					$duel->endDuelPrematurely(true);
				}
				if($this->plugin->getDuelHandler()->isInPartyDuel($player)){
					$pduel=$this->plugin->getDuelHandler()->getPartyDuel($player);
					$pduel->endDuelPrematurely(true);
				}
			}
		}
	}
}