<?php
/**
 * exTransmitMail クラス
 *
 * @package   TransmitMail
 * @license   MIT License
 * @copyright TAGAWA Takao, dounokouno@gmail.com
 * @link      https://github.com/dounokouno/TransmitMail
 */

class exTransmitMail extends TransmitMail
{
    /**
     * メール送信の前の処理
     */
    public function afterSetTemplateProperty()
    {
        if ($this->page_name === 'finish') {
            switch ($this->post['お問い合わせ内容']) {
                case '利用方法について':
                    $this->config['to_email'] = $this->config['to_email_support'];
                    break;
                case '資料請求':
                    $this->config['to_email'] = $this->config['to_email_material'];
                    break;
                case 'その他':
                default:
                    // 「その他」または選択がない場合は to_email 宛にメールを送信するので、ここでは特に何もしない
                    break;
            }
        }
    }
}
