<?php
/*
 * System name : TransmitMail
 * Description : メール送信システム本体
 * Author : TAGAWA Takao (dounokouno@gmail.com)
 * License : MIT License
 * Since : 2010-11-19
 * Modified : 2015-06-08
*/

// --------------------------------------------------------------
// ライブラリ読み込み
// --------------------------------------------------------------
require_once('./conf/config.php');
require_once(DIR_LIB . '/common.php');
require_once(DIR_LIB . '/tinyTemplate.php');
$tmpl = new tinyTemplate();


// --------------------------------------------------------------
// 言語環境など
// --------------------------------------------------------------
mb_language('ja');
mb_internal_encoding(CHARASET);
mb_regex_encoding(CHARASET);
ini_set('error_log', DIR_LOGS . '/error.log');


// --------------------------------------------------------------
// 変数
// --------------------------------------------------------------
// 統括エラー
$global_error = array();
$global_error_flag = false;

// アクセス拒否フラグ
$deny_flag = false;

// 表示ページ名
$page = '';


// --------------------------------------------------------------
// GET値、POST値、SERVER値取得
// --------------------------------------------------------------
// $_GET、$_POST
$_GET = delete_nullbyte($_GET);
$_POST = delete_nullbyte($_POST);
$_GET = safe_strip_slashes($_GET);
$_POST = safe_strip_slashes($_POST);

// $_SERVER
$_SERVER = delete_nullbyte($_SERVER);
$_SERVER = safe_strip_slashes($_SERVER);
if (empty($_SERVER['REMOTE_HOST'])) {
	$_SERVER['REMOTE_HOST'] = gethostbyaddr($_SERVER['REMOTE_ADDR']);
}

// アクセス拒否判定
if (defined('DENY_HOST') && (DENY_HOST !== '')) {
	$pattern = '/' . DENY_HOST . '/';
	if (preg_match($pattern, $_SERVER['REMOTE_ADDR'])
		|| preg_match($pattern, $_SERVER['REMOTE_HOST'])
	) {
		$deny_flag = true;
	}
}


// --------------------------------------------------------------
// 入力内容取得
// --------------------------------------------------------------
// デフォルトのchecked、selectedをテンプレートにセット
$tmpl->set('checked.default', ATTR_CHECKED);
$tmpl->set('selected.default', ATTR_SELECTED);
if (count($_POST) > 0) {
	$tmpl->set('checked.default', '');
	$tmpl->set('selected.default', '');
}

// ラジオボタン、チェックボックス、セレクトメニューの選択状態
foreach ($_POST as $k1 => $v1) {
	if ($k1 !== 'file') {
		if (is_array($v1)) {
			foreach ($v1 as $v2) {
				$tmpl->set("checked.$k1.$v2", ATTR_CHECKED);
				$tmpl->set("selected.$k1.$v2", ATTR_SELECTED);
			}
		} else {
			$tmpl->set("checked.$k1.$v1", ATTR_CHECKED);
			$tmpl->set("selected.$k1.$v1", ATTR_SELECTED);
		}
	}
}

// 入力必須チェック
if (isset($_POST['required'])) {
	foreach ($_POST['required'] as $v) {
		$tmpl->set("required.$v", false);
		if (!isset($_POST[$v]) || (isset($_POST[$v]) && (is_null($_POST[$v]) || ($_POST[$v] === '')))) {
			$tmpl->set("required.$v", h($v . ERROR_REQUIRED));
			$global_error[] = h($v . ERROR_REQUIRED);
			$global_error_flag = true;
		}
	}
}

// 半角文字チェック
if (isset($_POST['hankaku'])) {
	foreach ($_POST['hankaku'] as $v) {
		$tmpl->set("hankaku.$v", false);
		if (!empty($_POST[$v])) {
			$_POST[$v] = mb_convert_kana($_POST[$v], 'a');
			if (!check_hankaku($_POST[$v])) {
				$tmpl->set("hankaku.$v", h($v . ERROR_HANKAKU));
				$global_error[] = h($v . ERROR_HANKAKU);
				$global_error_flag = true;
			}
		}
	}
}

