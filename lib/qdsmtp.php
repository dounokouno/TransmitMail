<?php
/**
 * Qdsmtp ver 0.2.0a
 * SMTP Talker
 *
 * PHP versions 4 and 5 (PHP4.3 upper)
 *
 * Copyright 2008, Spok in japan , tokyo
 * hal456.net/qdmail    :  http://hal456.net/qdsmtp/
 * & CPA-LAB/Technical  :  http://www.cpa-lab.com/tech/
 * Licensed under The MIT License License
 *
 * @copyright		Copyright 2008, Spok.
 * @link			http://hal456.net/qdsmtp/
 * @version			0.2.0a
 * @lastmodified	2008-10-25
 * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
 * 
 * Qdsmtp is SMTP Taler library ,easy , quickly , usefull .
 * Copyright (C) 2008  spok 
*/
if ( defined('CAKE_CORE_INCLUDE_PATH') || defined('CAKE')) {
	class QdsmtpBranch extends Object{}
}else{
	class QdsmtpBranch{}
}

class QdsmtpError extends QdsmtpBranch{

	var $name = 'QdsmtpError';
	var $error_display		= true;
	var $errorlog_level		= 0;
	var $log_level			= 0;
	var $error				= array() ;
	var $error_stack		= array();
	var $log_LFC			= "\r\n";
	var $log_append			= 'a';
	var $errorlog_append	= 'a';
	var $log_filename		='qdsmtp.log';
	var $errorlog_filename	='qdsmtp_error.log';
	var $log_dateformat		= 'Y-m-d H:i:s';
	var $error_ignore		= false;

	function errorRender( $error = null , $lf = null , $display = true ){
		if( is_null( $error ) ){
			$error = $this->error;
		}
		if( is_null( $lf ) ){
			$lf = $this->log_LFC ;
		}
		if( !is_array( $error ) ){
			$error = array( $error );
		}
		$out = null ;
		foreach($error as $mes){
			$out .= $this->name . ' error: ' . trim( $mes ) . $lf ;
		}
		if( $this->error_display && $display ){
			$_out = str_replace( $lf ,'<br>' . $lf , $out );
			echo  $_out ;
		}
		return $out ;
	}

	function errorGather( $message = null , $line = null){

		if( !is_null( $message ) && !$this->error_ignore){
			if( !is_null( $line ) ){
				$message .= ' line -> '.$line;
			}
			$this->error[] = $message ;
		}elseif( 0 === count( $this->error ) ){
			return true;
		}elseif( $this->error_ignore ){
			return false;
		}

		$er = $this->errorRender();
		$this->error_stack = array_merge( $this->error_stack , $this->error );
		$this->error = array();
		if( !$this->logWrite( 'error' ,  $er )){
			$this->error_stack = array_merge( $this->error_stack , $this->error );
		}
		return false;
	}

	function logWrite( $type , $message ){
		$tp = ('error' == $type) ? false:true;
		$level		=	$tp ? $this->log_level:$this->errorlog_level;
		if( 0 == $level ){
			return true;
		}
		$filename	=	$tp ? $this->log_filename:$this->errorlog_filename;
		$ap			=	$tp ? $this->log_append:$this->errorlog_append;
		$fp = fopen( $filename , $ap );
		if( !is_resource( $fp ) ){
			$this->error[]='file open error at logWrite() line->'.__LINE__;
			return false;
		}
		$spacer = $tp ? $this->log_LFC : $this->log_LFC ;
		fwrite( $fp , 
			date( $this->log_dateformat )
			. $spacer
			. trim( $message )
			. $this->log_LFC
		);
		fclose( $fp ) ;
		return true ;
	}
	function log(){
		$mes = null;
		foreach($this->smtp_log as $line){
			$mes .= trim( $line ) . $this->log_LFC;
		}
		$this->logWrite( null ,$mes );
		$this->smtp_log = array();
	}
	function logFilename( $data = null ){
		if( is_null( $data ) ){
			return $this->log_filename;
		}
		if( is_string( $data ) ){
			$this->log_filename = $data;
			return $this->errorGather();
		}else{
			return $this->errorGather('Data specified error',__LINE__);
		}
	}
	function errorlogFilename( $data = null ){
		if( is_null( $data ) ){
			return $this->errorlog_filename;
		}
		if( is_string( $data ) ){
			$this->errorlog_filename = $data;
			return $this->errorGather();
		}else{
			return $this->errorGather('Data specified error',__LINE__);
		}
	}
}

