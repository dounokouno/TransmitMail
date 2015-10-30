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
        "file_required",
        "date"
    ]';

    /**
     * 入力内容をチェックのあとの処理
     */
    public function afterCheckInput()
    {
        // 日付チェック
        if (isset($this->post['date'])) {
            foreach ($this->post['date'] as $value) {
                $this->tpl->set("date.$value", false);

                if (!empty($this->post[$value])) {
                    if (!$this->isDate($this->post[$value])) {
                        $this->tpl->set("date.$value", $this->h($value . $this->config['error_date']));
                        $this->global_errors[] = $this->h($value . $this->config['error_date']);
                    }
                }
            }
        }
    }

    /**
     * 存在する日付の判別
     *
     * @param string $string
     * @return boolean
     */
    public function isDate($string)
    {
        $array = explode('-', $string);
        $year = isset($array[0]) ? $array[0] : null;
        $month = isset($array[1]) ? $array[1] : null;
        $day = isset($array[2]) ? $array[2] : null;

        return checkdate($month, $day, $year);
    }
}
