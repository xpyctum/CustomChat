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

use MassiveEconomy\MassiveEconomyAPI;
use Praxthisnovcht\KillChat\KillChat;
/**
 * PraxListener // CustomChat 1.3.1 Release
 */
class ccListener implements Listener {
	public $pgin;	
	private $factionspro;
	private $pureperms;
	
	public function __construct(ccMain $pg) {
		$this->pgin = $pg;
        // Use FactionsPro by Tethered_
		$this->factionspro = $this->pgin->getServer()->getPluginManager()->getPlugin("FactionsPro");
		// Use PurePerms by 64FF00	
		$this->pureperms = $this->pgin->getServer()->getPluginManager()->getPlugin("PurePerms");
		// Use EconomyJob by Onebone	   
		$this->economyjob = $this->pgin->getServer()->getPluginManager()->getPlugin("EconomyJob");
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
	    $message = $this->pgin->getConfig()->get("CustomJoin");
        if($message === false){                                                                                                                                                                    
            $event->setJoinMessage(null);                                                                                                                                                       
        }                                                                                                                                                                                                     
        $message = str_replace("@Player", $event->getPlayer()->getDisplayName(), $message);                                                                      
        $event->setJoinMessage($message);
			
		if($this->factionspro == true && $this->factionspro->isInFaction($player->getName())) {
			$getUserFaction = $this->factionspro->getPlayerFaction($player->getName()); 
			$message = str_replace ( "{@faction}", $getUserFaction, $message );
		}else{
			$nofac = $this->pgin->getConfig ()->get ( "if-player-has-no-faction");
			$message = str_replace ( "@Faction", $nofac, $message );
		}		
		     $player = $event->getPlayer ();
		     $this->pgin->formatterPlayerDisplayName ( $player );
	}
    public function onPlayerQuit(PlayerQuitEvent $event){
        $message = $this->pgin->getConfig()->get("CustomLeave"); 
        if($message === false){
            $event->setQuitMessage(null);
        }
        $message = str_replace("@player", $event->getPlayer()->getDisplayName(), $message);
        $event->setQuitMessage($message);
		
		if($this->factionspro == true && $this->factionspro->isInFaction($player->getName())) {
			$getUserFaction = $this->factionspro->getPlayerFaction($player->getName()); 
			$message = str_replace ( "{@faction}", $getUserFaction, $message );
		}else{
			$nofac = $this->pgin->getConfig ()->get ( "if-player-has-no-faction");
			$message = str_replace ( "@Faction", $nofac, $message );
		}
	}
	public function getFormattedMessage(Player $player, $message) {
		$format = $this->pgin->getConfig ()->get ( "chat-format" );
		// "chat-format: '{WORLD_NAME}:[{PREFIX}]<{DISPLAY_NAME}> ({Kills}) {MESSAGE}'";		
		$format = str_replace ( "{WORLD_NAME}", $player->getLevel ()->getName (), $format );
		
		$format = str_replace ( "{Money}", MassiveEconomyAPI::getInstance()->getMoney($player->getName()), $format); 
		
		
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