class QdsmtpBase extends QdsmtpError{

	var $name		= 'QdSmtp';
	var $smtpLFC	="\r\n";
	var $smtp_param	= array(
		'HOST'		=> null,
		'FROM'		=> null,
		'USER'		=> null,
		'PASS'		=> null,
		'PORT'		=> 25,
		'PROTOCOL'	=> array(
			'SMTP_AUTH','POP_BEFORE','SMTP'
		),
		'POP_SERVER'=> null,
		'POP_USER'	=> null,
		'POP_PASS'	=> null,
		'CONTINUE'	=> null,
	);
	var $protocol_def	='SMTP';
	var $smtp_log		= array();
	var $smtp_auth_kind	= array('PLAIN');
//	var $smtp_auth_kind	= array('CRAM-MD5','DIGEST-MD5','LOGIN','PLAIN');
	var $data			= null;
	var $continue		= false;
	var $auto_kind		= true;
	var $rcpt			= array();
	var $rcpt_stack		= array();
	var $rcpt_undone	= array();
	var $rcpt_undone_stack = array();
	var $smtp_limit		= 1000;
	var $sock			= null;
	var $already_auth	= false;
	// POP3
	var $pop3_time_file		= 'qdsmtp.time';
	var $pop3_use_file		= true;
	var $pop3_connect_start	= null;
	var $pop3_valid_minute	= 10;
	var $time_out			= 3 ;
	var $always_notify_success = false;

	function QdsmtpBase( $param = null ){
		if( !is_null( $param[0] ) && is_bool( $param[0] ) ){
			$this->continue = $continue;
		}
		if( is_array( $param[0] ) ){
			$this->server( $param[0] );
		}
	}

	//-----------------------------------------
	// User Interface except POP3 
	//-----------------------------------------
	function server( $param = null ){
		if( is_null( $param ) ){
			return $this->smtp_param;
		}elseif( !is_array( $param ) ){
			return $this->errorGather('Error Parameter type',__LINE__);
		}
		$param = array_change_key_case( $param , CASE_UPPER );
		if( isset( $param['PROTOCOL'] ) && is_array($param['PROTOCOL'] ) ){
			$param['PROTOCOL'] = array_change_key_case( $param['PROTOCOL'] , CASE_UPPER );
		}
		if( isset( $param['ALWAYS_NOTIFY'] ) && is_bool($param['ALWAYS_NOTIFY']) ){
			$this->always_notify_success = $param['ALWAYS_NOTIFY'];
		}
		$this->smtp_param = array_merge( $this->smtp_param , $param );
		if( isset( $this->smtp_param['CONTINUE'] ) ){
			$this->continue = $this->smtp_param['CONTINUE'];
		}
		return true;
	}
	function data( $data = null ){
		if( is_null( $data ) ){
			return $this->data;
		}
		if( is_string( $data ) ){
			$this->data = $data;
			return $this->errorGather();
		}else{
			return $this->errorGather('Data specified error',__LINE__);
		}
	}
	function to( $recip , $add = false ){
		return $this->recipient( $recip );
	}
	function recipient( $recip , $add = false ){
		if( !is_array( $recip ) ){
			$recip = array( $recip );
		}
		if( $add ){
			$this->recipient = array_merge( $this->recipient , $recip );
			$this->recipient = array_unique( $this->recipient );
		}else{
			$this->recipient = $recip;
		}
		return $this->errorGather();
	}
	function addRecipient( $recip ){
		return $this->recipient( $recip , true );
	}
	function addto( $recip ){
		return $this->addRecipient( $recip );
	}
	function done( $stack = false ){
		if( $stack ){
			return $this->rcpt_stack;
		}else{
			return $this->rcpt;
		}
	}
	function undone( $stack = false ){
		if( $stack ){
			return $this->rcpt_undone_stack;
		}else{
			return $this->rcpt_undone;
		}
	}
	function continueConnect( $bool = null ){
		if( is_null( $bool ) ){
			return $this->continue;
		}
		if( is_bool( $bool ) ){
			$this->continue = $bool;
			return $this->errorGather();
		}else{
			return $this->errorGather('Connection Continue Mode specifed error',__LINE__);
		}
	}
	function timeOut( $sec = null ){
		if(is_null($sec)){
			return $this->time_out;
		}
		if( is_numeric( $sec ) ){
			$this->time_out = $sec;
			return $this->errorGather();
		}else{
			return $this->errorGather(__FUNCTION__.'  specifed error',__LINE__);
		}
	}
	function errorlogLevel( $num = null ){
		if( is_null( $num ) ){
			return $this->errorlog_level;
		}
		if( is_numeric( $num ) ){
			$this->errorlog_level = $num;
			return $this->errorGather();
		}else{
			return $this->errorGather(__FUNCTION__.' specifed error',__LINE__);
		}
	}
	function logLevel( $num = null ){
		if( is_null( $num ) ){
			return $this->log_level;
		}
		if( is_numeric( $num ) ){
			$this->log_level = $num;
			return $this->errorGather();
		}else{
			return $this->errorGather(__FUNCTION__.' specifed error',__LINE__);
		}
	}
	function alwaysNotifySuccess( $bool = null ){
		if(is_null($bool)){
			return $this->always_notify_success;
		}
		if(is_bool($bool)){
			$this->always_notify_success = $bool;
			return true;
		}else{
			return false;
		}
	}
	//------------------------------------------
	// Sending Method
	//------------------------------------------
	// Qdsmtp ignore $option parameter
	function mail( $to , $subject , $message , $header= null , $option = null ){
		$this->makeData( $to , $subject , $message , $header , $option );
		return $this->send();
	}

