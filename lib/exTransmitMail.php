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
     * メール送信のあとの処理
     */
    public function afterSetTemplateAndSendMail()
    {
        if ($this->page_name === 'finish') {
            try {
                $dns = 'mysql:host=' . $this->config['db_host'] . ';dbname=' . $this->config['db_name'] . ';charset=utf8';
                $pdo = new PDO($dns, $this->config['db_user'], $this->config['db_password']);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                $sql = 'insert into ' . $this->config['db_table'] . ' (name, age, email, created_at) values (:name, :age, :email, :created_at)';

                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':name' => $this->post['お名前'],
                    ':age' => $this->post['年齢'],
                    ':email' => $this->post['メールアドレス'],
                    ':created_at' => date('Y-m-d H:i:s')
                ]);

            } catch(PDOException $e) {
                echo $e->getMessage();
            }
        }
    }
}
