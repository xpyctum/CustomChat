<?php

namespace Praxthisnovcht\CustomChat;

use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
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
 * PraxListener // CustomChat 1.1.2 Release
 */
class ccListener implements Listener {
	public $pgin;
	private $factionspro;
	public function __construct(ccMain $pg) {
		$this->pgin = $pg;
         // Use FactionsPro by Tethered_
		$this->factionspro = $this->pgin->getServer()->getPluginManager()->getPlugin("FactionsPro");
		// Use EconomyJob by Onebone	   
		$this->economyjob = $this->pgin->getServer()->getPluginManager()->getPlugin("EconomyJob");
		// Use PurePerms by 64FF00	
		$this->pureperms = $this->pgin->getServer()->getPluginManager()->getPlugin("PurePerms");
		
		
		$playerstats = $this->pgin->getServer()->getPluginManager()->getPlugin("PlayerStats");
			// Counter ALL! Then use $playerstats->get(whatYouNeed!);
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
		// "chat-format: '{WORLD_NAME}:[{PREFIX}]<{DISPLAY_NAME}> ({Kills}) {MESSAGE}'";		
		$format = str_replace ( "{WORLD_NAME}", $player->getLevel ()->getName (), $format );
		// PlayerStats Needed  ")->getDeaths($player);
		// FactionsPro Needed $FactionsPro->getFaction
		// CustomChat 1.1.7 Release
		if($this->factionspro == true && $this->factionspro->isInFaction($player->getName())) {
			$getUserFaction = $this->factionspro->getPlayerFaction($player->getName()); 
			$format = str_replace ( "{FACTION}", $getUserFaction, $format );
		}else{
			$nofac = $this->pgin->getConfig ()->get ( "if-player-has-no-faction");
			$format = str_replace ( "{FACTION}", $nofac, $format );
		}

		if(!$this->pureperms) {
			$format = str_replace("{PurePerms}", "NoGroup", $format);
		}
		if($this->pureperms) {
				$isMultiWorldEnabled = $this->pureperms->getConfig()->get("enable-multiworld-formats");
				$levelName = $isMultiWorldEnabled ?  $player->getLevel()->getName() : null;
                 $format = str_replace("{PurePerms}", $this->pureperms->getUser($player)->getGroup($levelName)->getName(), $format);
            } else {
                return false;
                }
				
		/* --------- PLAYERSTATS API PART ------ */
		if($playerstats->getDeaths($player) == null){
			$playerstats_deaths = "";
		}
		$format = str_replace("{Deaths}",$playerstats_deaths, $format);
		
		if($playerstats_break == null){
			$playerstats_break = "";
		}
		$format = str_replace("{Break_Block}",$playerstats_break, $format);
		
		if($playerstats_pose == null){
			$playerstats_pose = "";
		}
		$format = str_replace("{Pose_Block}",$playerstats_pose, $format);
		
		if($playerstats_leave == null){
			$playerstats_leave = "";
		}
		$format = str_replace("{Leave_Counter}",$playerstats_leave, $format);		

		if($playerstats_kick == null){
			$playerstats_kick = "";
		}
		$format = str_replace("{Kick_Counter}",$playerstats_kick, $format);				

		if($playerstats_join == null){
			$playerstats_join = "";
		}
		$format = str_replace("{Counter_JoinGames}",$playerstats_join, $format);		

		if($playerstats_drop == null){
			$playerstats_drop = "";
		}
		$format = str_replace("{Drops_Block}",$playerstats_drop, $format);				
		/* ----------- ENDED API PART -------- */		
         
       
         
       
		
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
	}
	private function log($msg) {
		$this->pgin->getLogger ()->info ( $msg );
	}
}
