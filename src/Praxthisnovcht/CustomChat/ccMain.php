<?php

namespace Praxthisnovcht\CustomChat;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\CommandExecutor;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\level\Position;
use pocketmine\level\Level;
use pocketmine\level\Explosion;
use pocketmine\event\block\BlockEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\entity\EntityMoveEvent;
use pocketmine\event\entity\EntityMotionEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\block\SignChangeEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\Listener;
use pocketmine\math\Vector3 as Vector3;
use pocketmine\math\Vector2 as Vector2;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\network\protocol\AddMobPacket;
use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\network\protocol\UpdateBlockPacket;
use pocketmine\block\Block;
use pocketmine\block\WallSign;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\network\protocol\DataPacket;
use pocketmine\network\protocol\Info;
use pocketmine\network\protocol\LoginPacket;
use pocketmine\level\generator\Generator;

/**
 * Main // CustomChat 1.1.7 Release  
 */
class ccMain extends PluginBase implements CommandExecutor {

	public $pos_display_flag = 0;
	private $factionspro;
	private $pureperms;
	private $economyjob;
	public $swCommand;
	
	/**
	 * OnLoad
	 * (non-PHPdoc)
	 * 
	 * @see \pocketmine\plugin\PluginBase::onLoad()
	 */
	public function onLoad() {
		$this->swCommand = new ccCommand ( $this );
		
	}
	
	/**
	 * OnEnable
	 *
	 * (non-PHPdoc)
	 * 
	 * @see \pocketmine\plugin\PluginBase::onEnable()
	 */
	public function onEnable() {
		$this->enabled = true;
		// Use FactionsPro by Tethered_
		if(!$this->getServer()->getPluginManager()->getPlugin("FactionsPro") == false) {
			$this->factionspro = $this->getServer()->getPluginManager()->getPlugin("FactionsPro");
			$this->log ( TextFormat::GREEN . "- CustomChat - Loaded With FactionsPro!" );
		}	
		// Use EconomyJob by Onebone	
		if(!$this->getServer()->getPluginManager()->getPlugin("EconomyJob") == false) {
			$this->economyjob = $this->getServer()->getPluginManager()->getPlugin("EconomyJob");
			$this->log ( TextFormat::GREEN . "- CustomChat - Loaded With EconomyJob!" );
		}	
		// Use PurePerms by 64FF00	
		if(!$this->getServer()->getPluginManager()->getPlugin("PurePerms ") == false) {
			$this->pureperms = $this->getServer()->getPluginManager()->getPlugin("PurePerms ");
			$this->log ( TextFormat::GREEN . "- CustomChat - Loaded With PurePerms !" );
		}
		$this->getServer()->getPluginManager()->registerEvents(new ccListener($this), $this);
		$this->log ( TextFormat::GREEN . "- CustomChat - Enabled!" );
		$this->loadConfig ();
	}
	
	/**
	 * OnDisable
	 * (non-PHPdoc)
	 * 
	 * @see \pocketmine\plugin\PluginBase::onDisable()
	 */
	public function onDisable() {
		$this->log ( TextFormat::RED . "CustomChat - Disabled" );
		$this->enabled = false;
	}
	
	/**
	 * OnCommand
	 * (non-PHPdoc)
	 * 
	 * @see \pocketmine\plugin\PluginBase::onCommand()
	 */
	public function onCommand(CommandSender $sender, Command $command, $label, array $args) {
		$this->swCommand->onCommand ( $sender, $command, $label, $args );
	}
	
	public function loadConfig() {
		$this->saveDefaultConfig();
		$this->fixConfigData ();
	}
// 	public function reloadConfig() {
// 		$this->reloadConfig ();
// 		$this->loadConfig ();
// 	}
	public function fixConfigData() {
//		  It is completely useless to have chat-format '{PurePerms}' and '{PREFIX}'.
//                We must put a single to avoid confusion.
//                So use either:
//                "{WORLD_NAME}:<{JOB}>[{FACTION}][{PurePerms}]<{DISPLAY_NAME}> {MESSAGE}"
//                oR
//                "{WORLD_NAME}:<{JOB}>[{FACTION}][{PREFIX}<{DISPLAY_NAME}> {MESSAGE}"
		if (! $this->getConfig ()->get ( "chat-format" )) {
			$this->getConfig ()->set ( "chat-format", "{WORLD_NAME}:<{JOB}>[{FACTION}][{PurePerms}][{PREFIX}]<{DISPLAY_NAME}> {MESSAGE}" );
		}
		if (! $this->getConfig ()->get ( "if-player-has-no-faction" )) {
			$this->getConfig ()->set ( "if-player-has-no-faction", "NoFaction" );
		}
		if (! $this->getConfig ()->get ( "if-player-has-no-job" )) {
			$this->getConfig ()->set ( "if-player-has-no-job", "unemployed" );
		}
		if (! $this->getConfig ()->get ( "Enable Support Money" )) {
			$this->getConfig ()->set ( "Enable Support Money", false );	
		}
		if (! $this->getConfig ()->get ( "enable-formatter" )) {
			$this->getConfig ()->set ( "enable-formatter", true );
		}
		if (! $this->getConfig ()->get ( "disablechat" )) {
			$this->getConfig ()->set ( "disablechat", false );
		}
		if (! $this->getConfig ()->get ( "default-player-prefix" )) {
			$this->getConfig ()->set ( "default-player-prefix", "Default" );
		}
		$this->getConfig()->save();
	}
	
	public function formatterPlayerDisplayName(Player $p) {
		$prefix=null;
		$playerPrefix = $this->getConfig ()->get ( $p->getName ().".prefix" );
		if ($playerPrefix != null) {
			$prefix = $playerPrefix;
		} else {
			//use default prefix
			$prefix = $this->getConfig ()->get ( "default-player-prefix");
		}
		//check if player has nick name
		$nick = $this->getConfig ()->get ( $p->getName().".nick");
		if ($nick!=null && $prefix!=null) {
			$p->setNameTag( $prefix . ":" . $nick );
			return;
		}
		if ($nick!=null && $prefix==null) {
			$p->setNameTag($nick );
			return;
		}
		if ($nick==null && $prefix!=null) {
			$p->setNameTag($prefix . ":".$p->getName());
			return;
		}
		//default to regular name
		$p->setNameTag($p->getName());
		return;
	}
	
	/**
	 * Logging util function
	 *
	 * @param unknown $msg        	
	 */
	private function log($msg) {
		$this->getLogger ()->info ( $msg );
	}
}