	function send( $data = null ){

		if( !is_array($this->smtp_param['PROTOCOL'])){
			$fg = $this->sendBase( $data , $this->smtp_param['PROTOCOL'] );
			$this->log();
			return $fg;
		}
		$stack = array( $this->error_display , $this->errorlog_level );
		$this->error_display = false;
		$this->errorlog_level= 0;
		$ret = false;
		foreach($this->smtp_param['PROTOCOL'] as $protocol ){
			if( $this->sendBase( $data , $protocol ) ){
				$ret = true;
				$this->error_stack = array();
				break;
			}
		}
		list( $this->error_display , $this->errorlog_level ) = $stack;
		if( !$ret ){
			$fg = $this->errorGather( implode($this->smtpLFC , $this->error_stack) , __LINE__);
			$this->log();
			return $fg;
		}
		if( !$this->continue ){
			$this->close();
		}
		$this->log();
		return ( 0 === count( $this->error_stack ) ) && $this->errorGather();
	}

	function close(){
		$items = array(
				array( 'QUIT' , null ),
			);
		list( $st , $mes , $com ) = $this->communicate( $items );
		if( !$st ){
			return $this->errorGather('Error at QUIT',__LINE__);
		}
		fclose( $this->sock );
	}

	function sendBase( $data = null ,$protocol = null ){
		if( !is_null( $data ) ){
			$this->data = $data;
		}
		if( is_null( $protocol ) ){
			$protocol = $this->protocol_def;
		}
		switch($protocol){
			case 'POP_BEFORE'://POP3
				if( !$this->pop3() ){
					return $this->errorGather('POP failure',__LINE);
				}
			case 'SMTP':
				if( !is_resource( $this->sock ) ){
					$this->sock = $this->connect();
				}
				$this->sayHello();
				if( !$this->sendData() ){
					return false;
				}
			break;
			case 'SMTP_AUTH':
				if(!is_resource($this->sock)){
					$this->sock = $this->connect();
				}

				if( !$this->already_auth ){
					$this->sayHello();
					if( 0 === preg_match('/[\s-]AUTH\s+([^\r\n]*)\r?\n/is', implode($this->smtpLFC,$this->smtp_log) , $matches)){
						return $this->errorGather('HOST:'.$this->smtp_param['HOST'].' doesnot suppoted SMTP AUTH Protocol',__LINE__);
					}
					$mes = strtoupper( $matches[1] );
					$decide = null;
					foreach($this->smtp_auth_kind as $auth){
						if( false !== strpos( $mes , $auth ) ){
							$decide = $auth;
							break;
						}
					}
					if( is_null( $decide ) ){
						return $this->errorGather('HOST:'.$this->smtp_param['HOST'].' doesnot suppoted MY Abalable SMTP AUTH Protocol '.implode(' or ',$this->smtp_auth_kind),__LINE__);
					}
					$decide = strtolower( str_replace( '-' , '_' ,$decide ) );
					if( !$this->{$decide}() ){
						return $this->errorGather('Auth Error',__LINE__);;
					}
					$this->already_auth = true;
				}
				if( !$this->sendData() ){
					$this->already_auth = false;
					return $this->errorGather('Send Data Error or Auth Error',__LINE__);
				}
			break;
			case 'OVER_SSL':
			break;
			default:
			break;
		}
	//debug
/*
		if( 0==count($this->error_stack)){
			$this->error_stack = 'no error';
		}
		echo "<pre>";
		echo htmlspecialchars(print_r($this->smtp_log , true));
		echo htmlspecialchars(print_r($this->error_stack , true));
		echo "</pre>";
*/
		return $this->errorGather();

	}