// 半角英数字チェック
if (isset($_POST['hankaku_eisu'])) {
	foreach ($_POST['hankaku_eisu'] as $v) {
		$tmpl->set("hankaku_eisu.$v", false);
		if (!empty($_POST[$v])) {
			$_POST[$v] = mb_convert_kana($_POST[$v], 'a');
			if (!check_hankaku_eisu($_POST[$v])) {
				$tmpl->set("hankaku_eisu.$v", h($v . ERROR_HANKAKU_EISU));
				$global_error[] = h($v . ERROR_HANKAKU_EISU);
				$global_error_flag = true;
			}
		}
	}
}

// 半角英字チェック
if (isset($_POST['hankaku_eiji'])) {
	foreach ($_POST['hankaku_eiji'] as $v) {
		$tmpl->set("hankaku_eiji.$v", false);
		if (!empty($_POST[$v])) {
			$_POST[$v] = mb_convert_kana($_POST[$v], 'r');
			if (!check_hankaku_eiji($_POST[$v])) {
				$tmpl->set("hankaku_eiji.$v", h($v . ERROR_HANKAKU_EIJI));
				$global_error[] = h($v . ERROR_HANKAKU_EIJI);
				$global_error_flag = true;
			}
		}
	}
}

// 数値チェック
if (isset($_POST['num'])) {
	foreach ($_POST['num'] as $v) {
		$tmpl->set("num.$v", false);
		if (!empty($_POST[$v])) {
			$_POST[$v] = mb_convert_kana($_POST[$v], 'n');
			if (!check_num($_POST[$v])) {
				$tmpl->set("num.$v", h($v . ERROR_NUM));
				$global_error[] = h($v . ERROR_NUM);
				$global_error_flag = true;
			}
		}
	}
}

// 数値とハイフンチェック
if (isset($_POST['num_hyphen'])) {
	foreach ($_POST['num_hyphen'] as $v) {
		$tmpl->set("num_hyphen.$v", false);
		if (!empty($_POST[$v])) {
			$_POST[$v] = mb_convert_kana($_POST[$v], 'a');
			if (!check_num_hyphen($_POST[$v])) {
				$tmpl->set("num_hyphen.$v", h($v . ERROR_NUM_HYPHEN));
				$global_error[] = h($v . ERROR_NUM_HYPHEN);
				$global_error_flag = true;
			}
		}
	}
}

// ひらがなチェック
if (isset($_POST['hiragana'])) {
	foreach ($_POST['hiragana'] as $v) {
		$tmpl->set("hiragana.$v", false);
		if (!empty($_POST[$v])) {
			$_POST[$v] = mb_convert_kana($_POST[$v], 'cH');
			$_POST[$v] = delete_blank($_POST[$v]);
			if (!check_hiragana($_POST[$v])) {
				$tmpl->set("hiragana.$v", h($v . ERROR_HIRAGANA));
				$global_error[] = h($v . ERROR_HIRAGANA);
				$global_error_flag = true;
			}
		}
	}
}

// 全角カタカナチェック
if (isset($_POST['zenkaku_katakana'])) {
	foreach ($_POST['zenkaku_katakana'] as $v) {
		$tmpl->set("zenkaku_katakana.$v", false);
		if (!empty($_POST[$v])) {
			$_POST[$v] = mb_convert_kana($_POST[$v], 'CK');
			$_POST[$v] = delete_blank($_POST[$v]);
			if (!check_zenkaku_katakana($_POST[$v])) {
				$tmpl->set("zenkaku_katakana.$v", ($v . ERROR_ZENKAKU_KATAKANA));
				$global_error[] = ($v . ERROR_ZENKAKU_KATAKANA);
				$global_error_flag = true;
			}
		}
	}
}

