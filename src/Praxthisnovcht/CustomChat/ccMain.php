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


// Support Money
########################################
use MassiveEconomy\MassiveEconomyAPI;  ##
########################################


####################################
# Addon For CustomChat                      ##
use Praxthisnovcht\KillChat\KillChat;      ##
####################################

/**
 * Main // CustomChat 1.3.0 Release  
 */
class ccMain extends PluginBase implements CommandExecutor {

	public $pos_display_flag = 0;
	
	
	private $playerstats_deaths;
	private $playerstats_break;
	private $playerstats_pose;
	private $playerstats_leave;
	private $playerstats_kick;
	private $playerstats_join;
	private $playerstats_drop;
	private $factionspro;
	private $pureperms;
	private $economyjob;
	
	private $kc_deaths;
	private $kc_kills;
	private $me_money;
	
	public $swCommand;
	
	
	/**
	 * OnLoad
	 * (non-PHPdoc)
	 * 
	 * @see \pocketmine\plugin\PluginBase::onLoad()
	 */
	public function onLoad() {
		$this->getLogger()->info(TextFormat::YELLOW . "Loading CustomChat v_1.3.0 by Praxthisnovcht");
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
		
		// Use PlayerStats By 
        //   ╔══╗╔══╗╔═══╗╔╗╔╗╔══╗╔════╗╔╗╔╗╔╗──╔╗
        //   ╚═╗║║╔═╝║╔═╗║║║║║║╔═╝╚═╗╔═╝║║║║║║──║║
        //   ──║╚╝║──║╚═╝║║╚╝║║║────║║──║║║║║╚╗╔╝║
        //   ──║╔╗║──║╔══╝╚═╗║║║────║║──║║║║║╔╗╔╗║
        //   ╔═╝║║╚═╗║║────╔╝║║╚═╗──║║──║╚╝║║║╚╝║║
        //   ╚══╝╚══╝╚╝────╚═╝╚══╝──╚╝──╚══╝╚╝──╚╝		
		if(!$this->getServer()->getPluginManager()->getPlugin("PlayerStats") == false) {
			$playerstats_deaths = Server::getInstance()->getPluginManager()->getPlugin("PlayerStats")->getDeaths($player);
			// Counter deaths 
			$playerstats_break = Server::getInstance()->getPluginManager()->getPlugin("PlayerStats")->getBreaks($player);
			// Counter Break Block
			$playerstats_pose = Server::getInstance()->getPluginManager()->getPlugin("PlayerStats")->getPlaces($player);
			// Counter Pose Block
			$playerstats_leave = Server::getInstance()->getPluginManager()->getPlugin("PlayerStats")->getDeaths($player);
			// Counter Leave Games
			$playerstats_kick = Server::getInstance()->getPluginManager()->getPlugin("PlayerStats")->getKicked($player);
			// Counter Kicked Games
			$playerstats_join = Server::getInstance()->getPluginManager()->getPlugin("PlayerStats")->getJoins($player);
			// Counter Join Games
			$playerstats_drop = Server::getInstance()->getPluginManager()->getPlugin("PlayerStats")->getDrops($player);
			// Counter Drops
			$this->log ( TextFormat::GREEN . "- CustomChat - Loaded With PlayerStats !" );
		}
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
		if(!$this->getServer()->getPluginManager()->getPlugin("PurePerms") == false) {
			$this->pureperms = $this->getServer()->getPluginManager()->getPlugin("PurePerms ");
			$this->log ( TextFormat::GREEN . "- CustomChat - Loaded With PurePerms !" );
		}
		
		if(!$this->getServer()->getPluginManager()->getPlugin("KillChat") == false) {
			$kc_kills = Server::getInstance()->getPluginManager()->getPlugin("KillChat")->getKills("Player Name");		
			$kc_deaths = Server::getInstance()->getPluginManager()->getPlugin("KillChat")->getDeaths("Player Name");
			// KillChat Addon
			$this->log ( TextFormat::YELLOW . "- CustomChat - Loaded With KillChat [Addon For Only CustomChat] !" );
		} 
		if(!$this->getServer()->getPluginManager()->getPlugin("MassiveEconomy") == false) {
			$me_money = Server::getInstance()->getPluginManager()->getPlugin("PlayerStats")->getMoney("Player Name");
			$this->log ( TextFormat::GREEN . "- CustomChat - Loaded With MassiveEconomy !" );
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
			$this->getConfig ()->set ( "chat-format", "{WORLD_NAME}:[{FACTION}][{PurePerms}][{PREFIX}]<{DISPLAY_NAME}> {MESSAGE}" );
		}
		if (! $this->getConfig ()->get ( "CustomChat options" )) {
			$this->getConfig ()->set ( "CustomChat options", "{Kills} | {Deaths} | {Money}" );
		}
		if (! $this->getConfig ()->get ( "CustomJoin" )) {
			$this->getConfig ()->set ( "@player joined the server ! Isaku is Awesome" );
		}
		if (! $this->getConfig ()->get ( "CustomLeave" )) {
			$this->getConfig ()->set ( "@player leave the server ! Isaku is Awesome" );
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