	//----------------------------------------------
	// Comunication 
	//----------------------------------------------
	//-------------------------------------------------------------------------
	// see RFC821
	// in japanese ->http://www.sea-bird.org/doc/rfc_doc/rfc821-jp.txt(not me)
	//-------------------------------------------------------------------------
	var $smtp_status100 = array(
			1 => 4,
			2 => 3,
			3 => 2,
			4 => 1,
			5 => 0,
		);
	// 0 error
	// 1 continue
	// 2 final
	var $smtp_status10 = array(
			0 => 0,
			1 => 5,
			2 => 1,
			3 => 0,
			4 => 0,
			5 => 5,
		);

	function sayHello(){
		$items = array(
			array( 'EHLO' , $this->smtp_param['HOST'] ),
			array( 'HELO' , $this->smtp_param['HOST'] ),
			);
		if( !$this->tryUntilSuccess( $items ) ){
			return $this->errorGather('HOST:'.$this->smtp_param['HOST'].' say no HELLO',__LINE__);
		}
		return $this->errorGather();
	}
	function sendData(){
		$reci = array();

		if( '<' == substr( $this->smtp_param['FROM'],0,1) ){
			$from = 'FROM:' . $this->smtp_param['FROM'];
		}else{
			$from = 'FROM:<' . $this->smtp_param['FROM'] . '>';
		}

		$items = array(
				array( 'MAIL' ,  $from ),
			);
		list( $st , $mes , $com ) = $this->communicate($items);
		if( !$st ){
			return $this->errorGather('Error From setting',__LINE__);
		}
		$this->rcpt = array();
		$notify = $this->always_notify_success ? ' NOTIFY=SUCCESS,FAILURE':'';
		foreach( $this->recipient as $recipi ){
			$items = array(array( 'RCPT' , 'TO:<' . $recipi . '>'  . $notify ));
			list( $st , $mes , $com ) = $this->communicate($items);
			if( !$st ){
				$this->rcpt_undone[] = $recipi ;
				$this->errorGather('Error RCPT setting',__LINE__);
			}else{
				$this->rcpt[] = $recipi ;
			}
		}
		$this->rcpt_stack = array_merge( $this->rcpt_stack , $this->rcpt );
		$this->rcpt_undone_stack = array_merge( $this->rcpt_undone_stack , $this->rcpt_undone );
		$items = array();
		$items[] = array( 'DATA' ,  null );
		$items[] = array( 'DATA_CONTENT' , $this->smtpEscape($this->data).$this->smtpLFC . '.' );
		$items[] = array( 'RSET' , null );
		list( $st , $mes , $com ) = $this->communicate($items);
		if( !$st ){
			return $this->errorGather('Error Data sending',__LINE__);
		}
		return $this->errorGather();
	}