// 半角カタカナチェック
if (isset($_POST['hankaku_katakana'])) {
	foreach ($_POST['hankaku_katakana'] as $v) {
		$tmpl->set("hankaku_katakana.$v", false);
		if (!empty($_POST[$v])) {
			$_POST[$v] = mb_convert_kana($_POST[$v], 'kh');
			$_POST[$v] = delete_blank($_POST[$v]);
			if (!check_hankaku_katakana($_POST[$v])) {
				$tmpl->set("hankaku_katakana.$v", h($v . ERROR_HANKAKU_KATAKANA));
				$global_error[] = h($v . ERROR_HANKAKU_KATAKANA);
				$global_error_flag = true;
			}
		}
	}
}

// 全角文字チェック
if (isset($_POST['zenkaku'])) {
	foreach ($_POST['zenkaku'] as $v) {
		$tmpl->set("zenkaku.$v", false);
		if (!empty($_POST[$v])) {
			if (!check_zenkaku($_POST[$v])) {
				$tmpl->set("zenkaku.$v", h($v . ERROR_ZENKAKU));
				$global_error[] = h($v . ERROR_ZENKAKU);
				$global_error_flag = true;
			}
		}
	}
}

// 全て全角文字チェック
if (isset($_POST['zenkaku_all'])) {
	foreach ($_POST['zenkaku_all'] as $v) {
		$tmpl->set("zenkaku_all.$v", false);
		if (!empty($_POST[$v])) {
			if (!check_zenkaku_all($_POST[$v])) {
				$tmpl->set("zenkaku_all.$v", h($v . ERROR_ZENKAKU_ALL));
				$global_error[] = h($v . ERROR_ZENKAKU_ALL);
				$global_error_flag = true;
			}
		}
	}
}

// メールアドレスチェック
if (isset($_POST['email'])) {
	foreach ($_POST['email'] as $v) {
		$tmpl->set("email.$v", false);
		if (!empty($_POST[$v])) {
			$_POST[$v] = mb_convert_kana($_POST[$v], 'a');
			$_POST[$v] = delete_crlf($_POST[$v]);
			if (!check_mail_address($_POST[$v])) {
				$tmpl->set("email.$v", h($v . ERROR_EMAIL));
				$global_error[] = h($v . ERROR_EMAIL);
				$global_error_flag = true;
			}
		}
	}
}

// 自動返信メールの宛先（$_POST[AUTO_REPLY_EMAIL]）のメールアドレスチェック
if (isset($_POST[AUTO_REPLY_EMAIL]) && !empty($_POST[AUTO_REPLY_EMAIL])) {
	$_POST[AUTO_REPLY_EMAIL] = mb_convert_kana($_POST[AUTO_REPLY_EMAIL], 'a');
	$_POST[AUTO_REPLY_EMAIL] = delete_crlf($_POST[AUTO_REPLY_EMAIL]);
	if (!check_mail_address($_POST[AUTO_REPLY_EMAIL])) {
		$tmpl->set("email." . AUTO_REPLY_EMAIL, h(AUTO_REPLY_EMAIL . ERROR_EMAIL));
		if (!in_array(h(AUTO_REPLY_EMAIL . ERROR_EMAIL), $global_error, true)) {
			$global_error[] = h(AUTO_REPLY_EMAIL . ERROR_EMAIL);
		}
		$global_error_flag = true;
	}
}

// 一致チェック
if (isset($_POST['match'])) {
	foreach ($_POST['match'] as $v) {
		$array = preg_split('/\s|,/', $v);
		$tmpl->set("match.$array[0]", false);
		if (!empty($_POST[$array[0]])
			&& !empty($_POST[$array[1]])
			&& $_POST[$array[0]] != $_POST[$array[1]]
			) {
				$tmpl->set("match.$array[0]", h($array[0] . ERROR_MATCH));
				$global_error[] = h($array[0] . ERROR_MATCH);
				$global_error_flag = true;
		}
	}
}

