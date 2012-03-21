<?php
/*
 * System name : TransmitMail
 * File name : common.php
 * Description : 関数群
 * Author : TAGAWA Takao (dounokouno@gmail.com)
 * License : MIT License
 * Since : 2010-11-19
 * Modified : 2012-03-21
*/

// ----------------------------------------------------------------
// システム名、バージョン
// ----------------------------------------------------------------
define('SYSTEM_NAME', 'TransmitMail');
define('VERSION', '1.0.4');

// 入力情報として除外する項目
define('EXCLUSION_ITEM', 'page|required|hankaku|hankaku_eisu|hankaku_eiji|num|num_hyphen|hiragana|zenkaku_katakana|hankaku_katakana|zenkaku|zenkaku_all|email|match|len');

// タイムゾーン
date_default_timezone_set('Asia/Tokyo');


// ----------------------------------------------------------------
// チェックモードのHTMLを出力
// ----------------------------------------------------------------
function output_checkmode() {
	// 変数
	$a = array();
	$ng = '<span style="color:#f00;">NG</span>';
	
	// HTML生成
	$a[] = '<html>';
	$a[] = '<head>';
	$a[] = '<title>チェックモード</title>';
	$a[] = '</head>';
	$a[] = '<body>';
	$a[] = '<h1>チェックモード</h1>';
	
	// システム情報
	$a[] = '<h2>システム情報</h2>';
	$a[] = '<ul>';
	$a[] = '<li>システム名 : ' . SYSTEM_NAME . '</li>';
	$a[] = '<li>バージョン : ' . VERSION . '</li>';
	$a[] = '</ul>';
	
	// sendmail
	$a[] = '<h2>sendmail</h2>';
	$a[] = '<ul>';
	$ini = ini_get_all();
	$sendmail = $ini['sendmail_path']['global_value'];
	if ($sendmail != '') {
		$a[] = '<li>' . $sendmail . '</li>';
	} else {
		$a[] = '<li>' . $ng . '</li>';
	}
	$a[] = '</li>';
	$a[] = '</ul>';
	
	// HTMLテンプレート
	$a[] = '<h2>HTMLテンプレート</h2>';
	$a[] = '<ul>';
	
	// HTMLテンプレート 入力画面
	$s = '<li>' . TMPL_INPUT . ' : ';
	if (is_file(TMPL_INPUT)) {
		$s .= 'OK';
	} else {
		$s .= '<span style="color:#f00;">NG</span>';
	}
	$s .= '</li>';
	$a[] = $s;
	
	// HTMLテンプレート 確認画面
	$s = '<li>' . TMPL_CONFIRM . ' : ';
	if (is_file(TMPL_CONFIRM)) {
		$s .= 'OK';
	} else {
		$s .= '<span style="color:#f00;">NG</span>';
	}
	$s .= '</li>';
	$a[] = $s;
	
	// HTMLテンプレート 完了画面
	$s = '<li>' . TMPL_FINISH . ' : ';
	if (is_file(TMPL_FINISH)) {
		$s .= 'OK';
	} else {
		$s .= '<span style="color:#f00;">NG</span>';
	}
	$s .= '</li>';
	$a[] = $s;
	
	// HTMLテンプレート エラー画面
	$s = '<li>' . TMPL_ERROR . ' : ';
	if (is_file(TMPL_ERROR)) {
		$s .= 'OK';
	} else {
		$s .= '<span style="color:#f00;">NG</span>';
	}
	$s .= '</li>';
	$a[] = $s;
	
	// HTMLテンプレート ここまで
	$a[] = '</ul>';
	
	// メールテンプレート
	$a[] = '<h2>メールテンプレート</h2>';
	$a[] = '<ul>';
	
	// メールテンプレート 送信メール
	$s = '<li>' . MAIL_BODY . ' : ';
	if (is_file(MAIL_BODY)) {
		$s .= 'OK';
	} else {
		$s .= '<span style="color:#f00;">NG</span>';
	}
	$s .= '</li>';
	$a[] = $s;
	
	// メールテンプレート 自動返信メール
	$s = '<li>' . MAIL_AUTO_REPLY_BODY . ' : ';
	if (is_file(MAIL_AUTO_REPLY_BODY)) {
		$s .= 'OK';
	} else {
		$s .= '<span style="color:#f00;">NG</span>';
	}
	$a[] = $s;
	
	// メールテンプレート ここまで
	$a[] = '</ul>';
	
	// パーミッション
	$a[] = '<h2>パーミッション</h2>';
	$a[] = '<ul>';
	
	// パーミッション logsディレクトリ
	$s = '<li>' . DIR_LOGS . '/ : ';
	$perms = substr(sprintf('%o', fileperms(DIR_LOGS)), -3);
	if (($perms === '707') || $perms === '777') {
		$s .= 'OK';
	} else {
		$s .= '<span style="color:#f00;">NG</span>';
	}
	$s .= ' (' . $perms . ')</li>';
	$a[] = $s;
	
	// パーミッション tempディレクトリ
	$s = '<li>' . DIR_TEMP . '/ : ';
	$perms = substr(sprintf('%o', fileperms(DIR_TEMP)), -3);
	if (($perms === '707') || $perms === '777') {
		$s .= 'OK';
	} else {
		$s .= '<span style="color:#f00;">NG</span>';
	}
	$s .= ' (' . $perms . ')</li>';
	$a[] = $s;
	
	// パーミッション ここまで
	$a[] = '</ul>';
	
	$a[] = '</body>';
	$a[] = '</html>';
	
	// HTML出力
	header("Content-type: text/html; charset=utf-8");
	echo implode($a, "\n");
}


