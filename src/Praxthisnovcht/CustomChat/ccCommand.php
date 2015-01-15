<?php

namespace Praxthisnovcht\CustomChat;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\level\Position;
use pocketmine\level\Level;
use pocketmine\level\Explosion;
use pocketmine\event\entity\EntityMoveEvent;
use pocketmine\event\entity\EntityMotionEvent;
use pocketmine\event\Listener;
use pocketmine\math\Vector3 as Vector3;
use pocketmine\math\Vector2 as Vector2;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\network\protocol\DataPacket;
use pocketmine\network\protocol\Info;
use pocketmine\network\protocol\LoginPacket;

/**
 * Command // CustomChat 1.3.0 Release
 *
 */
class ccCommand {
	private $pgin;
	/**
	 *
	 * @param
	 *        	$pg
	 */
	public function __construct(ccMain $pg) {
		$this->pgin = $pg;
	}

	
	/**
	 * onCommand
	 *
	 * @param CommandSender $sender        	
	 * @param Command $command        	
	 * @param unknown $label        	
	 * @param array $args        	
	 * @return boolean
	 */
	public function onCommand(CommandSender $sender, Command $command, $label, array $args) {
		// disable chat for all players
		if ((strtolower ( $command->getName () ) == "disablechat")) {
			$this->pgin->getConfig ()->set ( "disablechat", true ); // config.yml
			$this->pgin->getConfig ()->save ();
			$sender->sendMessage ("§4" .TextFormat::RED . "disable chat for all players" );
			$this->log ( "disable chat for all players" );
			return;
		}
		// enable chat for all players
		if ((strtolower ( $command->getName () ) == "enablechat")) {
			$this->pgin->getConfig ()->set ( "disablechat", false ); // config.yml
			$this->pgin->getConfig ()->save ();
			$sender->sendMessage ("§a" .TextFormat::GREEN . "enable chat for all players" );
			$this->log ( "enable chat for all players" );
			return;
		}
		
		// sets default prefix for new players
		if ((strtolower ( $command->getName () ) == "defprefix") && isset ( $args [0] )) {
			$playerName = $args [0];
			$p = $sender->getServer ()->getPlayerExact ( $playerName );
			if ($p == null) {
				$sender->sendMessage ("§4" .TextFormat::RED . "player " . $playerName . " is not online!" );
				return true;
			}
			$prefix = $args [1];
			$this->pgin->getConfig ()->set ( "default-player-prefix", $prefix );
			$this->pgin->getConfig ()->save ();
			$sender->sendMessage ("§a" .TextFormat::GREEN . " all players default prefix set to " . $args [1] );
			return;
		}
		
		// sets prefix for player
		if ((strtolower ( $command->getName () ) == "setprefix") && isset ( $args [0] ) && isset ( $args [1] )) {
			$playerName = $args [0];
			$p = $sender->getServer ()->getPlayerExact ( $playerName );
			if ($p == null) {
				$sender->sendMessage ("§4" .TextFormat::RED . "player " . $playerName . " is not online!" );
				return true;
			}
			$prefix = $args [1];
			$this->pgin->getConfig ()->set ( $p->getName ().".prefix", $prefix );
			$this->pgin->getConfig ()->save ();
			
			// $p->setDisplayName($prefix.":".$name);
			$this->pgin->formatterPlayerDisplayName ( $p );
			$sender->sendMessage ("§a" .TextFormat::GREEN . $p->getName () . " prefix set to " . $args [1] );
			return;
		}
		
		// set player's prefix to default.
		if ((strtolower ( $command->getName () ) == "delprefix") && isset ( $args [0] )) {
			$playerName = $args [0];
			$p = $sender->getServer ()->getPlayerExact ( $playerName );
			if ($p == null) {
				$sender->sendMessage ("§4" .TextFormat::RED . "player " . $playerName . " is not online!" );
				return true;
			}
			$this->pgin->getConfig ()->remove ( $p->getName () . ".prefix" );
			$this->pgin->getConfig ()->save ();
			$sender->sendMessage ("§a" .TextFormat::RED . $p->getName () . " prefix set to default" );
			return;
		}
		
		// sets nick for player
		if ((strtolower ( $command->getName () ) == "setnick") && isset ( $args [0] ) && isset ( $args [1] )) {
			$playerName = $args [0];
			$p = $sender->getServer ()->getPlayerExact ( $playerName );
			if ($p == null) {
				$sender->sendMessage ("§6" .TextFormat::RED . "player " . $playerName . " is not online!" );
				return true;
			}
			$nick = $args [1];
			$this->pgin->getConfig ()->set ( $p->getName () . ".nick", $nick );
			$this->pgin->getConfig ()->save ();
			
			$this->pgin->formatterPlayerDisplayName ( $p );
			$sender->sendMessage ("§6" .TextFormat::GREEN . $p->getName () . " nick name set to " . $args [1] );
			return;
		}
		// sets nick for player
		if ((strtolower ( $command->getName () ) == "delnick") && isset ( $args [0] ) && isset ( $args [1] )) {
			$playerName = $args [0];
			$p = $sender->getServer ()->getPlayerExact ( $playerName );
			if ($p == null) {
				$sender->sendMessage ("§4" .TextFormat::RED . "player " . $playerName . " is not online!" );
				return true; 
			}
			$nick = $args [1];
			$this->pgin->getConfig ()->remove ( $p->getName () . ".nick" );
			$this->pgin->getConfig ()->save ();
			// save yml
			
			$this->pgin->formatterPlayerDisplayName ( $p );
			$sender->sendMessage ("§6" .TextFormat::GREEN . $p->getName () . " nick removed " );
			return;
		}
		
		// mute player from chat
		if ((strtolower ( $command->getName () ) == "mute") && isset ( $args [0] )) {
			$playerName = $args [0];
			// check if the player exist
			$p = $sender->getServer ()->getPlayerExact ( $playerName );
			if ($p == null) {
				$sender->sendMessage ("§4" .TextFormat::RED . "player " . $playerName . " is not online!" );
				return true;
			}
			$perm = "chatmute";
			$p->addAttachment ( $this->pgin, $perm, true );
			$sender->sendMessage ("§a".TextFormat::GREEN . $p->getName () . " chat muted" );
			// $this->log ( "isPermissionSet " . $p->isPermissionSet ( $perm ) );
			return;
		}
		// - unmute player from chat
		if ((strtolower ( $command->getName () ) == "unmute") && isset ( $args [0] )) {
			$playerName = $args [0];
			// check if the player exist
			$p = $sender->getServer ()->getPlayerExact ( $playerName );
			if ($p == null) {
				$sender->sendMessage ("§4" .TextFormat::RED . "player " . $playerName . " is not online!" );
				return true;
			}
			$perm = "chatmute";
			foreach ( $p->getEffectivePermissions () as $pm ) {
				if ($pm->getPermission () == $perm) {
					// $this->log ( "remove attachements " . $pm->getValue () );
					$p->removeAttachment ( $pm->getAttachment () );
					$sender->sendMessage ("§2" .TextFormat::GREEN . $p->getName () . " chat unmuted" );
					return;
				}
			}
			$sender->sendMessage ("§4" .TextFormat::RED . $p->getName () . " already unmuted" );
			// $this->log ( "isPermissionSet " . $p->isPermissionSet ( $perm ) );
			return; // next try again
			
		}
	}
	             // TODO NEXT VERSION
	
	private function hasCommandAccess(CommandSender $sender) {
		if ($sender->getName () == "CONSOLE") {
			return true;
		} elseif ($sender->isOp ()) {
			return true;
		}
		return false;
	}
	
	/**
	 * Logging util function
	 *
	 * @param unknown $msg        	
	 */
	private function log($msg) {
		$this->pgin->getLogger ()->info ( $msg );
	}
}

// Code Color 
//BLACK = "§0";

// DARK_BLUE = "§1";

// DARK_GREEN = "§2";

// DARK_AQUA = "§3";

// DARK_RED = "§4";

// DARK_PURPLE = "§5";

// GOLD = "§6";

// GRAY = "§7";

// DARK_GRAY = "§8";

// BLUE = "§9";

// GREEN = "§a";

// AQUA = "§b";

// RED = "§c";

// LIGHT_PURPLE = "§d";

// YELLOW = "§e";

// WHITE = "§f";