// 文字数チェック
if (isset($_POST['len'])) {
	foreach ($_POST['len'] as $v) {
		$array = preg_split('/\s|,/', $v);
		$delim = explode('-', $array[1]);
		$delim = array_map('intval', $delim);
		$tmpl->set("len.$array[0]", false);
		if (!empty($_POST[$array[0]]) && !check_len($_POST[$array[0]], $delim)) {
			if (empty($delim[0])) {
				$error_len = str_replace('{文字数}', "$delim[1]文字以内", ERROR_LEN);
			} elseif (empty($delim[1])) {
				$error_len = str_replace('{文字数}', "$delim[0]文字以上", ERROR_LEN);
			} else {
				if ($delim[0] === $delim[1]) {
					$error_len = str_replace('{文字数}', "$delim[0]文字", ERROR_LEN);
				} else {
					$error_len = str_replace('{文字数}', "$delim[0]〜$delim[1]文字", ERROR_LEN);
				}
			}
			$tmpl->set("len.$array[0]", h($array[0] . $error_len));
			$global_error[] = h($array[0] . $error_len);
			$global_error_flag = true;
		}
	}
}

// URLチェック
if (isset($_POST['url'])) {
	foreach ($_POST['url'] as $v) {
		$tmpl->set("url.$v", false);
		if (!empty($_POST[$v])) {
			$_POST[$v] = mb_convert_kana($_POST[$v], 'a');
			$_POST[$v] = delete_crlf($_POST[$v]);
			if (!check_url($_POST[$v])) {
				$tmpl->set("url.$v", h($v . ERROR_URL));
				$global_error[] = h($v . ERROR_URL);
				$global_error_flag = true;
			}
		}
	}
}

// 整数範囲チェック
if (isset($_POST['num_range'])) {
	foreach ($_POST['num_range'] as $v) {
		$array = preg_split('/\s|,/', $v);
		$delim = explode('-', $array[1]);
		$delim = array_map('intval', $delim);
		$tmpl->set("num_range.$array[0]", false);
		if ($_POST[$array[0]] !== '') {
			// 数値チェック
			$_POST[$array[0]] = mb_convert_kana($_POST[$array[0]], 'n');
			if (!check_num($_POST[$array[0]])) {
				$tmpl->set("num_range.$array[0]", h($array[0] . ERROR_NUM));
				$global_error[] = h($array[0] . ERROR_NUM);
				$global_error_flag = true;
			} else {
				if (!check_num_range($_POST[$array[0]], $delim)) {
					if ($delim[0] === $delim[1]) {
						$error_num_range = str_replace('{範囲}', "ちょうど{$delim[0]}", ERROR_NUM_RANGE);
					} else {
						if ($delim[1] === 0) {
							$error_num_range = str_replace('{範囲}', "{$delim[0]}以上", ERROR_NUM_RANGE);
						} else {
							$error_num_range = str_replace('{範囲}', "{$delim[0]}以上、{$delim[1]}以下", ERROR_NUM_RANGE);
						}
					}
					$tmpl->set("num_range.$array[0]", h($array[0] . $error_num_range));
					$global_error[] = h($array[0] . $error_num_range);
					$global_error_flag = true;
				}
			}
		}
	}
}