// ----------------------------------------------------------------
// 半角文字チェック
// ----------------------------------------------------------------
function check_hankaku($s) {
	return preg_match('/^[!-~]*$/', $s);
}


// ----------------------------------------------------------------
// 半角英数字チェック
// ----------------------------------------------------------------
function check_hankaku_eisu($s) {
	return preg_match('/^[a-zA-Z0-9]*$/', $s);
}


// ----------------------------------------------------------------
// 半角英字チェック
// ----------------------------------------------------------------
function check_hankaku_eiji($s) {
	return preg_match('/^[a-zA-Z]*$/', $s);
}


// ----------------------------------------------------------------
// 数字チェック
// ----------------------------------------------------------------
function check_num($s) {
	return preg_match('/^[0-9]*$/', $s);
}


// ----------------------------------------------------------------
// 数字とハイフンチェック
// ----------------------------------------------------------------
function check_num_hyphen($s) {
	return preg_match('/^[0-9-]*$/', $s);
}


// ----------------------------------------------------------------
// ひらがなチェック
// ----------------------------------------------------------------
function check_hiragana($s) {
	return preg_match('/^[ぁ-ゞ]*$/' . REG_OPTION, $s);
}


// ----------------------------------------------------------------
// 全角カタカナチェック
// ----------------------------------------------------------------
function check_zenkaku_katakana($s) {
	return preg_match('/^[ァ-ヶー]*$/' . REG_OPTION, $s);
}


// ----------------------------------------------------------------
// 半角カタカナチェック
// ----------------------------------------------------------------
function check_hankaku_katakana($s) {
	return preg_match('/^[ｱ-ﾝﾞﾟ]*$/' . REG_OPTION, $s);
}


// ----------------------------------------------------------------
// 全角文字を含むかチェック
// ----------------------------------------------------------------
function check_zenkaku($s) {
	return preg_match('/[^ -~｡-ﾟ]/' . REG_OPTION, $s);
}


// ----------------------------------------------------------------
// 全て全角文字チェック
// ----------------------------------------------------------------
function check_zenkaku_all($s) {
	return preg_match('/^[^ -~｡-ﾟ]*$/' . REG_OPTION, $s);
}


// ----------------------------------------------------------------
// メールアドレスの書式チェック
// ----------------------------------------------------------------
function check_mail_address($s) {
	return preg_match('/^([a-z0-9_]|\-|\.|\+)+@(([a-z0-9_]|\-)+\.)+[a-z]{2,6}$/i', $s);
}


// ----------------------------------------------------------------
// 文字数チェック
// ----------------------------------------------------------------
function check_len($s, $a) {
	if (empty($a[0])) {
		if (mb_strlen($s) > $a[1]) {
			return false;
		}
	} elseif (empty($a[1])) {
		if (mb_strlen($s) < $a[0]) {
			return false;
		}
	}	else {
		if ((mb_strlen($s) < $a[0]) || (mb_strlen($s) > $a[1])) {
			return false;
		}
	}
	return true;
}


// ----------------------------------------------------------------
// input type="hidden"に置き換え
// ----------------------------------------------------------------
function convert_input_hidden($k, $v) {
	$results = array();
	$hidden = '<input type="hidden" name="{key}" value="{value}" />';
	if (is_array($v)) {
		$array = array();
		foreach ($v as $v2) {
			$str = str_replace('{key}', h($k) . '[]', $hidden);
			$results[] = str_replace('{value}', h($v2), $str);
		}
	} else {
		$str = str_replace('{key}', h($k), $hidden);
		$results[] = str_replace('{value}', h($v), $str);
	}
	return implode('', $results);
}


