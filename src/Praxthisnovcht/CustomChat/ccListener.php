<?php

namespace Praxthisnovcht\CustomChat;

use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
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
	
########################################################################################################################
########################################################################################################################
## https://forums.pocketmine.net/members/iksaku.1199/ 
## This part of the plugin CustomMessage.
## Part of the code was created by iksaku, official permission:
## 'Yes, I have no problem with that, but by legal issues, I recommend you to merge the plugin with CustomChat, I will delete mine from the repo and I will give you the privileges above CustomMessages so you can freely add those functions to CustomChat!

	public function onPlayerJoin(PlayerJoinEvent $event) {                                                                                                                             
		$message = $this->getConfig()->get("CustomJoin");                                                                                                                            
        if($message === false){                                                                                                                                                                    
            $event->setJoinMessage(null);                                                                                                                                                       
        }                                                                                                                                                                                                     
        $message = str_replace("@player", $event->getPlayer()->getDisplayName(), $message);                                                                      
        $event->setJoinMessage($message);
		
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
		$message = str_replace ( "{@prefix}", $prefix, $message );
		return $message;
		
		if($this->factionspro == true && $this->factionspro->isInFaction($player->getName())) {
			$getUserFaction = $this->factionspro->getPlayerFaction($player->getName()); 
			$message = str_replace ( "{@faction}", $getUserFaction, $message );
		}else{
			$nofac = $this->pgin->getConfig ()->get ( "if-player-has-no-faction");
			$message = str_replace ( "{@faction}", $nofac, $message );
		}
		
        $message = str_replace ( "{Money}", MassiveEconomyAPI::getInstance()->getMoney($player->getName()), $message); 
		
		
		$message = str_replace ( "{Kills}", KillChat::getInstance()->getDeaths("Player Name")()), $message); 
		
	    $message = str_replace ( "{Deaths}", KillChat::getInstance()->getKills("Player Name")()), $message); 		
		
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
    public function onPlayerQuit(PlayerQuitEvent $event){
        $message = $this->getConfig()->get("CustomLeave");
        if($message === false){
            $event->setQuitMessage(null);
        }
        $message = str_replace("@player", $event->getPlayer()->getDisplayName(), $message);
        $event->setQuitMessage($message);
    }
########################################################################################################################
########################################################################################################################



	public function getFormattedMessage(Player $player, $message) {
		$format = $this->pgin->getConfig ()->get ( "chat-format" );
		// "chat-format: '{WORLD_NAME}:[{PREFIX}]<{DISPLAY_NAME}> ({Kills}) {MESSAGE}'";		
		$format = str_replace ( "{WORLD_NAME}", $player->getLevel ()->getName (), $format );
		
		
		// FactionsPro Needed $FactionsPro->getFaction
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
				
        if($this->economyjob && $this->economyjob->player->exists($player->getName())){
                    $job = $this->economyjob->getPlayers($sender->getName());
                    $format = str_replace("{JOB}",$job, $format);
        }else{
            $nojob = $this->pgin->getConfig()->get("if-player-has-no-job");
            $format = str_replace("{JOB}",$nojob, $format);
        }
        /*Economy$Job API END*/  

		
		/* --------- PLAYERSTATS API PART ------ */
		if($playerstats->getDeaths($player) == null){
			$playerstats_deaths = "";
		}
		$format = str_replace("{Deaths}",$playerstats_deaths, $format);
		#################################################################
		
		if($playerstats->getBreaks($player) == null){
			$playerstats_break = "";
		}
		$format = str_replace("{Break_Block}",$playerstats_break, $format);
		#################################################################		
		if($playerstats->getPlaces($player) == null){
			$playerstats_pose = "";
		}
		$format = str_replace("{Pose_Block}",$playerstats_pose, $format);
		#################################################################				
		if($playerstats->getLeaves($player) == null){
			$playerstats_leave = "";
		}
		$format = str_replace("{Leave_Counter}",$playerstats_leave, $format);		
		#################################################################				
		if($playerstats->getKicked($player) == null){
			$playerstats_kick = "";
		}
		$format = str_replace("{Kick_Counter}",$playerstats_kick, $format);				
		#################################################################				
		if($playerstats->getJoins($player) == null){
			$playerstats_join = "";
		}
		$format = str_replace("{Counter_JoinGames}",$playerstats_join, $format);		
		#################################################################				
		if($playerstats->getDrops($player) == null){
			$playerstats_drop = "";
		}
		$format = str_replace("{Drops_Block}",$playerstats_drop, $format);
		#################################################################						
		/* ----------- ENDED API PART -------- */		
 
        $format = str_replace ( "{Money}", MassiveEconomyAPI::getInstance()->getMoney($player->getName()), $format); 
		
		
		$format = str_replace ( "{Kills}", KillChat::getInstance()->getDeaths("Player Name")()), $format); 
		
	    $format = str_replace ( "{Deaths}", KillChat::getInstance()->getKills("Player Name")()), $format); 
		
     
         
       
		
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