// ファイル添付を利用する場合
if (FILE) {
	$files = array();

	// ファイルの削除
	if (isset($_POST['file_remove'])) {
		foreach ($_POST['file_remove'] as $v) {
			if (file_exists(DIR_TEMP . '/' . $v) && (preg_match('/^' . FILE_NAME_PREFIX . '/', $v)) && check_file_extension($v)) {
				if (!unlink(DIR_TEMP . '/' . $v)) {
					$global_error[] = h($v . ERROR_FILE_REMOVE);
					$global_error_flag = true;
				}
			} else {
				$global_error[] = h($v . ERROR_FILE_REMOVE);
				$global_error_flag = true;
			}
		}
	}

	// 既にファイルがアップロードされている場合
	if (isset($_POST['file'])) {
		foreach ($_POST['file'] as $k => $v) {
			if (isset($v['tmp_name'])) {
				// singleの場合
				if (file_exists(DIR_TEMP . '/' . $v['tmp_name'])) {
					$tmpl->set("$k.tmp_name", h($v['tmp_name']));
					$tmpl->set("$k.name", h($v['name']));
					$files[$k] = array('tmp_name' => h($v['tmp_name']), 'name' => h($v['name']));
				}
			}
		}
	}

	// ファイルのアップロード
	if (isset($_FILES)) {
		foreach ($_FILES as $k => $v) {
			$file_error = array();
			$tmpl->set("file.$k", false);
			if (!is_array($v['name'])) {
				// singleの場合
				if (!empty($v['name'])) {
					// 拡張子のチェック
					if (FILE_ALLOW_EXTENSION !== '' && !check_file_extension($v['name'])) {
						$file_error[] = h($k . ERROR_FILE_EXTENSION);
						$global_error[] = h($k . ERROR_FILE_EXTENSION);
						$global_error_flag = true;
					}

					// 空ファイルのチェック
					if ($v['size'] === 0) {
						$file_error[] = h($k . ERROR_FILE_EMPTY);
						$global_error[] = h($k . ERROR_FILE_EMPTY);
						$global_error_flag = true;
					}

					// ファイルサイズのチェック
					if ($v['size'] > FILE_MAX_SIZE) {
						$file_error[] = h($k . str_replace('{ファイルサイズ}', format_bytes(FILE_MAX_SIZE), ERROR_FILE_MAX_SIZE));
						$global_error[] = h($k . str_replace('{ファイルサイズ}', format_bytes(FILE_MAX_SIZE), ERROR_FILE_MAX_SIZE));
						$global_error_flag = true;
					}

					// エラーを判別
					if (count($file_error) > 0) {
						// エラーがある場合、エラーメッセージをセット
						$tmpl->set("file.$k", $file_error);
					} else {
						// エラーが無い場合、ファイルをDIR_TEMPに移動
						$tmp_name = FILE_NAME_PREFIX . uniqid(rand()) . '_' . $v['name'];
						$file_path = DIR_TEMP . '/' . $tmp_name;
						if (move_uploaded_file($v['tmp_name'], $file_path)) {
							$tmpl->set("$k.tmp_name", h($tmp_name));
							$tmpl->set("$k.name", h($v['name']));
							$files[$k] = array('tmp_name' => h($tmp_name), 'name' => h($v['name']));
						} else {
							// アップロードに失敗した場合
							$file_error[] = h($k . ERROR_FILE_UPLOAD);
							$global_error[] = h($k . ERROR_FILE_UPLOAD);
							$global_error_flag = true;
							$tmpl->set("file.$k", $file_error);
						}
					}
				} else {
					if (!isset($files[$k])) {
						$files[$k] = false;
					}
				}
			}
		}
	}
}

// セッションチェック
if (SESSION) {
	$session_flag = false;
	session_start();
	if (isset($_SESSION['transmit_mail_input']) && $_SESSION['transmit_mail_input']) {
		$session_flag = true;
	}
} else {
	$session_flag = true;
}


// --------------------------------------------------------------
// 表示画面判別
// --------------------------------------------------------------
if ($deny_flag) {
	// アクセス拒否
	$page = 'deny';

} elseif (CHECK_MODE && isset($_GET['mode']) && ($_GET['mode'] === 'check')) {
	// チェックモード
	$page = 'checkmode';

} elseif (isset($_GET['file'])) {
	// ファイル表示
	$page = 'file';

} elseif (!$session_flag) {
	// セッションが無い場合 入力画面
	$page = '';

} elseif (count($_POST) > 0) {
	if ($global_error_flag) {
		// エラーがある場合 入力エラー画面
		$page = '';

	} elseif (isset($_POST['page']) && ($_POST['page'] === 'input') && !$global_error_flag) {
		// 再入力画面
		$page = '';

	} elseif (isset($_POST['page']) && ($_POST['page'] === 'finish') && !$global_error_flag) {
		// 完了画面
		$page = 'finish';

	} elseif (!$global_error_flag) {
		// エラーが無い場合 確認画面
		$page = 'confirm';

	} else {
		// 入力画面
		$page = '';

	}
}


