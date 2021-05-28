<?php

declare(strict_types=1);

namespace Zinkil\Pandaz\listeners;

use pocketmine\event\Listener;
use pocketmine\Server;
use pocketmine\Player;
use pocketmine\item\Item;
use pocketmine\level\Location;
use pocketmine\math\Vector3;
use pocketmine\utils\Config;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerDeathEvent;
use Zinkil\Pandaz\Core;
use Zinkil\Pandaz\CorePlayer;
use Zinkil\Pandaz\Utils;
use Zinkil\Pandaz\managers\PlayerProtectManager as PPM;

class PvPUtilsListener implements Listener{
	
	public $plugin;
	
	public $combo;
	
	public function __construct(){
		$this->plugin=Core::getInstance();
        $this->combo = new Config($this->plugin->getDataFolder() . "combo.yml", Config::YAML);

	}
	/**
	* @priority LOW
	*/
    public function onJoin(PlayerJoinEvent $event)
    {
        $player = $event->getPlayer();
        $this->combo->set($player->getName(), 0);
        /* Register Player */
    }
    
    public function onQuit(PlayerQuitEvent $event)
    {
        $player = $event->getPlayer();
        $this->combo->set($player->getName(), 0);
        /* Register Player */
        if (PPM::isIn($player)) {
            PPM::delIn($player);
        }
    }
    
    public function onPlayerDeath(PlayerDeathEvent $event)
    {
        $player = $event->getPlayer();
        /* If player has more than 0 combos */
        if ($this->combo->get($player->getName()) >= 1){
        /* Reset combo on quit */
        $this->combo->set($player->getName(), 0);
        }
    
        if (PPM::isIn($player)) {
            PPM::delIn($player);
        }
    }
	/**
	* @priority HIGH
	*/
	public function onEntityDamageByEntity(EntityDamageEvent $event){
		$player=$event->getEntity();
		$cause=$event->getCause();
		$level=$player->getLevel()->getName();
		switch($cause){
			case EntityDamageEvent::CAUSE_ENTITY_ATTACK:
			$damager=$event->getDamager();
			$dlevel=$damager->getLevel()->getName();
			if(!$player instanceof Player) return;
			if(!$damager instanceof Player) return;
			if($damager->isCreative()) return;
			if($level==Core::LOBBY) return;
			if($dlevel==Core::LOBBY) return;
			if($event->isCancelled()) return; // Check if hit from safezone/attack cooldown
			$damagerpos=$damager->getPosition() ?? new Vector3(0,0,0);
			$playerpos=$player->getPosition() ?? new Vector3(0,0,0);
			$distance=$damagerpos->distance($playerpos);
			$health=round($player->getHealth(), 1);
			$playername=$player->getDisplayName();
			$damagername=$damager->getDisplayName();
            $playercps=$this->plugin->getClickHandler()->getCps($player);
            $damagercps=$this->plugin->getClickHandler()->getCps($damager);
            $this->combo->set($damager->getName(), $this->combo->get($damager->getName()) + 1);
			$this->combo->set($player->getName(), 0);
            if(Utils::isPvPUtilsCounterEnabled($player)==true) $player->sendTip("§cCPS§7: §f".$damagercps."§7 | §cCombo§7: §f".$this->combo->get($damager->getName())."§7 | §cReach§7: §f".round($distance, 2));
            if(Utils::isPvPUtilsCounterEnabled($damager)==true) $damager->sendTip("§bCPS§7: §f".$damagercps."§7 | §bCombo§7: §f".$this->combo->get($damager->getName())."§7 | §bReach§7: §f".round($distance, 2));
            if ($player instanceof Player && $damager instanceof Player) {
                if (PPM::isIn($player)) {
                    if (PPM::getIn($player) === $damager->getName()) {
                        PPM::setIn($player, $damager);
                    } else {
                        $event->setCancelled();
                    }
                } elseif (PPM::isIn($damager)) {
                    if (PPM::getIn($damager) === $player->getName()) {
                        PPM::setIn($player, $damager);
                    } else {
                        $event->setCancelled();
                    }
                } else {
                    PPM::setIn($player, $damager);
                }
            }
		}
	}
}