// ----------------------------------------------------------------
// メール送信
// ----------------------------------------------------------------
function send_mail($to_email, $subject, $body, $from_email, $from_name = '') {
	// from
	if (empty($from_name)) {
		$from = $from_email;
	} else {
		$from = mb_encode_mimeheader(mb_convert_encoding($from_name, 'UTF-8', 'AUTO')) . ' <' . $from_email . '>';
	}
	
	// headers
	$array[] = 'MIME-Version: 1.0';
	$array[] = 'From: ' . $from;
	$array[] = 'Reply-To: ' . $from;
	$array[] = 'Content-Type: text/plain; charset=UTF-8';
	$headers = implode("\n", $array);
	
	// subject
	$subject = mb_encode_mimeheader(mb_convert_encoding($subject, 'UTF-8', 'AUTO'));
	
	// body
	$body = mb_convert_encoding($body, 'UTF-8', 'AUTO');
	
	// params
	$params  = "-f$from_email";
	
	// send
	return mail($to_email, $subject, $body, $headers, $params);
}


// ----------------------------------------------------------------
// ログ出力
// ----------------------------------------------------------------
function put_error_log($s, $suffix) {
	// ファイル名
	$date = date('Ymd_His');
	$file_name = 'error_' . $date . '_' . $suffix;
	$log_name = is_log_file($file_name);
	
	// ファイルロック
	$lock_file = DIR_LOGS . 'lock';
	$lock_fp = @fopen($lock_file, 'w');
	$lock = @flock($lock_fp, LOCK_EX);
	while (!$lock) {
		usleep(100000);	// 0.1秒スリープ(2000000 = 1秒)
		$log_name = is_log_file($file_name);
		$lock_fp = @fopen($lock_file, 'w');
		$lock = @flock($lock_fp, LOCK_EX);
	}
	
	// ファイル書き込み
	$byte = file_put_contents(DIR_LOGS . $log_name.'.txt', $s);
	fclose($lock_fp);
	unlink($lock_file);
	return $byte;
}


// ----------------------------------------------------------------
// ログファイルの存在確認
// ----------------------------------------------------------------
function is_log_file($file_name) {
	$log_file = $file_name;
	if (is_file($log_file)) {
		$i = 1;
		$num = '_' . $i;
		$log_file = $file_name . $num;
		while (is_file($result)) {
			$i++;
			$num = '_' . $i;
			$log_file = $file_name . $num;
		}
	}
	return $log_file;
}


// ----------------------------------------------------------------
// ヌルバイトの削除
// ----------------------------------------------------------------
function delete_nullbyte($s) {
	if (is_array($s)) {
		return array_map('delete_nullbyte', $s);
	}
	return str_replace("\0", '', $s);
}


// ----------------------------------------------------------------
// magic_quotes_gpcがonの場合、バックスラッシュ（\）を削除
// ----------------------------------------------------------------
function safe_strip_slashes($s) {
	if (is_array($s)) {
		return array_map('safe_strip_slashes',$s);
	} else {
		if (get_magic_quotes_gpc()){
			$s = stripslashes($s);
		}
		return $s;
	}
}


// ----------------------------------------------------------------
// 空白文字の削除
// ----------------------------------------------------------------
function delete_blank($s) {
	if (is_array($s)) {
		return array_map('delete_blank', $s);
	}
	return preg_replace('/\s|　/', '', $s);
}


// ----------------------------------------------------------------
// 改行コードの削除
// ----------------------------------------------------------------
function delete_crlf($s) {
	if (is_array($s)) {
		return array_map('delete_crlf', $s);
	}
	return preg_replace('/\r|\n/', '', $s);
}


// ----------------------------------------------------------------
// htmlentitiesのショートハンド関数
// ----------------------------------------------------------------
function h($s) {
	if(is_array($s)) {
		return array_map('h', $s);
	}
	return htmlentities($s, ENT_QUOTES, mb_internal_encoding());
}


// ----------------------------------------------------------------
// html_entity_decodeのショートハンド関数
// --------------------------------------------------------------
function hd($s) {
	if(is_array($s)) {
		return array_map('hd', $s);
	}
	return html_entity_decode($s, ENT_QUOTES, mb_internal_encoding());
}


// ----------------------------------------------------------------
// file_put_contents for PHP4
// ----------------------------------------------------------------
if (!function_exists('file_put_contents')) {
	function file_put_contents($filename, $data) {
		$f = @fopen($filename, 'w');
		if (!$f) {
			return false;
		} else {
			$bytes = fwrite($f, $data);
			fclose($f);
			return $bytes;
		}
	}
}


// ----------------------------------------------------------------
// print_debug
// ----------------------------------------------------------------
function print_debug($v) {
	echo '<pre>';
	var_dump($v);
	echo '</pre>';
}


?>
