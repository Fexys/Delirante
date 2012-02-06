<?php
// Includo la classe SmartIRC che è presente nei repo di Debian ma è documentata da cani
include_once( 'SmartIRC.php' );
include_once('SmartIRC/irccommands.php');
include_once('SmartIRC/messagehandler.php'); //queste funzioni sono extra ma non funzionano

//includo il file con le bio
include( 'bio.php' );

// Variabile che contiene il nome del chan
$chan = '#DeliriNotturni';

//$chan = '#delirinotturni2';

// Classe con le funzioni del ilDelirante
class Delirio {
	//Variabile che conterrà i vari op
	var $op = array();
	//Variabile che conterrà i vari insulti
	var $insulti = array();
	//Variabile che blocca insulto personalizzato
	var $stop = true;
	//Variabile per il conteggio inalbera
	var $inalbera = 0;
	//Settiamo le varie proprietà del bot
	function setVar( ) {
		$this->op = array_map('rtrim',file( 'op.php',FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES));
		$this->insulti = array_map('rtrim',file( 'insulti.php',FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES));
	}
	//Rimuove elementi da array tramite il valore ***uso Interno***
	function remove_item_by_value($array, $val = '') {
		if (!in_array($val, $array)) return $array;
		foreach($array as $key => $value) {
			if ($value == $val) unset($array[$key]);
		}
		return $array;
	}
	//Scrive sul chan ***uso Interno***
	function scrivi_messaggio(&$irc, &$data, $message) {
		$irc->message( SMARTIRC_TYPE_CHANNEL, $data->channel, $message);
	}
	//Verifica Funzione esistente
	function check( &$irc, &$data ) {
		if($data->messageex[0][0]=='!'&&!in_array(str_replace("!","",$data->messageex[0]), get_class_methods($this))) {
			$this->scrivi_messaggio($irc, $data,'Non conosco questo comando, quindi fanculizzati da solo');
		}
	}
	//Disattiva/Attiva l'insulto personalizzato
	function stop( &$irc, &$data ) {
		if( in_array($data->nick, $this->op) ) {
			if($this->stop==false){$this->stop=true;}else{$this->stop=false;}
		} else {
			$this->scrivi_messaggio($irc, $data,'Chi ti credi di essere per darmi questi comandi?????');
		}
	}
	//Saluto chi entra gentilmente
	function onjoin_greeting( &$irc, &$data ) {
		if( $data->nick == $irc->_nick ) {return;}
		$this->scrivi_messaggio($irc, $data, 'Ciao '.$data->nick );
	}
	//Spengo il bot
	function shutdown( &$irc, &$data ) {
		if( in_array($data->nick, $this->op) ) {
			$irc->quit('Addio mondo crudele!');
		} else {
			$this->scrivi_messaggio($irc, $data, 'Chi ti credi di essere per darmi questi comandi?????');
		}
	}
	//Kick
	function kick( &$irc, &$data ) {
		if( in_array( $data->nick, $this->op ) ) {
			if( isset( $data->messageex[1], $data->messageex[2] ) ) {
				$nickname = $data->messageex[1];
				$reason   = $data->messageex[2];
				$channel  = $data->channel;
				$irc->kick( $channel, $nickname, $reason );
			}
			else {
				$irc->message( $data->type, $data->nick, 'Invalid Parameter' );
				$irc->message( $data->type, $data->nick, 'use: !kick $nick' );
			}
		}
	}
	//Se il bot è kikato rientra
	function kick_response( &$irc, &$data ) {
		//if bot is kicked

		if( $data->nick == $irc->_nick ) {
			$irc->join( array( $chan ) );
			$this->scrivi_messaggio($irc, $data, 'dont kick me... please' );
			Return;
		}
	}
	//modalità del chan
	function mode( $channel, $newmode = null, $priority = SMARTIRC_MEDIUM ) {
		if( $newmode !== null ) {
			$irc->_send( 'MODE '.$channel.' '.$newmode, $priority );
		}
		else {
			$irc->_send( 'MODE '.$channel, $priority );
		}
	}
	//Devoice
	function devoice( &$irc, &$data ) {
		if( in_array( $data->nick, $this->op ) ) {
			if( isset( $data->messageex[1] ) ) {
				$nickname = $data->messageex[1];
				$channel = $data->channel;
				$irc->devoice( $channel, $nickname );
			}
		}
	}
	//Op
	function op( &$irc, &$data ) {
		if( in_array( $data->nick, $this->op ) ) {
			if( isset( $data->messageex[1] ) ) {
				$nickname = $data->messageex[1];
				$channel = $data->channel;
				$irc->op( $channel, $nickname );
			}
		}
	}
	//Deop
	function deop( &$irc, &$data ) {
		if( in_array( $data->nick, $this->op ) ) {
			if( isset( $data->messageex[1] ) ) {
				$nickname = $data->messageex[1];
				$channel = $data->channel;
				$irc->deop( $channel, $nickname );
			}
		}
	}
	//Join
	function join( &$irc, &$data ) {
		if( in_array( $data->nick, $this->op ) ) {
			if( isset( $data->messageex[1] ) ) {
				$channel = $data->messageex[1];
				$irc->join( $channel );
			}
		}
	}
	//Part ***Che cavolo è???***
	function part( &$irc, &$data ) {
		if( in_array( $data->nick, $this->op ) ) {
			if( isset( $data->messageex[1] ) ) {
				$channel = $data->messageex[1];
				$irc->part( $channel );
			}
		}
	}
	//Rejoin
	function rejoin( &$irc, &$data ) {
		if( in_array( $data->nick, $this->op ) ) {
			if( isset( $data->messageex[1] ) ) {
				$channel = $data->messageex[1];
				$irc->part( $channel );
				$irc->join( $channel );
			}
		}
	}
	//Ban ***deve essere insultante***
	function ban( &$irc, &$data ) {
		if( in_array( $data->nick, $this->op ) ) {
			if( isset( $data->messageex[1] ) ) {
				$hostmask = $data->messageex[1];
				$channel = $data->channel;
				$irc->ban( $channel, $hostmask );
			}
			else {
				$irc->message( $data->type, $data->nick, 'Invalid Parameter' );
				$irc->message( $data->type, $data->nick, 'use: !ban $nick' );
			}
		}
	}
	//Perchè cambiare il nick a ildelirante?
	function nick( &$irc, &$data ) {
		if( in_array( $data->nick, $this->op ) ) {
			if( isset( $data->messageex[1] ) ) {
				$newnick = $data->messageex[1];
				$channel = $data->channel;
				$irc->changeNick( $newnick );
			}
		}
	}
	//Saluta molto educatamente
	function saluta( &$irc, &$data ) {
			if( isset( $data->messageex[1] ) ) {
				$poggio=$data->messageex;
				unset($poggio[0]);
				$this->scrivi_messaggio($irc, $data,'Fottiti '.implode('',$poggio) );
			}
	}
	//Stampa la bio dell'utente o del parametro passato
	function whoami( &$irc, &$data ) {
		global $bio;
			if( isset( $data->messageex[1] ) && isset($bio[$data->messageex[1]]['bio'])) {
				$this->scrivi_messaggio($irc, $data, $data->messageex[1].': '.$bio[$data->messageex[1]]['bio'] );
			} elseif(!isset($data->messageex[1]) && isset($bio[$data->nick]['bio'])) {
				$this->scrivi_messaggio($irc, $data,$data->nick.': '.$bio[$data->nick]['bio'] );
			}else{
				$this->scrivi_messaggio($irc, $data,'Utente non inserito nel sistema. Tentativo di intrusione rilevato!' );
			}
	}
	//Lista dei comandi
	function help( &$irc, &$data ) {
		$this->scrivi_messaggio($irc, $data,'Comandi da cazzeggio: !saluta, !whoami, !versione, !who, !insulta, !dado, !inalbera' );
		$this->scrivi_messaggio($irc, $data,'Tool: !help, !versione, !github, !ls, !paste, !google, !deb, !rpm' );
	}
	//Versione
	function versione( &$irc, &$data ) {
		$this->scrivi_messaggio($irc, $data,'Sono cavoli miei... 0.0.9' );
	}
	//Link su Github del bot
	function github( &$irc, &$data ) {
		$this->scrivi_messaggio($irc, $data, 'Sorgenti: https://github.com/Mte90/Delirante' );
	}
	//Utenti nel database
	function who( &$irc, &$data ) {
		global $bio_tot;
		$this->scrivi_messaggio($irc, $data,count($bio_tot).' Utenti nel database: '.implode(', ', $bio_tot) );
	}
	//Insulta
	function insulta( &$irc, &$data ) {
		if( isset( $data->messageex[1] )) {
			if($data->messageex[1]=='-c') {
				$this->scrivi_messaggio($irc, $data,count($this->insulti).' insulti nel sistema');
			}elseif(is_numeric($data->messageex[1])&&$data->messageex[1]<count($this->insulti)&&isset($data->messageex[2])) {
				$this->scrivi_messaggio($irc, $data,$this->insulti[$data->messageex[1]].' '.$data->messageex[2]);
			}elseif(is_numeric($data->messageex[1])&&$data->messageex[1]<count($this->insulti)) {
				$this->scrivi_messaggio($irc, $data, $this->insulti[$data->messageex[1]]);
			} else {
				if(in_array($data->messageex[1], $irc->_updateIrcUser($data))) {
					$this->scrivi_messaggio($irc, $data, $data->messageex[1].' '.$this->insulti[array_rand($this->insulti)]);
				}
			}
		} else {
			$this->scrivi_messaggio($irc, $data,'-c Mostra il numero di insulti, !insulta NUMERO[non obbligatorio] NICK');
		}
	}
	//Insulto personalizzato a citazione
	function insulto( &$irc, &$data ) {
	if(rand(0, 20)==1&&$this->stop){$this->scrivi_messaggio($irc, $data,"Mi servono molti insulti personalizzati!");}
		if($this->stop && $data->messageex[0][0]!='!'){
		$messaggio=implode(' ',$data->messageex);
		global $bio;
			foreach($irc->_updateIrcUser($data) as $item){
				if(strstr($messaggio,$item)){
					if($item=='ilDelirante'){
						$this->scrivi_messaggio($irc, $data,$bio[$item]['insulto'][array_rand($bio[$item]['insulto'])].' '.$data->nick);
					}elseif(isset($bio[$item]['insulto'][0])){
						$this->scrivi_messaggio($irc, $data,$bio[$item]['insulto'][array_rand($bio[$item]['insulto'])].' '.$item);
					}
				}
			}
		}
	}
	//Cambia il nick attuale??
	function changeNick( $newnick, $priority = SMARTIRC_MEDIUM ) {
		$this->_send( 'NICK '.$newnick, $priority );
		$this->_nick = $newnick;
	}
	//Elenco Utenti
	function ls( &$irc, &$data ) {
		$nicklist=$this->remove_item_by_value($irc->_updateIrcUser($data, "ChanServ"));
		$this->scrivi_messaggio($irc, $data,count($nicklist).' Utenti nel sistema: '.implode(', ', $nicklist));
	}
	//Pastebin vari
	function paste( &$irc, &$data ) {
		$this->scrivi_messaggio($irc, $data,'http://pastebin.com , http://paste.kde.org , http://nopaste.voric.com');
	}
	//Google
	function google( &$irc, &$data ) {
		$this->scrivi_messaggio($irc, $data,'http://www.google.it/search?q='.str_replace("!google ","",implode(' ',$data->messageex)));
	}
	//DEB
	function deb( &$irc, &$data ) {
		if($data->messageex[1]=='-ubu') {
			$this->scrivi_messaggio($irc, $data,'http://packages.ubuntu.com/search?keywords='.$data->messageex[2]);
		}elseif(!isset($data->messageex[1])) {
			$this->scrivi_messaggio($irc, $data,'!deb -ubu(per usare Ubuntu altrimenti Debian)');
		}else{
			$this->scrivi_messaggio($irc, $data,'http://packages.debian.org/search?keywords='.$data->messageex[1]);
		}
	}
	//RPM
	function rpm( &$irc, &$data ) {
		$this->scrivi_messaggio($irc, $data,'http://software.opensuse.org/search?q='.$data->messageex[1].'&baseproject=ALL');
	}
	//Tira il dado
	function dado( &$irc, &$data ) {
		$this->scrivi_messaggio($irc, $data,'Ti devo dare '.rand(1, 6).' calci, preparati '.$data->nick);
	}
	//Inalbera
	function inalbera( &$irc, &$data ) {
		$this->scrivi_messaggio($irc, $data,$data->messageex[1].' '.$this->insulti[array_rand($this->insulti)]);
		$this->scrivi_messaggio($irc, $data,$data->messageex[1].' '.$this->insulti[array_rand($this->insulti)]);
		$this->scrivi_messaggio($irc, $data,$data->messageex[1].' '.$this->insulti[array_rand($this->insulti)]);
		$this->scrivi_messaggio($irc, $data,$data->messageex[1].' '.$this->insulti[array_rand($this->insulti)]);
		$this->scrivi_messaggio($irc, $data,$data->messageex[1].' '.$this->insulti[array_rand($this->insulti)]);
	}
	/*End the Bot-class*/
}
//Impostiamo e facciamo partire il bot
$bot = new Delirio( );
$bot->setVar( );
$irc = new Net_SmartIRC( );
//$irc->startBenchmark();
//$irc->setDebug( SMARTIRC_DEBUG_ALL );
//$irc->setBenchmark(TRUE);
$irc->setUseSockets( TRUE );
$irc->setUserSyncing( TRUE );
$irc->setChannelSyncing( TRUE );
$irc->setSenddelay(600);
//Configuriamo i vari comandi con le funzioni
$irc->connect( 'irc.freenode.org', 6667 );
$irc->registerActionhandler( SMARTIRC_TYPE_JOIN, '.*', $bot, 'onjoin_greeting' );
$irc->registerActionhandler( SMARTIRC_TYPE_KICK, '.*', $bot, 'kick_response' );
$irc->registerActionhandler( SMARTIRC_TYPE_CHANNEL, '.*', $bot, 'insulto' );
$irc->registerActionhandler( SMARTIRC_TYPE_CHANNEL, '^!*', $bot, 'check' );
$irc->registerActionhandler( SMARTIRC_TYPE_CHANNEL, '^!shutdown$', $bot, 'shutdown' );
$irc->registerActionhandler( SMARTIRC_TYPE_CHANNEL, '^!kick', $bot, 'kick' );
$irc->registerActionhandler( SMARTIRC_TYPE_CHANNEL, '^!voice', $bot, 'voice' );
$irc->registerActionhandler( SMARTIRC_TYPE_CHANNEL, '^!devoice', $bot, 'devoice' );
$irc->registerActionhandler( SMARTIRC_TYPE_CHANNEL, '^!op', $bot, 'op' );
$irc->registerActionhandler( SMARTIRC_TYPE_CHANNEL, '^!deop', $bot, 'deop' );
$irc->registerActionhandler( SMARTIRC_TYPE_CHANNEL, '^!join', $bot, 'join' );
$irc->registerActionhandler( SMARTIRC_TYPE_CHANNEL, '^!part', $bot, 'part' );
$irc->registerActionhandler( SMARTIRC_TYPE_CHANNEL, '^!rejoin', $bot, 'rejoin' );
$irc->registerActionhandler( SMARTIRC_TYPE_CHANNEL, '^!rm', $bot, 'ban' );
$irc->registerActionhandler( SMARTIRC_TYPE_CHANNEL, '^!nick', $bot, 'nick' );
$irc->registerActionhandler( SMARTIRC_TYPE_CHANNEL, '^!saluta', $bot, 'saluta' );
$irc->registerActionhandler( SMARTIRC_TYPE_CHANNEL, '^!help$', $bot, 'help' );
$irc->registerActionhandler( SMARTIRC_TYPE_CHANNEL, '^!who\s*$', $bot, 'who' );
$irc->registerActionhandler( SMARTIRC_TYPE_CHANNEL, '^!whoami', $bot, 'whoami' );
$irc->registerActionhandler( SMARTIRC_TYPE_CHANNEL, '^!versione$', $bot, 'versione' );
$irc->registerActionhandler( SMARTIRC_TYPE_CHANNEL, '^!github$', $bot, 'github' );
$irc->registerActionhandler( SMARTIRC_TYPE_CHANNEL, '^!insulta', $bot, 'insulta' );
$irc->registerActionhandler( SMARTIRC_TYPE_CHANNEL, '^!ls', $bot, 'ls' );
$irc->registerActionhandler( SMARTIRC_TYPE_CHANNEL, '^!stop', $bot, 'stop' );
$irc->registerActionhandler( SMARTIRC_TYPE_CHANNEL, '^!paste', $bot, 'paste' );
$irc->registerActionhandler( SMARTIRC_TYPE_CHANNEL, '^!google', $bot, 'google' );
$irc->registerActionhandler( SMARTIRC_TYPE_CHANNEL, '^!deb', $bot, 'deb' );
$irc->registerActionhandler( SMARTIRC_TYPE_CHANNEL, '^!rpm', $bot, 'rpm' );
$irc->registerActionhandler( SMARTIRC_TYPE_CHANNEL, '^!dado', $bot, 'dado' );
$irc->registerActionhandler( SMARTIRC_TYPE_CHANNEL, '^!inalbera', $bot, 'inalbera' );

// nick , nome , realname , ident, boh
$irc->login( 'ilDelirante', 'ilDelirante'.'delirio', 8, 'delirio', '' );
$irc->join( $chan );
$irc->listen( );
$irc->disconnect( );
?>