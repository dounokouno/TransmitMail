<?php
/**
 * TransmitMail クラス
 *
 * @package   TransmitMail
 * @license   MIT License
 * @copyright TAGAWA Takao, dounokouno@gmail.com
 * @link      https://github.com/dounokouno/TransmitMail
 */

class TransmitMail
{
    // システム情報
    const SYSTEM_NAME = 'TransmitMail';
    const VERSION = '2.0.3';

    // グローバルエラー
    public $global_errors = array();

    // アクセス拒否フラグ
    public $deny_flag = false;

    // セッションによる多重送信防止フラグ
    public $session_flag = false;

    // ページ名
    public $page_name = '';

    // リクエスト値
    public $get = array();
    public $post = array();
    public $server = array();
    public $files = array();

    // テンプレート
    public $tpl = null;

    // メール
    public $mail = null;
    public $smtp = null;

    // 入力情報として除外する項目
    public $exclusion_item = '[
        "x",
        "y",
        "page",
        "required",
        "hankaku",
        "hankaku_eisu",
        "hankaku_eiji",
        "num",
        "num_hyphen",
        "hiragana",
        "zenkaku_katakana",
        "zenkaku",
        "zenkaku_all",
        "email",
        "match",
        "len",
        "url",
        "num_range",
        "file",
        "file_remove",
        "file_required"
    ]';

    // 設定の初期値
    private $default_config = array(
        // 基本的な設定
        'to_email' => '',
        'cc_email' => '',
        'bcc_email' => '',
        'to_subject' => '',
        'auto_reply' => true,
        'auto_reply_email' => 'メールアドレス',
        'auto_reply_subject' => '',
        'auto_reply_from_email' => '',
        'auto_reply_name' => '',

        // ファイルアップロード
        'file' => false,
        'file_allow_extension' => '',
        'file_max_size' => 512000,
        'file_retention_period' => 1800,

        // CSV
        'csv_output' => false,
        'csv_file' => 'data.csv',
        'csv_encode' => 'SJIS-win',

        // アクセス拒否ホスト
        'deny_host' => '',

        // セッションによる多重送信防止
        'session' => true,

        // チェックモード
        // 0 => 無効
        // 1 => 簡易モード
        // 2 => 詳細モード
        'checkmode' => 0,

        // エラー表示
        'display_error' => false,

        // SMTP
        'smtp' => false,
        'smtp_host' => '',
        'smtp_port' => '',
        'smtp_protocol' => '',
        'smtp_user' => '',
        'smtp_password' => '',

        // 言語
        'language' => 'ja',
        'charaset' => 'UTF-8',
        'reg_option' => 'u',

        // タイムゾーン
        'timezone' => 'Asia/Tokyo',

        // メールフォームプログラム
        'mailform_program' => 'index.php',

        // テンプレート
        'tpl_input' => 'input.html',
        'tpl_confirm' => 'confirm.html',
        'tpl_finish' => 'finish.html',
        'tpl_error' => 'error.html',
        'mail_body' => 'config/mail_body.txt',
        'mail_auto_reply_body' => 'config/mail_auto_reply_body.txt',

        // エラーメッセージ
        'error_required' => 'は入力必須です。',
        'error_hankaku' => 'は半角文字で入力してください。',
        'error_hankaku_eisu' => 'は半角英数字で入力してください。',
        'error_hankaku_eiji' => 'は半角英字で入力してください。',
        'error_num' => 'は数字で入力してください。',
        'error_num_hyphen' => 'は数字とハイフンで入力してください。',
        'error_hiragana' => 'はひらがなで入力してください。',
        'error_zenkaku_katakana' => 'は全角カタカナで入力してください。',
        'error_zenkaku' => 'は全角文字を含めて入力してください。',
        'error_zenkaku_all' => 'は全て全角文字で入力してください。',
        'error_email' => 'はメールアドレスの書式で入力してください。',
        'error_match' => 'が一致しません。',
        'error_len' => 'は{文字数}で入力してください。',
        'error_url' => 'はURLの書式で入力してください。',
        'error_num_range' => 'は{範囲}の数字で入力してください。',
        'error_file_extension' => 'は許可されていない拡張子です。',
        'error_file_empty' => 'は空のファイルです。',
        'error_file_max_size' => 'は指定サイズ（{ファイルサイズ}）を超えています。',
        'error_file_upload' => 'のアップロードに失敗しました。',
        'error_file_remove' => 'を削除できませんでした。',
        'error_file_not_exist' => 'は見つかりませんでした。',
        'error_file_over_the_period' => 'は一時保存期間を超えました。',
        'error_file_required' => 'は入力必須です。',
        'error_deny' => 'お使いのホストからのアクセスは管理者によって拒否されています。',
        'error_failure_send_mail' => 'メールの送信に失敗しました。',
        'error_failure_send_mail_auto_reply' => '自動返信メールの送信に失敗しました。',

        // その他
        'attr_checked' => 'checked',
        'attr_selected' => 'selected',
        'index_dir' => './',
        'log_dir' => 'log/',
        'tmp_dir' => 'tmp/',
        'file_name_prefix' => 'file_'
    );

    // 読み込まれた設定情報
    private $loaded_config = array();

    // 設定情報
    public $config = array();

    // 設定ファイル
    private $config_file = array();

    /**
     * コンストラクタ
     */
    public function __construct()
    {
        // 設定の初期値を $config に代入
        $this->config = $this->default_config;

        // 設定ファイルを読み込む
        $args = func_get_args();
        foreach ($args as $arg) {
            $this->config_file[] = $arg;
            if (is_file($arg)) {
                $this->loadConfig($arg);
            }
        }

        // エラー表示の設定
        if ($this->config['display_error']) {
            ini_set('display_errors', 'On');
        } else {
            ini_set('display_errors', 'Off');
        }

        if (defined('E_DEPRECATED')) {
            error_reporting(E_ALL ^ E_DEPRECATED);
        } else {
            error_reporting(E_ALL);
        }

        // エラーログの出力先
        ini_set('error_log', $this->config['log_dir'] . 'error.log');

        // 言語設定など
        mb_language($this->config['language']);
        mb_internal_encoding($this->config['charaset']);

        // タイムゾーン
        if (function_exists('date_default_timezone_set')) {
            date_default_timezone_set($this->config['timezone']);
        }

        // tinyTemplate を読み込む
        require_once dirname(__FILE__) . '/tinyTemplate.php';
        $this->tpl = new tinyTemplate();

        // セッションの開始
        if ($this->config['session']) {
            if (!isset($_SESSION)) {
                session_start();
            }
        }
    }

    /**
     * 実行
     *
     * @return boolean
     */
    public function run()
    {
        // リクエストを取得
        $this->getRequest();

        if (method_exists($this, 'afterGetRequest')) {
            $this->afterGetRequest();
        }

        // アクセス拒否ホストを判別
        $this->checkDenyHost();

        if (method_exists($this, 'afterCheckDenyHost')) {
            $this->afterCheckDenyHost();
        }

        // 入力内容をチェック
        $this->checkInput();

        if (method_exists($this, 'afterCheckInput')) {
            $this->afterCheckInput();
        }

        // 表示画面を判別、$page にセット
        $this->setPageName();

        if (method_exists($this, 'afterSetPageName')) {
            $this->afterSetPageName();
        }

        // 入力内容をテンプレートプロパティにセット
        $this->setTemplateProperty();

        if (method_exists($this, 'afterSetTemplateProperty')) {
            $this->afterSetTemplateProperty();
        }

        // $page にあわせて、メール送信、HTMLを出力
        $this->setTemplateAndSendMail();

        if (method_exists($this, 'afterSetTemplateAndSendMail')) {
            $this->afterSetTemplateAndSendMail();
        }
    }

    /**
     * 設定ファイルを読み込む
     *
     * @param string $config_file
     */
    public function loadConfig($config_file)
    {
        // 拡張子を取得
        preg_match('/\.([a-z0-9]{2,4})\z/i', $config_file, $matches);
        $suffix = strtolower($matches[1]);

        // 拡張子を判別
        switch ($suffix) {
            case 'php':
                // php の場合
                require_once $config_file;
                break;
            case 'json':
                // json の場合
                $json = json_decode(file_get_contents($config_file));
                $config = $json->config;
                break;
            case 'yaml':
            case 'yml':
                // ymlの場合
                require_once dirname(__FILE__) . '/Spyc.php';

                $yaml = Spyc::YAMLLoad($config_file);
                $config = $yaml['config'];
                break;
        }

        // $config を置き換え
        foreach ($config as $key => $value) {
            $this->loaded_config[$key] = $value;
            $this->config[$key] = $value;
        }
    }

    /**
     * リクエストを取得
     */
    public function getRequest()
    {
        $this->get = $_GET;
        $this->post = $_POST;
        $this->server = $_SERVER;

        $this->get = $this->deleteNullbyte($this->get);
        $this->post = $this->deleteNullbyte($this->post);
        $this->server = $this->deleteNullbyte($this->server);

        $this->get = $this->safeStripSlashes($this->get);
        $this->post = $this->safeStripSlashes($this->post);
        $this->server = $this->safeStripSlashes($this->server);

        if (empty($this->server['REMOTE_HOST'])) {
            $this->server['REMOTE_HOST'] = gethostbyaddr($this->server['REMOTE_ADDR']);
        }
    }

    /**
     * アクセス拒否ホストを判別
     */
    public function checkDenyHost()
    {
        if (!empty($this->config['deny_host'])) {
            $pattern = '/\A' . $this->config['deny_host'] . '\z/';

            if (preg_match($pattern, $this->servers['REMOTE_ADDR']) ||
                preg_match($pattern, $this->servers['REMOTE_HOST'])) {
                $this->deny_flag = true;
            }
        }
    }

    /**
     * 入力内容をチェック
     */
    public function checkInput()
    {
        // デフォルトの checked 、 selected をテンプレートにセット
        $this->tpl->set('checked.default', $this->config['attr_checked']);
        $this->tpl->set('selected.default', $this->config['attr_selected']);

        if (count($this->post) > 0) {
            $this->tpl->set('checked.default', '');
            $this->tpl->set('selected.default', '');
        }

        // ラジオボタン、チェックボックス、セレクトメニューの選択状態
        foreach ($this->post as $key1 => $value1) {
            if (is_array($value1)) {
                foreach ($value1 as $value2) {
                    if (!is_array($value2)) {
                        $this->tpl->set("checked.$key1.$value2", $this->config['attr_checked']);
                        $this->tpl->set("selected.$key1.$value2", $this->config['attr_selected']);
                    }
                }
            } else {
                $this->tpl->set("checked.$key1.$value1", $this->config['attr_checked']);
                $this->tpl->set("selected.$key1.$value1", $this->config['attr_selected']);
            }
        }

        // 入力必須チェック
        if (isset($this->post['required'])) {
            foreach ($this->post['required'] as $value) {
                $this->tpl->set("required.$value", false);

                if (!isset($this->post[$value]) || (isset($this->post[$value]) && (is_null($this->post[$value]) || ($this->post[$value] === '')))) {
                    $this->tpl->set("required.$value", $this->h($value . $this->config['error_required']));
                    $this->global_errors[] = $this->h($value . $this->config['error_required']);
                }
            }
        }

        // 半角文字チェック
        if (isset($this->post['hankaku'])) {
            foreach ($this->post['hankaku'] as $value) {
                $this->tpl->set("hankaku.$value", false);

                if (!empty($this->post[$value])) {
                    $this->post[$value] = mb_convert_kana($this->post[$value], 'a');

                    if (!$this->isHankaku($this->post[$value])) {
                        $this->tpl->set("hankaku.$value", $this->h($value . $this->config['error_hankaku']));
                        $this->global_errors[] = $this->h($value . $this->config['error_hankaku']);
                    }
                }
            }
        }

        // 半角英数字チェック
        if (isset($this->post['hankaku_eisu'])) {
            foreach ($this->post['hankaku_eisu'] as $value) {
                $this->tpl->set("hankaku_eisu.$value", false);

                if (!empty($this->post[$value])) {
                    $this->post[$value] = mb_convert_kana($this->post[$value], 'a');

                    if (!$this->isHankakuEisu($this->post[$value])) {
                        $this->tpl->set(
                            "hankaku_eisu.$value",
                            $this->h($value . $this->config['error_hankaku_eisu'])
                        );
                        $this->global_errors[] = $this->h($value . $this->config['error_hankaku_eisu']);
                    }
                }
            }
        }

        // 半角英字チェック
        if (isset($this->post['hankaku_eiji'])) {
            foreach ($this->post['hankaku_eiji'] as $value) {
                $this->tpl->set("hankaku_eiji.$value", false);

                if (!empty($this->post[$value])) {
                    $this->post[$value] = mb_convert_kana($this->post[$value], 'r');

                    if (!$this->isHankakuEiji($this->post[$value])) {
                        $this->tpl->set(
                            "hankaku_eiji.$value",
                            $this->h($value . $this->config['error_hankaku_eiji'])
                        );
                        $this->global_errors[] = $this->h($value . $this->config['error_hankaku_eiji']);
                    }
                }
            }
        }

        // 数字チェック
        if (isset($this->post['num'])) {
            foreach ($this->post['num'] as $value) {
                $this->tpl->set("num.$value", false);

                if (!empty($this->post[$value])) {
                    $this->post[$value] = mb_convert_kana($this->post[$value], 'n');

                    if (!$this->isNum($this->post[$value])) {
                        $this->tpl->set("num.$value", $this->h($value . $this->config['error_num']));
                        $this->global_errors[] = $this->h($value . $this->config['error_num']);
                    }
                }
            }
        }

        // 数字とハイフンチェック
        if (isset($this->post['num_hyphen'])) {
            foreach ($this->post['num_hyphen'] as $value) {
                $this->tpl->set("num_hyphen.$value", false);

                if (!empty($this->post[$value])) {
                    $this->post[$value] = mb_convert_kana($this->post[$value], 'a');

                    if (!$this->isNumHyphen($this->post[$value])) {
                        $this->tpl->set("num_hyphen.$value", $this->h($value . $this->config['error_num_hyphen']));
                        $this->global_errors[] = $this->h($value . $this->config['error_num_hyphen']);
                    }
                }
            }
        }

        // ひらがなチェック
        if (isset($this->post['hiragana'])) {
            foreach ($this->post['hiragana'] as $value) {
                $this->tpl->set("hiragana.$value", false);

                if (!empty($this->post[$value])) {
                    $this->post[$value] = mb_convert_kana($this->post[$value], 'cH');
                    $this->post[$value] = $this->deleteBlank($this->post[$value]);

                    if (!$this->isHiragana($this->post[$value])) {
                        $this->tpl->set("hiragana.$value", $this->h($value . $this->config['error_hiragana']));
                        $this->global_errors[] = $this->h($value . $this->config['error_hiragana']);
                    }
                }
            }
        }

        // 全角カタカナチェック
        if (isset($this->post['zenkaku_katakana'])) {
            foreach ($this->post['zenkaku_katakana'] as $value) {
                $this->tpl->set("zenkaku_katakana.$value", false);

                if (!empty($this->post[$value])) {
                    $this->post[$value] = mb_convert_kana($this->post[$value], 'CK');
                    $this->post[$value] = $this->deleteBlank($this->post[$value]);

                    if (!$this->isZenkakuKatakana($this->post[$value])) {
                        $this->tpl->set(
                            "zenkaku_katakana.$value",
                            $this->h($value . $this->config['error_zenkaku_katakana'])
                        );
                        $this->global_errors[] = ($value . $this->config['error_zenkaku_katakana']);
                    }
                }
            }
        }

        // 全角文字チェック
        if (isset($this->post['zenkaku'])) {
            foreach ($this->post['zenkaku'] as $value) {
                $this->tpl->set("zenkaku.$value", false);

                if (!empty($this->post[$value])) {
                    if (!$this->isZenkaku($this->post[$value])) {
                        $this->tpl->set("zenkaku.$value", $this->h($value . $this->config['error_zenkaku']));
                        $this->global_errors[] = $this->h($value . $this->config['error_zenkaku']);
                    }
                }
            }
        }

        // 全て全角文字チェック
        if (isset($this->post['zenkaku_all'])) {
            foreach ($this->post['zenkaku_all'] as $value) {
                $this->tpl->set("zenkaku_all.$value", false);

                if (!empty($this->post[$value])) {
                    if (!$this->isZenkakuAll($this->post[$value])) {
                        $this->tpl->set(
                            "zenkaku_all.$value",
                            $this->h($value . $this->config['error_zenkaku_all'])
                        );
                        $this->global_errors[] = $this->h($value . $this->config['error_zenkaku_all']);
                    }
                }
            }
        }

        // メールアドレスチェック
        if (isset($this->post['email'])) {
            foreach ($this->post['email'] as $value) {
                $this->tpl->set("email.$value", false);

                if (!empty($this->post[$value])) {
                    $this->post[$value] = mb_convert_kana($this->post[$value], 'a');
                    $this->post[$value] = $this->deleteCrlf($this->post[$value]);

                    if (!$this->isEmail($this->post[$value])) {
                        $this->tpl->set("email.$value", $this->h($value . $this->config['error_email']));
                        $this->global_errors[] = $this->h($value . $this->config['error_email']);
                    }
                }
            }
        }

        // 自動返信メールの宛先（ $this->post[$this->config['auto_reply_email']] ）のメールアドレスチェック
        if (!empty($this->post[$this->config['auto_reply_email']])) {
            $this->post[$this->config['auto_reply_email']] = mb_convert_kana($this->post[$this->config['auto_reply_email']], 'a');
            $this->post[$this->config['auto_reply_email']] = $this->deleteCrlf($this->post[$this->config['auto_reply_email']]);

            if (!$this->isEmail($this->post[$this->config['auto_reply_email']])) {
                $this->tpl->set("email." . $this->config['auto_reply_email'], $this->h($this->config['auto_reply_email'] . $this->config['error_email']));

                if (!in_array($this->h($this->config['auto_reply_email'] . $this->config['error_email']), $this->global_errors, true)) {
                    $this->global_errors[] = $this->h($this->config['auto_reply_email'] . $this->config['error_email']);
                }
            }
        }

        // 一致チェック
        if (isset($this->post['match'])) {
            foreach ($this->post['match'] as $value) {
                $array = preg_split('/\s|,/', $value);
                $this->tpl->set("match.$array[0]", false);

                if ((!empty($this->post[$array[0]]) || !empty($this->post[$array[1]]))
                    && $this->post[$array[0]] != $this->post[$array[1]]
                ) {
                    $this->tpl->set("match.$array[0]", $this->h($array[0] . $this->config['error_match']));
                    $this->global_errors[] = $this->h($array[0] . $this->config['error_match']);
                }
            }
        }

        // 文字数チェック
        if (isset($this->post['len'])) {
            foreach ($this->post['len'] as $value) {
                $array = preg_split('/\s|,/', $value);
                $delim = explode('-', $array[1]);
                $delim = array_map('intval', $delim);
                $this->tpl->set("len.$array[0]", false);

                if (!empty($this->post[$array[0]]) && !$this->isAllowLen($this->post[$array[0]], $delim)) {
                    if (empty($delim[0])) {
                        $error_len = str_replace('{文字数}', "$delim[1]文字以下", $this->config['error_len']);
                    } elseif (empty($delim[1])) {
                        $error_len = str_replace('{文字数}', "$delim[0]文字以上", $this->config['error_len']);
                    } else {
                        if ($delim[0] === $delim[1]) {
                            $error_len = str_replace('{文字数}', "$delim[0]文字", $this->config['error_len']);
                        } else {
                            $error_len = str_replace('{文字数}', "$delim[0]〜$delim[1]文字", $this->config['error_len']);
                        }
                    }

                    $this->tpl->set("len.$array[0]", $this->h($array[0] . $error_len));
                    $this->global_errors[] = $this->h($array[0] . $error_len);
                }
            }
        }

        // URL チェック
        if (isset($this->post['url'])) {
            foreach ($this->post['url'] as $value) {
                $this->tpl->set("url.$value", false);

                if (!empty($this->post[$value])) {
                    $this->post[$value] = mb_convert_kana($this->post[$value], 'a');
                    $this->post[$value] = $this->deleteCrlf($this->post[$value]);

                    if (!$this->isUrl($this->post[$value])) {
                        $this->tpl->set("url.$value", $this->h($value . $this->config['error_url']));
                        $this->global_errors[] = $this->h($value . $this->config['error_url']);
                    }
                }
            }
        }

        // 整数範囲チェック
        if (isset($this->post['num_range'])) {
            foreach ($this->post['num_range'] as $value) {
                $array = preg_split('/\s|,/', $value);
                $delim = explode('-', $array[1]);
                $delim = array_map('intval', $delim);
                $this->tpl->set("num_range.$array[0]", false);

                if ($this->post[$array[0]] !== '') {
                    // 数字チェック
                    $this->post[$array[0]] = mb_convert_kana($this->post[$array[0]], 'n');

                    if (!$this->isNum($this->post[$array[0]])) {
                        $this->tpl->set("num_range.$array[0]", $this->h($array[0] . $this->config['error_num']));
                        $this->global_errors[] = $this->h($array[0] . $this->config['error_num']);
                    } else {
                        if (!$this->isAllowNumRange($this->post[$array[0]], $delim)) {
                            if ($delim[0] === $delim[1]) {
                                $error_num_range = str_replace(
                                    '{範囲}',
                                    "ちょうど{$delim[0]}",
                                    $this->config['error_num_range']
                                );
                            } else {
                                if ($delim[1] === 0) {
                                    $error_num_range = str_replace(
                                        '{範囲}',
                                        "{$delim[0]}以上",
                                        $this->config['error_num_range']
                                    );
                                } else {
                                    $error_num_range = str_replace(
                                        '{範囲}',
                                        "{$delim[0]}以上、{$delim[1]}以下",
                                        $this->config['error_num_range']
                                    );
                                }
                            }

                            $this->tpl->set("num_range.$array[0]", $this->h($array[0] . $error_num_range));
                            $this->global_errors[] = $this->h($array[0] . $error_num_range);
                        }
                    }
                }
            }
        }

        // ファイル添付を利用する場合
        if ($this->config['file']) {
            // ファイルの削除
            if (isset($this->post['file_remove'])) {
                foreach ($this->post['file_remove'] as $value) {
                    $tmp_name = $this->post['file'][$value]['tmp_name'];

                    if (is_file($this->config['tmp_dir'] . $tmp_name) &&
                        preg_match('/\A' . $this->config['file_name_prefix'] . '/', $tmp_name) &&
                        $this->isAllowFileExtension($tmp_name))
                    {
                        if (unlink($this->config['tmp_dir'] . $tmp_name)) {
                            $this->post['file'][$value]['tmp_name'] = '';
                            $this->post['file'][$value]['name'] = '';
                        } else {
                            $this->global_errors[] = $this->h($tmp_name . $this->config['error_file_remove']);
                        }
                    } else {
                        $this->global_errors[] = $this->h($tmp_name . $this->config['error_file_remove']);
                    }
                }
            }

            // 既にファイルがアップロードされている場合
            if (isset($this->post['file'])) {
                foreach ($this->post['file'] as $key => $value) {
                    if (isset($value['tmp_name'])) {
                        // single の場合
                        if (is_file($this->config['tmp_dir'] . $value['tmp_name'])) {
                            $this->tpl->set("$key.tmp_name", $this->h($value['tmp_name']));
                            $this->tpl->set("$key.name", $this->h($value['name']));
                            $this->files[$key] = array(
                                'tmp_name' => $this->h($value['tmp_name']),
                                'name' => $this->h($value['name'])
                            );
                        }
                    }
                }
            }

            // ファイルの入力必須チェック
            if (isset($this->post['file_required'])) {
                foreach ($this->post['file_required'] as $value) {
                    $this->tpl->set("file_required.$value", false);

                    if (empty($_FILES[$value]['tmp_name']) && empty($this->post['file'][$value]['tmp_name'])) {
                        $this->tpl->set(
                            "file_required.$value",
                            $this->h($value . $this->config['error_file_required'])
                        );
                        $this->global_errors[] = $this->h($value . $this->config['error_file_required']);
                    }
                }
            }

            // ファイルのアップロード
            if (isset($_FILES)) {
                foreach ($_FILES as $key => $value) {
                    $file_error = array();
                    $this->tpl->set("file.$key", false);

                    if (!is_array($value['tmp_name'])) {
                        // single の場合
                        if (!empty($value['tmp_name'])) {
                            // 拡張子のチェック
                            if (!empty($this->config['file_allow_extension']) &&
                                !$this->isAllowFileExtension($value['name']))
                            {
                                $file_error[] = $this->h($key . $this->config['error_file_extension']);
                                $this->global_errors[] = $this->h($key . $this->config['error_file_extension']);
                            }

                            // 空ファイルのチェック
                            if ($value['size'] === 0) {
                                $file_error[] = $this->h($key . $this->config['error_file_empty']);
                                $this->global_errors[] = $this->h($key . $this->config['error_file_empty']);
                            }

                            // ファイルサイズのチェック
                            if ($value['size'] > $this->config['file_max_size']) {
                                $file_error[] = $this->h($key . str_replace(
                                    '{ファイルサイズ}',
                                    $this->getFormatedBytes($this->config['file_max_size']),
                                    $this->config['error_file_max_size']));
                                $this->global_errors[] = $this->h($key . str_replace(
                                    '{ファイルサイズ}',
                                    $this->getFormatedBytes($this->config['file_max_size']),
                                    $this->config['error_file_max_size']));
                            }

                            // エラーを判別
                            if (count($file_error) > 0) {
                                // エラーがある場合、エラーメッセージをセット
                                $this->tpl->set("file.$key", $file_error);
                            } else {
                                // エラーが無い場合、ファイルを$config['tmp_dir']に移動
                                $tmp_name = $this->config['file_name_prefix'] . uniqid(rand()) .
                                    '_' . $value['name'];
                                $file_path = $this->config['tmp_dir'] . $tmp_name;

                                if (move_uploaded_file($value['tmp_name'], $file_path)) {
                                    $this->tpl->set("$key.tmp_name", $this->h($tmp_name));
                                    $this->tpl->set("$key.name", $this->h($value['name']));
                                    $this->files[$key] = array(
                                        'tmp_name' => $this->h($tmp_name),
                                        'name' => $this->h($value['name'])
                                    );
                                } else {
                                    // アップロードに失敗した場合
                                    $file_error[] = $this->h($key . ERROR_FILE_UPLOAD);
                                    $this->global_errors[] = $this->h($key . ERROR_FILE_UPLOAD);
                                    $this->tpl->set("file.$key", $file_error);
                                }
                            }
                        } else {
                            if (!isset($this->files[$key])) {
                                $this->files[$key] = false;
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * 表示画面を判別、$pageにセット
     */
    public function setPageName()
    {
        // セッションの判別
        if ($this->config['session']) {
            if (isset($_SESSION['transmit_mail_input']) && $_SESSION['transmit_mail_input']) {
                $this->session_flag = true;
            }
        } else {
            $this->session_flag = true;
        }

        // $page_name の判別
        if ($this->deny_flag) {
            // アクセス拒否
            $this->page_name === 'deny';
        } elseif ($this->config['checkmode'] && isset($this->get['checkmode'])) {
            // チェックモード
            $this->page_name = 'checkmode';
        } elseif (isset($this->get['file'])) {
            // ファイル表示
            $this->page_name = 'file';
        } elseif (!$this->session_flag) {
            // セッションが無い場合 入力画面
            $this->page_name = '';
        } elseif (count($this->post) > 0) {
            if ($this->global_errors) {
                // エラーがある場合 入力エラー画面
                $this->page_name = '';
            } elseif (isset($this->post['page']) &&
                ($this->post['page'] === 'input') &&
                !$this->global_errors)
            {
                // 再入力画面
                $this->page_name = '';
            } elseif (isset($this->post['page']) &&
                ($this->post['page'] === 'finish') &&
                !$this->global_errors)
            {
                // 完了画面
                $this->page_name = 'finish';
            } elseif (!$this->global_errors) {
                // エラーが無い場合 確認画面
                $this->page_name = 'confirm';
            }
        }
    }

    /**
     * 入力内容をテンプレートプロパティにセット
     */
    public function setTemplateProperty()
    {
        $params = array();
        $hiddens = array();

        if (empty($this->page_name)) {
            // 入力画面 or 入力エラー画面
            foreach ($this->post as $key => $value) {
                $this->tpl->set($key, $this->h($value));
            }
        } elseif ($this->page_name === 'confirm' || $this->page_name === 'finish') {
            // 確認画面 or 完了画面
            foreach ($this->post as $key => $value1) {
                if (!preg_match($this->exclusion_item_pattern(), $key)) {
                    if (is_array($value1)) {
                        $this->tpl->set("$key.array", array_map(array($this, 'h'), $value1));
                        $value2 = implode(', ', $value1);
                    } else {
                        $value2 = $value1;
                    }

                    $hidden = $this->getInputHidden($key, $value1);
                    $this->tpl->set("$key.key", $this->h($key));
                    $this->tpl->set("$key.value", $this->h($value2));
                    $this->tpl->set("$key.value.nl2br", nl2br($this->h($value2)));
                    $this->tpl->set("$key.hidden", $hidden);
                    $params[] = array(
                        'key' => $this->h($key),
                        'value' => $this->h($value2),
                        'value.nl2br' => nl2br($this->h($value2)),
                        'hidden' => $hidden
                    );
                    $hiddens[] = $hidden;
                }
            }
        }

        // $_FILES
        if ($this->config['file']) {
            $array = array();

            foreach ($this->files as $key => $value) {
                if (isset($value['tmp_name'])) {
                    // single の場合
                    $hidden_tmp_name = $this->getInputHidden('file[' . $key . '][tmp_name]', $value['tmp_name']);
                    $hidden_name = $this->getInputHidden('file[' . $key . '][name]', $value['name']);
                    $this->tpl->set("$key.key", $this->h($key));
                    $this->tpl->set("$key.tmp_name", $this->h($value['tmp_name']));
                    $this->tpl->set("$key.name", $this->h($value['name']));
                    $this->tpl->set("$key.hidden_tmp_name", $hidden_tmp_name);
                    $this->tpl->set("$key.hidden_name", $hidden_name);
                    $array[] = array(
                        'key' => $this->h($key),
                        'tmp_name' => $this->h($value['tmp_name']),
                        'name' => $this->h($value['name']),
                        'hidden_tmp_name' => $hidden_tmp_name,
                        'hidden_name' => $hidden_name
                    );
                    $hiddens[] = $hidden_tmp_name;
                    $hiddens[] = $hidden_name;
                }
            }

            $this->tpl->set('files', $array);
        }

        $this->tpl->set('params', $params);
        $this->tpl->set('hiddens', implode('', $hiddens));
        $this->tpl->set('_GET', $this->h($this->get));
        $this->tpl->set('_SERVER', $this->h($this->server));
    }

    /**
     * $page にあわせてHTMLを出力、メール送信
     */
    public function setTemplateAndSendMail()
    {
        // $page を判別
        if ($this->page_name === 'deny') {
            // アクセス拒否画面

            // エラーメッセージ
            $this->global_errors[] = $this->h($this->config['error_deny']);

            // エラー内容をテンプレートプロパティにセット
            $this->tpl->set('global_errors', $this->global_errors);

            // HTML を表示
            echo $this->tpl->fetch($this->config['tpl_error']);
        } elseif ($this->page_name === 'checkmode') {
            // チェックモードを表示
            echo $this->getCheckmode();
        } elseif ($this->page_name === 'file') {
            // ファイル表示
            $file_path = $this->config['tmp_dir'] . basename($this->get['file']);

            if (is_file($file_path)) {
                if ((filemtime($file_path) + $this->config['file_retention_period']) < time()) {
                    // 保存期間を超えている場合
                    $this->global_errors[] = $this->h($this->get['file'] . $this->config['error_file_over_the_period']);
                } else {
                    // ファイルを表示
                    header('Content-type: ' . $this->getMimeType($this->get['file']));
                    readfile($file_path);
                    exit();
                }
            } else {
                // ファイルが存在しない
                $this->global_errors[] = $this->h($this->get['file'] . $this->config['error_file_not_exist']);
            }

            // エラー内容をテンプレートプロパティにセット
            $this->tpl->set('global_errors', $this->global_errors);

            // HTMLを表示
            echo $this->tpl->fetch($this->config['tpl_error']);
        } elseif ($this->page_name === 'finish') {
            // メール送信
            $this->sendMail();

            // 自動返信メール送信
            if ($this->config['auto_reply'] && !empty($this->post[$this->config['auto_reply_email']])) {
                $this->sendMail(true);
            }

            // 添付ファイルを削除
            if ($this->config['file']) {
                foreach ($this->files as $file) {
                    unlink($this->config['tmp_dir'] . $file['tmp_name']);
                }
            }

            // CSV の出力
            if ($this->config['csv_output']) {
                $this->putCsv($this->post);
            }

            // セッションを破棄
            if ($this->config['session']) {
                if (isset($_COOKIE[session_name()])) {
                    setcookie(
                        session_name(),
                        '',
                        time() - 42000,
                        $this->config['index_dir'],
                        $this->server['HTTP_HOST']
                    );
                }

                $_SESSION = array();
                session_destroy();
            }

            // エラー判別
            if ($this->global_errors) {
                // エラーの場合

                // エラー内容をテンプレートプロパティにセット
                $this->tpl->set('global_errors', $this->global_errors);

                // HTML を表示
                echo $this->tpl->fetch($this->config['tpl_error']);
            } else {
                // エラーがない場合

                // 完了画面を表示
                echo $this->tpl->fetch($this->config['tpl_finish']);
            }
        } elseif ($this->page_name === 'confirm') {
            // 確認画面

            // HTML を表示
            echo $this->tpl->fetch($this->config['tpl_confirm']);
        } else {
            // 入力画面 or 入力エラー画面

            // セッションによる多重送信防止機能
            if ($this->config['session']) {
                $_SESSION['transmit_mail_input'] = true;
            }

            // エラー内容をテンプレートプロパティにセット
            $this->tpl->set('global_errors', $this->global_errors);

            // HTMLを表示
            echo $this->tpl->fetch($this->config['tpl_input']);
        }
    }

    /**
     * メール送信
     *
     * @params array
     */
    public function sendMail($is_auto_reply = false)
    {
        // Qdmail と Qdsmpt を読み込む
        require_once dirname(__FILE__) . '/qdmail.php';
        require_once dirname(__FILE__) . '/qdsmtp.php';

        // Qdmail の設定
        $this->mail = new Qdmail();
        $this->mail->errorDisplay(false);
        $this->mail->errorlogPath($this->config['log_dir']);
        $this->mail->errorlogLevel(3);
        $this->mail->errorlogFilename('qdmail_error.log');
        $this->mail->smtpObject()->error_display = false;

        // Qdsmpt の設定
        $this->smtp = new QdSmtp();
        $this->smtp->pop3TimeFilename($this->config['tmp_dir'] . 'qdsmtp.time');
        $this->mail->setSmtpObject($smtp);

        if ($is_auto_reply) {
            // 自動返信メールの場合

            // 宛先
            if (!empty($this->post[$this->config['auto_reply_email']])) {
                $to_email = $this->post[$this->config['auto_reply_email']];
            } else {
                $to_email = $this->config['to_email'];
            }

            // 件名
            $to_subject = $this->config['auto_reply_subject'];

            if (empty($to_subject)) {
                $to_subject = $this->config['to_subject'];
            }

            // メール本文
            $body = $this->tpl->fetch($this->config['mail_auto_reply_body']);
            $body = $this->hd($body);

            // メール送信元
            $from_email = $this->config['auto_reply_from_email'];

            if (empty($from_email)) {
                $from_email = $this->config['to_email'];
            }

            // $config['auto_reply_name'] の設定がある場合
            if (!empty($this->config['auto_reply_name'])) {
                $this->mail->from($from_email, $this->config['auto_reply_name']);
            } else {
                $this->mail->from($from_email);
            }
        } else {
            // 宛先
            $to_email = $this->config['to_email'];

            // 件名
            $to_subject = $this->config['to_subject'];

            // メール本文
            $body = $this->tpl->fetch($this->config['mail_body']);
            $body = $this->hd($body);

            // メール送信元
            if (!empty($this->post[$this->config['auto_reply_email']])) {
                $from_email = $this->post[$this->config['auto_reply_email']];
            } else {
                $from_email = $to_email;
            }

            $this->mail->from($from_email);

            // CC メールアドレスの設定がある場合
            if (!empty($this->config['cc_email'])) {
                $this->mail->cc($this->config['cc_email']);
            }

            // BCC メールアドレスの設定がある場合
            if (!empty($this->config['bcc_email'])) {
                $this->mail->bcc($this->config['bcc_email']);
            }
        }

        // メール送信内容
        $this->mail->to($to_email);
        $this->mail->subject($to_subject);
        $this->mail->text($body);

        // 添付ファイル機能を利用する場合
        if ($this->config['file']) {
            foreach ($this->files as $file) {
                $attach[] = array(
                    'PATH' => $this->config['tmp_dir'] . $file['tmp_name'],
                    'NAME' => $file['name']
                );
            }

            if (isset($attach)) {
                $this->mail->attach($attach);
            }
        }

        // 外部SMTPを利用する場合
        if ($this->config['smtp']) {
            $this->mail->smtp(true);
            $this->mail->smtpServer(
                array(
                    'host' => $this->config['smtp_host'],
                    'port' => $this->config['smtp_port'],
                    'protocol' => $this->config['smtp_protocol'],
                    'user' => $this->config['smtp_user'],
                    'pass' => $this->config['smtp_password'],
                    'from' => $from_email
                )
            );
        }

        // メール送信
        $result = $this->mail->send();

        // 送信できなかった場合
        if (!$result) {
            if ($is_auto_reply) {
                // エラーメッセージ
                $this->global_errors[] = $this->config['error_failure_send_mail_auto_reply'];

                // 接頭辞
                $suffix = 'autoreply';
                $data = $this->config['error_failure_send_mail_auto_reply'];
            } else {
                // エラーメッセージ
                $this->global_errors[] = $this->config['error_failure_send_mail'];

                // 接頭辞
                $suffix = 'sendmail';
                $data = $this->config['error_failure_send_mail'];
            }

            // ログの内容
            $data .= "\n\n" .
                "--\n\n" .
                "【宛先】\n" .
                $to_email . "\n\n" .
                "【件名】\n" .
                $to_subject . "\n\n" .
                "【本文】\n" .
                $body;

            // 添付ファイルがある場合
            if ($this->config['file']) {
                foreach ($this->files as $key => $file) {
                    if (copy($this->config['tmp_dir'] . $file['tmp_name'],
                        $this->config['log_dir'] . $file['tmp_name']))
                    {
                        $data .= "\n\n" .
                            "【" . $key . "】\n" .
                            "ファイル名: " . $file['name'] . "\n" .
                            "一時保存ファイル名: " . $file['tmp_name'];
                    } else {
                        $data .= "\n\n" .
                            "【" . $key . "】\n" .
                            "ファイルの保存に失敗しました";
                    }
                }
            }

            // ログ出力
            $this->putErrorLog($data, $suffix);
        }
    }

    /**
     * $this->EXCLUSION_ITEM を正規表現形式に変換した文字列を返す
     */
    public function exclusion_item_pattern() {
        $array = json_decode($this->exclusion_item);
        $array = array_map(function($string) {
            return '\A' . $string . '\z';
        }, $array);

        return '/' . implode('|', $array) . '/' . $this->config['reg_option'];
    }

    /**
     * 半角文字を判別
     *
     * @param string $string
     * @return boolean
     */
    public function isHankaku($string)
    {
        return preg_match('/\A[!-~]*\z/' . $this->config['reg_option'], $string);
    }

    /**
     * 半角英数字を判別
     *
     * @param string $string
     * @return boolean
     */
    public function isHankakuEisu($string)
    {
        return preg_match('/\A[a-zA-Z0-9]*\z/' . $this->config['reg_option'], $string);
    }

    /**
     * 半角英字を判別
     *
     * @param string $string
     * @return boolean
     */
    public function isHankakuEiji($string)
    {
        return preg_match('/\A[a-zA-Z]*\z/' . $this->config['reg_option'], $string);
    }

    /**
     * 数字を判別
     *
     * @param string $string
     * @return boolean
     */
    public function isNum($string)
    {
        return preg_match('/\A[0-9]*\z/' . $this->config['reg_option'], $string);
    }

    /**
     * 数字とハイフンを判別
     *
     * @param string $string
     * @return boolean
     */
    public function isNumHyphen($string)
    {
        return preg_match('/\A[0-9-]*\z/' . $this->config['reg_option'], $string);
    }

    /**
     * ひらがなを判別
     *
     * @param string $string
     * @return boolean
     */
    public function isHiragana($string)
    {
        return preg_match('/\A[ぁ-ゞ]*\z/' . $this->config['reg_option'], $string);
    }

    /**
     * 全角カタカナを判別
     *
     * @param string $string
     * @return boolean
     */
    public function isZenkakuKatakana($string)
    {
        return preg_match('/\A[ァ-ヶー]*\z/' . $this->config['reg_option'], $string);
    }

    /**
     * 全角文字を含むか判別
     *
     * @param string $string
     * @return boolean
     */
    public function isZenkaku($string)
    {
        return preg_match('/[^ -~｡-ﾟ]/' . $this->config['reg_option'], $string);
    }

    /**
     * 全て全角文字かを判別
     *
     * @param string $string
     * @return boolean
     */
    public function isZenkakuAll($string)
    {
        return preg_match('/\A[^\x01-\x7E]*\z/' . $this->config['reg_option'], $string);
    }

    /**
     * メールアドレスの書式を判別
     *
     * @param string $string
     * @return boolean
     */
    public function isEmail($string)
    {
        return preg_match('/\A[a-zA-Z0-9.!#$%&\'*+\/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*\z/i' . $this->config['reg_option'], $string);
    }

    /**
     * URL の書式を判別
     *
     * @param string $string
     * @return boolean
     */
    public function isUrl($string)
    {
        return preg_match('/\Ahttps?:\/\/[^\s]+\z/i' . $this->config['reg_option'], $string);
    }

    /**
     * 許可する文字数の判別
     *
     * @param string $string
     * @param array $array
     * @return boolean
     */
    public function isAllowLen($string, $array)
    {
        if (empty($array[0])) {
            if (mb_strlen($string) > $array[1]) {
                return false;
            }
        } elseif (empty($array[1])) {
            if (mb_strlen($string) < $array[0]) {
                return false;
            }
        } else {
            if ((mb_strlen($string) < $array[0]) || (mb_strlen($string) > $array[1])) {
                return false;
            }
        }

        return true;
    }

    /**
     * 許可する整数範囲の判別
     *
     * @param integer $integer
     * @param array $array
     * @return boolean
     */
    public function isAllowNumRange($integer, $array)
    {
        if ($array[0] >= 0) {
            if ($integer < $array[0]) {
                return false;
            }
        }

        if ($array[1] > 0) {
            if ($integer > $array[1]) {
                return false;
            }
        }

        return true;
    }

    /**
     * 許可する添付ファイルの拡張子を判別
     *
     * @param string $string
     * @return boolean
     */
    public function isAllowFileExtension($string)
    {
        return preg_match('/\.' . str_replace(',', '$|\.', $this->config['file_allow_extension']) . '\z/i' . $this->config['reg_option'], $string);
    }

    /**
     * 拡張子から MIME Type を判別
     *
     * @param string $file_name
     * @return string
     */
    public function getMimeType($file_name)
    {
        preg_match('/\.([a-z0-9]{2,4})\z/i', $file_name, $matches);
        $suffix = strtolower($matches[1]);

        switch ($suffix) {
            case 'jpg':
            case 'jpeg':
            case 'jpe':
                return 'image/jpeg';
            case 'png':
            case 'gif':
            case 'bmp':
            case 'tiff':
                return 'image/' . $suffix;
            case 'css':
                return 'text/css';
            case 'js':
                return 'application/x-javascript';
            case 'json':
                return 'application/json';
            case 'xml':
                return 'application/xml';
            case 'doc':
            case 'docx':
                return 'application/msword';
            case 'xls':
            case 'xlsx':
            case 'xlt':
            case 'xlm':
            case 'xld':
            case 'xla':
            case 'xlc':
            case 'xlw':
            case 'xll':
                return 'application/vnd.ms-excel';
            case 'ppt':
            case 'pptx':
            case 'pps':
                return 'application/vnd.ms-powerpoint';
            case 'rtf':
                return 'application/rtf';
            case 'pdf':
                return 'application/pdf';
            case 'html':
            case 'htm':
            case 'php':
                return 'text/html';
            case 'txt':
                return 'text/plain';
            case 'mpeg':
            case 'mpg':
            case 'mpe':
                return 'video/mpeg';
            case 'mp3':
                return 'audio/mpeg3';
            case 'wav':
                return 'audio/wav';
            case 'aiff':
            case 'aif':
                return 'audio/aiff';
            case 'avi':
                return 'video/msvideo';
            case 'wmv':
                return 'video/x-ms-wmv';
            case 'mov':
                return 'video/quicktime';
            case 'zip':
                return 'application/zip';
            case 'tar':
                return 'application/x-tar';
            case 'swf':
                return 'application/x-shockwave-flash';
            default:
                return 'application/octet-stream';
        }
    }

    /*
     * 単位付きのバイト数を取得
     *
     * @param integer $bytes
     * @return string
     */
    public function getFormatedBytes($bytes)
    {
        if ($bytes < 1024) {
            return $bytes .'B';
        } elseif ($bytes < 1048576) {
            return round($bytes / 1024, 2) . 'KB';
        } elseif ($bytes < 1073741824) {
            return round($bytes / 1048576, 2) . 'MB';
        } elseif ($bytes < 1099511627776) {
            return round($bytes / 1073741824, 2) . 'GB';
        } elseif ($bytes < 1125899906842624) {
            return round($bytes / 1099511627776, 2) . 'TB';
        } elseif ($bytes < 1152921504606846976) {
            return round($bytes / 1125899906842624, 2) . 'PB';
        } elseif ($bytes < 1180591620717411303424) {
            return round($bytes / 1152921504606846976, 2) . 'EB';
        } elseif ($bytes < 1208925819614629174706176) {
            return round($bytes / 1180591620717411303424, 2) . 'ZB';
        } else {
            return round($bytes / 1208925819614629174706176, 2) . 'YB';
        }
    }

    /**
     * <input type="hidden"> を取得
     *
     * @param string $key
     * @param string|array $value
     * @return string
     */
    public function getInputHidden($key, $value)
    {
        $hidden = '<input type="hidden" name="{key}" value="{value}">';
        $result = '';

        if (is_array($value)) {
            foreach ($value as $value2) {
                if (!is_array($value2)) {
                    $result .= preg_replace(
                        array('/{key}/', '/{value}/'),
                        array($this->h($key) . '[]', $this->h($value2)),
                        $hidden
                    );
                }
            }
        } else {
            $result .= preg_replace(
                array('/{key}/', '/{value}/'),
                array($this->h($key), $this->h($value)),
                $hidden
            );
        }

        return $result;
    }

    /**
     * ログ出力
     *
     * @param string $data
     * @param string $suffix
     * @return integer
     */
    public function putErrorLog($data, $suffix)
    {
        // ファイルロック
        $lock_file = $this->config['tmp_dir'] . 'lock';

        do {
            // 0.1 秒スリープ( 1000000 = 1 秒)
            usleep(100000);

            $lock_fp = @fopen($lock_file, 'w');
            $lock = @flock($lock_fp, LOCK_EX);
        } while (!$lock);

        // ファイル名
        $date_time = date('Ymd_His');
        $file_name = 'error_' . $date_time . '_' . $suffix;
        $log_file_name = $this->getLogFileName($file_name);

        // ファイル書き込み
        $bytes = file_put_contents($this->config['log_dir'] . $log_file_name . '.txt', $data);
        fclose($lock_fp);
        unlink($lock_file);
        return $bytes;
    }

    /**
     * ログファイルの存在を確認し、同名のファイルが存在した場合、連番をつけた別のファイル名を取得する
     *
     * @param string $file_name
     * @return string
     */
    public function getLogFileName($file_name)
    {
        $log_file_name = $file_name;

        if (is_file($log_file_name)) {
            $i = 1;
            $log_file_name = $file_name . '_' . $i;

            while (is_file($log_file_name)) {
                $i++;
                $log_file_name = $file_name . '_' . $i;
            }
        }

        return $log_file_name;
    }

    /*
     * CSV ファイルを保存
     *
     * @param array $values
     * @return
     */
    public function putCsv($values)
    {
        $csv_lines = array();

        foreach ($values as $key => $value) {
            if (!preg_match($this->exclusion_item_pattern(), $key)) {
                if (is_array($value)) {
                    $value = implode(' / ', $value);
                }

                // エンコード変換
                if ($this->config['csv_encode'] !== $this->config['charaset']) {
                    $csv_key = mb_convert_encoding($key,
                        $this->config['csv_encode'],
                        $this->config['charaset']);
                    $csv_value = mb_convert_encoding($value,
                        $this->config['csv_encode'],
                        $this->config['charaset']);
                    $csv_lines[$csv_key] = $csv_value;
                } else {
                    $csv_lines[$k] = $value;
                }
            }
        }

        // ファイル名
        $csv_file_name = $this->config['log_dir'] . $this->config['csv_file'];

        // ファイルロック
        $lock_file = $this->config['tmp_dir'] . 'lock';

        do {
            // 0.1 秒スリープ( 1000000 = 1 秒)
            usleep(100000);

            $lock_fp = @fopen($lock_file, 'w');
            $lock = @flock($lock_fp, LOCK_EX);
        } while (!$lock);

        // ファイルが存在するかどうかの確認
        $is_first_time = false;

        if (!is_file($csv_file_name)) {
            $is_first_time = true;
        }

        // ファイル書き込み
        $fp = fopen($csv_file_name, 'a');

        // 最初の書き込みの場合は、ヘッダーも追加する
        if ($is_first_time) {
            $bytes = fputcsv($fp, array_keys($csv_lines));
        }

        $bytes = fputcsv($fp, array_values($csv_lines));
        fclose($fp);

        // ファイルロックの終了処理
        fclose($lock_fp);
        unlink($lock_file);

        return $bytes;
    }

    /**
     * ヌルバイトの削除
     *
     * @param string $string
     * @return string
     */
    public function deleteNullbyte($string)
    {
        if (is_array($string)) {
            return array_map(array($this, 'deleteNullbyte'), $string);
        }
        return str_replace("\0", '', $string);
    }

    /**
     * magic_quotes_gpc が on の場合、バックスラッシュ（ \ ）を削除
     *
     * @param string $string
     * @return string
     */
    public function safeStripSlashes($string)
    {
        if (get_magic_quotes_gpc()) {
            if (is_array($string)) {
                return array_map(array($this, 'safeStripSlashes'), $string);
            } else {
                return stripslashes($string);
            }
        }
        return $string;
    }

    /**
     * 空白文字の削除
     *
     * @param string $string
     * @return string
     */
    public function deleteBlank($string)
    {
        if (is_array($string)) {
            return array_map(array($this, 'deleteBlank'), $string);
        }
        return preg_replace('/\s/' . $this->config['reg_option'], '', $string);
    }

    /**
     * 改行コードの削除
     *
     * @param string $string
     * @return string
     */
    public function deleteCrlf($string)
    {
        if (is_array($string)) {
            return array_map(array($this, 'deleteCrlf'), $string);
        }
        return preg_replace('/\r|\n/' . $this->config['reg_option'], '', $string);
    }

    /**
     * htmlentities のショートハンド
     *
     * @param string $string
     * @return string
     */
    public function h($string)
    {
        if (is_array($string)) {
            return array_map(array($this, 'h'), $string);
        }
        return htmlentities($string, ENT_QUOTES, $this->config['charaset']);
    }

    /**
     * html_entity_decode のショートハンド
     *
     * @param string $string
     * @return string
     */
    public function hd($string)
    {
        if (is_array($string)) {
            return array_map(array($this, 'hd'), $string);
        }
        return html_entity_decode($string, ENT_QUOTES, $this->config['charaset']);
    }

    /**
     * チェックモードを取得
     *
     * @return string
     */
    public function getCheckmode()
    {
        // 変数
        $html = '';
        $ok = 'OK';
        $ng = '<span style="color:#f00;">NG</span>';

        // HTML生成
        $html .= <<<EOL
<html>
<head>
<meta charset="utf-8">
<title>チェックモード</title>
<style>
table {
    margin-bottom: 1.5em;
    border-collapse: collapse;
    border-spacing: 0;
}
table tr:nth-child(even) {
    background-color: #eee;
}
table tr:hover {
    background: #ddd;
}
table thead tr {
    background: #bbb;
}
table th {
    padding: .5em;
    border: 1px solid #000;
    text-align: left;
}
table td {
    padding: .5em;
    border: 1px solid #000;
}
</style>
</head>
<body>
<h1>チェックモード</h1>
EOL;

        // システム情報
        $html .= '<h2>システム情報</h2>';
        $html .= '<ul>';
        $html .= '<li>システム名: ' . self::SYSTEM_NAME . '</li>';
        $html .= '<li>バージョン: ' . self::VERSION . '</li>';
        $html .= '</ul>';

        // sendmail
        $html .= '<h2>sendmail</h2>';
        $html .= '<ul>';
        $html .= '<li>' . ini_get('sendmail_path') . '</li>';
        $html .= '</li>';
        $html .= '</ul>';

        // safe_mode
        $html .= '<h2>セーフモード</h2>';
        $html .= '<ul>';
        $html .= (ini_get('safe_mode')) ? '<li>On</li>' : '<li>Off</li>';
        $html .= '</li>';
        $html .= '</ul>';

        // HTMLテンプレート
        $html .= '<h2>HTMLテンプレート</h2>';
        $html .= '<ul>';

        // HTMLテンプレート 入力画面
        $html .= '<li>' . $this->config['tpl_input'] . ': ';
        $html .= (is_file($this->config['tpl_input'])) ? $ok : $ng;
        $html .= '</li>';

        // HTMLテンプレート 確認画面
        $html .= '<li>' . $this->config['tpl_confirm'] . ': ';
        $html .= (is_file($this->config['tpl_confirm'])) ? $ok : $ng;
        $html .= '</li>';

        // HTMLテンプレート 完了画面
        $html .= '<li>' . $this->config['tpl_finish'] . ': ';
        $html .= (is_file($this->config['tpl_finish'])) ? $ok : $ng;
        $html .= '</li>';

        // HTMLテンプレート エラー画面
        $html .= '<li>' . $this->config['tpl_error'] . ': ';
        $html .= (is_file($this->config['tpl_error'])) ? $ok : $ng;
        $html .= '</li>';

        // HTMLテンプレート ここまで
        $html .= '</ul>';

        // メールテンプレート
        $html .= '<h2>メールテンプレート</h2>';
        $html .= '<ul>';

        // メールテンプレート 送信メール
        $html .= '<li>' . $this->config['mail_body'] . ': ';
        $html .= (is_file($this->config['mail_body'])) ? $ok : $ng;
        $html .= '</li>';

        // メールテンプレート 自動返信メール
        $html .= '<li>' . $this->config['mail_auto_reply_body'] . ': ';
        $html .= (is_file($this->config['mail_auto_reply_body'])) ? $ok : $ng;

        // メールテンプレート ここまで
        $html .= '</ul>';

        // パーミッション
        $html .= '<h2>パーミッション</h2>';
        $html .= '<ul>';

        // パーミッション logsディレクトリ
        $html .= '<li>' . $this->config['log_dir'] . ': ';

        $permission = substr(sprintf('%o', fileperms($this->config['log_dir'])), -3);

        $html .= ($permission === '707') ? $ok : $ng;
        $html .= ' (' . $permission . ')</li>';

        // パーミッション tmp ディレクトリ
        $html .= '<li>' . $this->config['tmp_dir'] . ': ';

        $permission = substr(sprintf('%o', fileperms($this->config['tmp_dir'])), -3);

        $html .= ($permission === '707') ? $ok : $ng;
        $html .= ' (' . $permission . ')</li>';

        // パーミッション ここまで
        $html .= '</ul>';

        // 詳細チェックモード
        if ($this->config['checkmode'] === 2) {
            $html .= '<hr>';
            $html .= '<h1>詳細チェックモード</h1>';

            // 設定ファイル
            $html .= '<h2>設定ファイル</h2>';
            $html .= '<ul>';

            if ($this->config_file) {
                foreach ($this->config_file as $config_file) {
                    $html .= '<li>' . $config_file . ': ';
                    $html .= (is_file($config_file)) ? $ok : $ng;
                    $html .= '</li>';
                }
            } else {
                $html .= '<li><span style="color:#f00;">指定なし</span></li>';
            }

            $html .= '</ul>';

            // 設定情報
            $html .= '<h2>設定情報</h2>';
            $html .= '<table>';
            $html .= '<thead>';
            $html .= '<tr>';
            $html .= '<th>設定名</th><th>初期値</th><th>変更値</th>';
            $html .= '</tr>';
            $html .= '</thead>';
            $html .= '<tbody>';

            foreach ($this->default_config as $key => $value) {
                $html .= '<tr>';
                $html .= '<th>' . $key . '</th>';

                if (is_bool($value)) {
                    if ($value) {
                        $html .= '<td>true</td>';
                    } else {
                        $html .= '<td>false</td>';
                    }
                } else {
                    $html .= '<td>' . $value . '</td>';
                }

                if (isset($this->loaded_config[$key])) {
                    if (is_bool($this->loaded_config[$key])) {
                        if ($this->loaded_config[$key]) {
                            $html .= '<td>true</td>';
                        } else {
                            $html .= '<td>false</td>';
                        }
                    } else {
                        $html .= '<td>' . $this->loaded_config[$key] . '</td>';
                    }
                } else {
                    $html .= '<td></td>';
                }

                $html .= '</tr>';
            }

            $html .= '</tbody>';
            $html .= '</table>';
        }
        // 詳細チェックモード ここまで

        $html .= '</body>';
        $html .= '</html>';

        return $html;
    }
}