	function communicate( $items , $fp = null ){
		if( is_null( $fp ) ){
			$fp = $this->sock ;
		}
		$message = null ;
		if( !is_resource( $fp ) ){
			return array( $this->errorGather( 'Error Resouce  or stop connect' ,__LINE__) , $message , false );
		}
		foreach( $items as $item ){
			if( 'DATA_CONTENT' == $item[0] ){
				$spacer = null ;
				$item[0] = null ;
			}elseif( 'DATA' == $item[0] || is_null($item[1]) ){
				$spacer = null;
			}else{
				$spacer = ' ';
			}
			$put_message = rtrim( $item[0] . $spacer . $item[1] ) . $this->smtpLFC;
			if( !fputs( $fp , $put_message  ) ){
				return array( $this->errorGather('SMTP can not fputs',__LINE__), $message , false );
			}
			$this->smtp_log[] = $this->name . ' ' . $put_message ;

			do{
				list( $st , $_message ) = $this->getMessage( $fp );
				$message .= trim( $_message ) . $this->smtpLFC;
				if( !$st ){
					return array( $this->errorGather('getMessage error',__LINE__), $message , $st );
				}
				$branch = isset($this->smtp_status[$item[0]][$st]) ? $this->smtp_status[$item[0]][$st] : null;
				switch( $branch ){
					case 'S': // Success
						$contine = false ;
					break;
					case 'F': // Failure
						return array( $this->errorGather('Failure :status'.$st.' message:'.htmlspecialchars($_message).' on '.htmlspecialchars($put_message),__LINE__) , $message , $st );
					break;
					case 'E': // Error
						return array( $this->errorGather('Error :status'.$st.' message:'.htmlspecialchars($_message).' on '.htmlspecialchars($put_message),__LINE__) , $message , $st );
					break;
					default:
						$s100 = (int) substr( $st , 0 , 1 );
						$s10 = (int) substr( $st , 1 , 1 );
						$s = $this->smtp_status100[$s100] * $this->smtp_status10[$s10];
						switch($s){
							case 0: // Error
								return array( $this->errorGather('Unkown Error :status'.$st.' message:'.htmlspecialchars($_message).' on '.htmlspecialchars($put_message),__LINE__) , $message , $st );
							break;
							case 3: //22X,220
								$contine = true ;
							break;
							case 10: //35X,354
								$contine = false ;
							break;
							case 15: //25X,250 Sucsess
								$contine = false ;
							break;
							default:
								$contine = false;
							break;
						}
					break;
				}
			}while($contine);
		}
		return array($this->errorGather() , $message , $st );
	}

	function getMessage( $fp = null ){
		if( is_null( $fp ) ){
			$fp = $this->sock ;
		}
		if( !is_resource( $fp ) ){
			return array( $this->errorGather( 'Error Resouce  or stop connect' ,__LINE__) , null );
		}
		$status = array();
		$status[-1] = null;
		$message = array();
		$count = 0;
		do{
			$mes = fgets( $fp , 512 );
			if( false === $mes ){
				$er = stream_get_meta_data ( $fp );
				$er_mes = null;
				if( true === $er ['timed_out'] ){
					$er_mes = ' SYSTEM TIME OUT ';
				}
				return array( $this->errorGather('No Responce' . $er_mes,__LINE__) , null );
			}
			$status[$count] = substr( $mes , 0 , 3 );
			$_continue = substr( $mes , 3 , 1 );
			$message[] = trim( $mes );
			$this->smtp_log[] = 'Server '.$mes ;
			if( '-' == $_continue  && ( ($status[$count] == $status[$count-1]) || (0 == $count)) ){
				$continue = true ;
			}else{
				$continue = false ;
			}
			$count ++;
		}while($continue);
		return array( $status[0] , implode( $this->smtpLFC , $message ) );
	}

	function connect( $host = null , $port = null , $status = 220 ){
		if(is_null($host)){
			$host = $this->smtp_param['HOST'];
		}
		if(is_null($port)){
			$port = $this->smtp_param['PORT'];
		}
		$sock = fsockopen( $host , $port , $err , $errst , $this->time_out );
		if( !is_resource( $sock ) ){
			return $this->errorGather('Connection error HOST: '.$host.' PORT: ' . $port ,__LINE__);
		}
		stream_set_timeout ( $sock , $this->time_out );
		return $sock;
	}

