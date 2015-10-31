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
            if (!empty($this->post['お名前'])) {
                $this->config['to_subject'] = $this->post['お名前'] . '様からの' . $this->config['to_subject'];
            }
        }
    }
}