// --------------------------------------------------------------
// セッションの書き込み、破棄
// --------------------------------------------------------------
if (SESSION) {
	if (empty($page)) {
		// 入力画面 or 入力エラー画面 の場合 セッションの書き込み
		$_SESSION['transmit_mail_input'] = true;

	} elseif ($page === 'finish') {
		// 完了画面の場合 セッションを破棄
		$_SESSION = array();
		if (isset($_COOKIE[session_name()])) {
			setcookie(session_name(), '', time()-42000, DIR_MAILFORM, $_SERVER['HTTP_HOST']);
		}
		session_destroy();

	}
}


// --------------------------------------------------------------
// 入力情報をテンプレートにセット
// --------------------------------------------------------------
if (empty($page)) {
	// 入力画面 or 入力エラー画面
	foreach ($_POST as $k => $v) {
		$tmpl->set($k, h($v));
	}
	$tmpl->set('_GET', h($_GET));
	$tmpl->set('_SERVER', h($_SERVER));

} elseif ($page === 'confirm' || $page === 'finish') {
	// 確認画面 or 完了画面
	$params = array();
	$hiddens = array();

	// $_POST
	foreach ($_POST as $k => $v) {
		if (!preg_match(exclusion_item_pattern(), $k)) {
			if (is_array($v)) {
				$s = implode(', ', $v);
				$tmpl->set("$k.array", array_map('h', $v));
			} else {
				$s = $v;
			}
			$h = convert_input_hidden($k, $v);
			$tmpl->set("$k.key", h($k));
			$tmpl->set("$k.value", h($s));
			$tmpl->set("$k.value.nl2br", nl2br(h($s)));
			$tmpl->set("$k.hidden", $h);
			$params[] = array('key' => h($k), 'value' => h($s), 'value.nl2br' => nl2br(h($s)), 'hidden' => $h);
			$hiddens[] = $h;
		}
	}

	// $_FILES
	if (FILE) {
		$array = array();
		foreach ($files as $k => $v) {
			if (isset($v['tmp_name'])) {
				// singleの場合
				$h_tmp_name = convert_input_hidden('file[' . $k . '][tmp_name]', $v['tmp_name']);
				$h_name = convert_input_hidden('file[' . $k . '][name]', $v['name']);
				$tmpl->set("$k.key", h($k));
				$tmpl->set("$k.tmp_name", h($v['tmp_name']));
				$tmpl->set("$k.name", h($v['name']));
				$tmpl->set("$k.hidden_tmp_name", $h_tmp_name);
				$tmpl->set("$k.hidden_name", $h_name);
				$array[] = array('key' => h($k), 'tmp_name' => h($v['tmp_name']), 'name' => h($v['name']), 'hidden_tmp_name' => $h_tmp_name, 'hidden_name' => $h_tmp_name);
				$hiddens[] = $h_tmp_name;
				$hiddens[] = $h_name;
			}
		}
		$tmpl->set('files', $array);
	}

	$tmpl->set('params', $params);
	$tmpl->set('hiddens', implode('', $hiddens));
	$tmpl->set('_GET', h($_GET));
	$tmpl->set('_SERVER', h($_SERVER));
}