	function tryUntilSuccess( $items ){
		$try = false;
		$err_mes = array();
		foreach( $items as $item ){
			$err_mes[] = $item[0];
			$this->error_ignore = true;
			list( $st , $mes , $com ) = $this->communicate( array( $item ) );
			$this->error_ignore = false;
			if( true === $st ){
				$try = true;
				$this->error = array();
				break;
			}
		}
		if( !$try ){
			return $this->errorGather( 'Tyr Error ->' . implode( ' ' , $err_mes ) ,__LINE__);
		}
		return $this->errorGather();
	}

	//--------------------------
	// AUTH
	//--------------------------
	function plain(){
		$plain = $this->makePlain();
		$items = array();
		foreach($plain as $pn ){
			$items[] = array( 'AUTH PLAIN' , $pn );
		}
		return $this->tryUntilSuccess( $items );
	}
	function makePlain(){
		$plain[0] = base64_encode($this->smtp_param['USER']."\0".$this->smtp_param['USER']."\0".$this->smtp_param['PASS']);
		$plain[1] = base64_encode($this->smtp_param['USER']."\0".$this->smtp_param['PASS']);
		return $plain;
	}
	//-----------------------------------
	// Utility
	//-----------------------------------
	function makeData( $to , $subject , $message , $header=null , $option=null ){
		$recip = array();
		$recip = array_merge( $recip , $this->extractAddr( $to ) );
		$recip = array_merge( $recip , $this->extractAddr( $this->extractHeader('CC' , $header) ) );
		$recip = array_merge( $recip , $this->extractAddr( $this->extractHeader('BCC' , $header) ) );
		$this->recipient( $recip );
		$head = trim( 'To: ' . $to . $this->smtpLFC . 'Subject: ' . trim( $subject ) . $this->smtpLFC . trim($header) );
		return $this->data = $head . $this->smtpLFC . $this->smtpLFC . $message ;
	}

	function extractAddr( $line ){
		if(0===preg_match_all('/<?([^<,]+@[^>,]+)>?\s*,?\s*/',$line,$matches)){
			return array();
		}else{
			return $matches[1];
		}
	}

	function extractHeader( $section , $header){
		if( 0===preg_match('/'.$section.': (.*)\r?\n[^\s]/is' , $header , $matches ) ){
			return null;
		}else{
			return str_replace( array( "\r" , "\n" , "\t" , ' ' ) , null , $matches[1] );
		}
	}

