<?php

namespace Praxthisnovcht\CustomChat;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\level\Position;
use pocketmine\event\block\SignChangeEvent;
use pocketmine\tile\Sign;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\Player;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;

/**
 * PraxListener Version 1.1.7 add FactionsPro Plugin
 *
 */
class ccListener implements Listener {
	public $pgin;
	public function __construct(ccMain $pg) {
		$this->pgin = $pg;
	}
	public function onPlayerChat(PlayerChatEvent $event) {
		$allowChat = $this->pgin->getConfig ()->get ( "disablechat" );
		// $this->log ( "allowChat ".$allowChat);
		if ($allowChat) {
			$event->setCancelled ( true );
			return;
		}
		
		if (! $allowChat || $allowChat == null) {
			$player = $event->getPlayer ();
			
			$perm = "chatmute";
			// $this->log ( "permission ".$player->isPermissionSet ( $perm ));
			
			if ($player->isPermissionSet ( $perm )) {
				$event->setCancelled ( true );
				return;
			}
			$format = $this->getFormattedMessage ( $player, $event->getMessage () );
			$config_node = $this->pgin->getConfig ()->get ( "enable-formatter" );
			if (isset ( $config_node ) and $config_node === true) {
				$event->setFormat ( $format );
			}
			return;
		}
	}
	public function onPlayerJoin(PlayerJoinEvent $event) {
		$player = $event->getPlayer ();
		$this->pgin->formatterPlayerDisplayName ( $player );
	}
// 	public function formatterPlayerDisplayName(Player $p) {
// 		$playerPrefix = $this->pgin->getConfig ()->get ( $player->getName () );
// 		$defaultPrefix = $this->pgin->getConfig ()->get ( "default-player-prefix" );
		
// 		if ($playerPrefix != null) {
// 			$p->setDisplayName ( $playerPrefix . ":" . $name );
// 			return;
// 		}
		
// 		if ($defaultPrefix != null) {
// 			$p->setDisplayName ( $defaultPrefix . ":" . $name );
// 			return;
// 		}
// 	}
	
	public function getFormattedMessage(Player $player, $message) {
		$format = $this->pgin->getConfig ()->get ( "chat-format" );
		// "chat-format: '{WORLD_NAME}:[{Factions-Names}][{PREFIX}]<{DISPLAY_NAME}> ({Kills}) {MESSAGE}'";		
		$format = str_replace ( "{WORLD_NAME}", $player->getLevel ()->getName (), $format );
		

				
		// PlayerStats Needed  ")->getDeaths($player);
		//$format = str_replace ( "{Kills}" .....	
		
		$nick = $this->pgin->getConfig ()->get ( $player->getName () > ".nick");
		if ($nick!=null) {
			$format = str_replace ( "{DISPLAY_NAME}", $nick, $format );
		} else {
			$format = str_replace ( "{DISPLAY_NAME}", $player->getName (), $format );			
		}
		
		$format = str_replace ( "{MESSAGE}", $message, $format );
		
		$level = $player->getLevel ()->getName ();
		
		$prefix = null;
		$playerPrefix = $this->pgin->getConfig ()->get ( $player->getName ().".prefix" );
		if ($playerPrefix != null) {
			$prefix = $playerPrefix;
		} else {
			//use default prefix
			$prefix = $this->pgin->getConfig ()->get ( "default-player-prefix");
		}				
		if ($prefix == null) {
			$prefix = "";
		}
		$format = str_replace ( "{PREFIX}", $prefix, $format );
		return $format;
		
		
		// FacionsPro Needed 
		if($factionspro->isInFaction($playerName)) {
			return $factionspro->getFaction($playerName);
		$format = str_replace("{Factions_Name}", $this->$factionspro->getPlayerFaction($player->getName()), $format);
			} else {
				return $format;
				}
		
	}
	private function log($msg) {
		$this->pgin->getLogger ()->info ( $msg );
	}
}