// --------------------------------------------------------------
// 画面表示
// --------------------------------------------------------------
if ($page === 'deny') {
	// -------------------------------------------------------
	// アクセス拒否画面
	// -------------------------------------------------------
	// エラーメッセージ
	$global_error_flag = true;
	$global_error[] = ERROR_DENY;

	// エラー情報をテンプレートにセット
	$tmpl->set('global_error_flag', $global_error_flag);
	$tmpl->set('global_error', $global_error);

	// HTML書き出し
	echo $tmpl->fetch(TMPL_ERROR);

} elseif ($page === 'checkmode') {
	// -------------------------------------------------------
	// チェックモード
	// -------------------------------------------------------
	output_checkmode();
	exit();

} elseif ($page === 'file') {
	// -------------------------------------------------------
	// ファイル表示
	// -------------------------------------------------------
	$file_path = DIR_TEMP . '/' . basename($_GET['file']);
	if (file_exists($file_path)) {
		if ((filemtime($file_path) + FILE_RETENTION_PERIOD) < time()) {
			// 保存期間を超えている場合
			echo h($_GET['file']) . ERROR_FILE_OVER_THE_PERIOD;
		} else {
			header('Content-type: ' . get_mime_type($_GET['file']));
			readfile($file_path);
		}
	} else {
		// ファイルが存在しない
		echo h($_GET['file']) . ERROR_FILE_NOT_EXIST;
	}
	exit();

} elseif ($page === 'finish') {
	// -------------------------------------------------------
	// メール送信
	// -------------------------------------------------------
	// ライブラリ読み込み
	require_once(DIR_LIB . '/qdmail.php');
	require_once(DIR_LIB . '/qdsmtp.php');

	// Qdmailの設定
	$mail = new Qdmail();
	$mail->errorDisplay(false);
	$mail->errorlogPath(DIR_LOGS . '/');
	$mail->errorlogLevel(3);
	$mail->errorlogFilename('qdmail_error.log');

	// Qdsmtpの設定
	$smtp = new QdSmtp();
	$smtp->pop3TimeFilename(DIR_TEMP . '/qdsmtp.time');
	$mail->setSmtpObject($smtp);

	// 宛先
	$to_email = TO_EMAIL;

	// 件名
	$to_subject = TO_SUBJECT;

	// メール本文
	$body = $tmpl->fetch(MAIL_BODY);
	$body = hd($body);

	// メール送信元
	if (isset($_POST[AUTO_REPLY_EMAIL]) && !empty($_POST[AUTO_REPLY_EMAIL])) {
		$from_email = $_POST[AUTO_REPLY_EMAIL];
	} else {
		$from_email = $to_email;
	}

	// メール送信内容
	$mail->to($to_email);
	$mail->subject($to_subject);
	$mail->text($body);
	$mail->from($from_email);

	// CCメールアドレスの設定がある場合
	if (CC_EMAIL !== '') {
		$mail->cc(CC_EMAIL);
	}

	// BCCメールアドレスの設定がある場合
	if (BCC_EMAIL !== '') {
		$mail->bcc(BCC_EMAIL);
	}

	// 添付ファイル機能を利用する場合
	if (FILE) {
		foreach ($files as $file) {
			$attach[] = array(
				'PATH' => DIR_TEMP . '/' . $file['tmp_name'],
				'NAME' => $file['name']
			);
		}
		if (isset($attach)) {
			$mail->attach($attach);
		}
	}

	// 外部SMTPを利用する場合
	if (SMTP) {
		$mail->smtp(true);
		$mail->smtpServer(
			array(
				'host' => SMTP_HOST,
				'port' => SMTP_PORT,
				'protocol' => SMTP_PROTOCOL,
				'user' => SMTP_USER,
				'pass' => SMTP_PASSWORD,
				'from' => $from_email
			)
		);
	}

	// メール送信
	$result = $mail->send();

	// 送信できなかった場合
	if (!$result) {
		// エラーメッセージ
		$global_error_flag = true;
		$global_error[] = ERROR_FAILURE_SEND_MAIL;

		// ログの内容
		$suffix = 'sendmail';
		$data = ERROR_FAILURE_SEND_MAIL .
			"\n\n" .
			"--\n\n" .
			"【宛先】\n" .
			"$to_email\n\n" .
			"【件名】\n" .
			"$to_subject\n\n" .
			"【本文】\n" .
			"$body";

		// 添付ファイルがある場合
		if (FILE) {
			foreach ($files as $key => $file) {
				if (copy(DIR_TEMP . '/' . $file['tmp_name'], DIR_LOGS . '/' . $file['tmp_name'])) {
					$data .= "\n\n" .
						"【$key】\n" .
						"ファイル名 : $file[name]\n" .
						"一時保存ファイル名 : $file[tmp_name]";
				} else {
					$data .= "\n\n" .
						"【$key】\n" .
						"ファイルの保存に失敗しました";
				}
			}
		}

		// ログ出力
		put_error_log($data, $suffix);
	}

	// -------------------------------------------------------
	// 自動返信メール
	// -------------------------------------------------------
	if (AUTO_REPLY && isset($_POST[AUTO_REPLY_EMAIL]) && !empty($_POST[AUTO_REPLY_EMAIL]) && $_POST[AUTO_REPLY_EMAIL] !== '') {
		// 宛先
		$to_email = $from_email;

		// 件名
		$to_subject = AUTO_REPLY_SUBJECT;
		if (empty($to_subject)) {
			$to_subject = TO_SUBJECT;
		}

		// メール本文
		$body = $tmpl->fetch(MAIL_AUTO_REPLY_BODY);
		$body = hd($body);

		// メール送信元
		$from_email = AUTO_REPLY_FROM_EMAIL;
		if (empty($from_email)) {
			$from_email = TO_EMAIL;
		}

		// メール送信内容
		$mail->to($to_email);
		$mail->subject($to_subject);
		$mail->text($body);

		// AUTO_REPLY_NAMEの設定がある場合
		if (AUTO_REPLY_NAME !== '') {
			$mail->from($from_email, AUTO_REPLY_NAME);
		} else {
			$mail->from($from_email);
		}

		// 外部SMTPを利用する場合
		if (SMTP) {
			$mail->smtp(true);
			$mail->smtpServer(
				array(
					'host' => SMTP_HOST,
					'port' => SMTP_PORT,
					'protocol' => SMTP_PROTOCOL,
					'user' => SMTP_USER,
					'pass' => SMTP_PASSWORD,
					'from' => $from_email
				)
			);
		}

		// メール送信
		$result = $mail->send();

		// 送信できなかった場合
		if (!$result) {
			// エラーメッセージ
			$global_error_flag = true;
			$global_error[] = ERROR_FAILURE_SEND_AUTO_REPLY;

			// ログの内容
			$suffix = 'autoreply';
			$data = ERROR_FAILURE_SEND_AUTO_REPLY .
				"\n\n" .
				"--\n\n" .
				"【宛先】\n" .
				"$to_email\n\n" .
				"【件名】\n" .
				"$to_subject\n\n" .
				"【本文】\n" .
				"$body";

			// 添付ファイルがある場合
			if (FILE) {
				foreach ($files as $key => $file) {
					if (copy(DIR_TEMP . '/' . $file['tmp_name'], DIR_LOGS . '/' . $file['tmp_name'])) {
						$data .= "\n\n" .
							"【$key】\n" .
							"ファイル名 : $file[name]\n" .
							"一時保存ファイル名 : $file[tmp_name]";
					} else {
						$data .= "\n\n" .
							"【$key】\n" .
							"ファイルの保存に失敗しました";
					}
				}
			}

			// ログ出力
			put_error_log($data, $suffix);
		}
	}

	// -------------------------------------------------------
	// 添付ファイルを削除
	// -------------------------------------------------------
	if (FILE) {
		foreach ($files as $file) {
			unlink(DIR_TEMP . '/' . $file['tmp_name']);
		}
	}

	// -------------------------------------------------------
	// CSVの出力
	// -------------------------------------------------------
	if (CSV_OUTPUT) {
		put_csv($_POST);
	}

	// -------------------------------------------------------
	// 完了画面
	// -------------------------------------------------------

	// エラー判別
	if ($global_error_flag) {
		// エラーの場合
		$tmpl->set('global_error_flag', $global_error_flag);
		$tmpl->set('global_error', $global_error);
		echo $tmpl->fetch(TMPL_ERROR);

	} else {
		// 送信できた場合
		echo $tmpl->fetch(TMPL_FINISH);

	}

} elseif ($page === 'confirm') {
	// -------------------------------------------------------
	// 確認画面
	// -------------------------------------------------------

	// テンプレート書き出し
	echo $tmpl->fetch(TMPL_CONFIRM);

} else {
	// -------------------------------------------------------
	// 入力画面 or 入力エラー画面
	// -------------------------------------------------------

	// エラー情報をテンプレートにセット
	$tmpl->set('global_error_flag', $global_error_flag);
	$tmpl->set('global_error', $global_error);

	// HTML書き出し
	echo $tmpl->fetch(TMPL_INPUT);

}