	function smtpEscape( $mes ){
		$mes = preg_replace( '/\r?\n\.\r?\n/is' , $this->smtpLFC . '..' . $this->smtpLFC , $mes );
		if( 0 !== preg_match( '/\r?\n[^\r\n]{'.$this->smtp_limit.',}\r?\n/is' , $mes ) ){
			return $this->errorGather('SMTP Overfllow '.$this->smtp_limit.' chars in one line.',__LINE__);
		}
		return $mes;
	}
	//----------------------------------------------------
	// POP3
	//----------------------------------------------------
	function pop3(){

		if( $this->pop3_use_file ){
			if( !file_exists( $this->pop3_time_file ) ){
				$this->writePop3Time();
			}
			$this->pop3_connect_start = file_get_contents( $this->pop3_time_file );
		}elseif( is_null( $this->pop3_connect_start ) ){
			$this->pop3_connect_start = time();
		}
		if( ( $this->pop3_connect_start + $this->pop3_valid_minute * 60 ) > time() ){
			return $this->errorGather();
		}

		if( $this->pop3_use_file ){
			$this->writePop3Time();
		}
		$fp = $this->connect( $this->smtp_param['POP_HOST'] , 110 , '+OK' );
		if( false === $fp ){
			return $this->errorGather('Can not connect POP3 Sever' ,__LINE__);
		}
		$items = array(
			array( 'USER' , $this->smtp_param['POP_USER'] ),
			array( 'PASS' , $this->smtp_param['POP_PASS'] ),
		);
		$this->communicate( $items , $fp );
		fclose( $fp );
		return $this->errorGather();
	}
	function pop3UseFile( $bool = null ){
		if( is_null($bool)){
			return $this->pop3_use_file;
		}
		if( is_bool( $bool ) ){
			$this->pop3_use_file = $bool;
			return $this->errorGather();
		}else{
			return $this->errorGather('POP3 UseFile specifed error',__LINE__);
		}
	}
	function pop3TimeFilename( $filename = null ){
		if(is_null($filename)){
			return $this->pop3_time_file;
		}
		if( is_string( $filename ) && !empty( $filename ) ){
			$this->pop3_time_file = $filename;
			return $this->errorGather();
		}else{
			return $this->errorGather('POP3 Filename specifed error',__LINE__);
		}
	}
	function pop3ValidMinute( $min = null ){
		if(is_null($min)){
			return $this->pop3_valid_minute;
		}
		if( is_numeric( $min ) ){
			$this->pop3_valid_minute = $min;
			return $this->errorGather();
		}else{
			return $this->errorGather('POP3 Valid Minute specifed error',__LINE__);
		}
	}
	function writePop3Time(){
		$fp_time = fopen($this->pop3_time_file,'w');
		fputs( $fp_time , time() );
		fclose($fp_time);
	}
	//--------------------------------
	// Result Colleciton 
	//--------------------------------
var $smtp_status= array(

	'USER' => array( 
		'+OK' => 'S', // for pop3
	),
	'PASS' => array( 
		'+OK' => 'S', // for pop3
	),

	'HELO' => array(
		'250' => 'S',
		'500' => 'E',
		'501' => 'E',
		'504' => 'E',
		'421' => 'E',
	),
	'EHLO' => array(
		'250' => 'S',
		'500' => 'E',
		'501' => 'E',
		'504' => 'E',
		'421' => 'E',
	),
	'MAIL' => array(
		'250' => 'S',
		'500' => 'E',
		'501' => 'E',
		'421' => 'E',
		'552' => 'E',
		'451' => 'E',
		'452' => 'E',
	),
	'RCPT' => array(
		'250' => 'S',
		'251' => 'S',
		'550' => 'F',
		'551' => 'F',
		'552' => 'F',
		'553' => 'F',
		'450' => 'F',
		'451' => 'F',
		'452' => 'F',
		'500' => 'E',
		'501' => 'E',
		'503' => 'E',
		'421' => 'E',
	),
	'DATA' => array(
		'354' => 'S',
		'250' => 'S',
		'451' => 'F',
		'554' => 'F',
		'500' => 'E',
		'501' => 'E',
		'503' => 'E',
		'421' => 'E',
	),
	'AUTH PLAIN' => array(
		'235' => 'S',
		'503' => 'S', //503 5.5.0 Already Authenticated
		'501' => 'E',
		'535' => 'E',
	),
	'STARTTLS' => array(
		'220' => 'S',
	),
	'RSET' => array(
		'250' => 'S',
	),
	'QUIT' => array(
		'221' => 'S',
		'500' => 'E',
		'501' => 'E',
		'504' => 'E',
	),
);
}

class Qdsmtp extends QdsmtpBase{
	function Qdsmtp( $param = null ){
		if( !is_null($param)){
			$param = func_get_args();
		}
		parent::QdsmtpBase( $param );
	}
}
//-------------------------------------------
// CakePHP Component
//-------------------------------------------
class QdsmtpComponent extends QdsmtpBase{

	var $layout		= 'default';
	var $view_dir	= 'email';
	var $layout_dir	= 'email';
	var $template	= 'default';
	var $view		= null;

	function QdsmtpComponent( $param = null ){
		if( !is_null($param)){
			$param = func_get_args();
		}
		parent::QdsmtpBase( $param );
	}

	function startup(&$controller) {
		$this->Controller =& $controller;
		if( defined( 'COMPONENTS' ) ){
			$this->logFilename(COMPONENTS.$this->name.'.log');
			$this->errorlogFilename( COMPONENTS . '_error' . $this->name . '.log' );
		}
		return;
	}
}?>