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
            require_once('vendor/autoload.php');

            $google_client = new Google_Client();
            $google_client->setApplicationName('TransmitMail');
            $google_client->setClientId($this->config['google_client_id']);
            $google_client->setAssertionCredentials(
                new Google_Auth_AssertionCredentials(
                    $this->config['google_client_email'],
                    ['https://spreadsheets.google.com/feeds','https://docs.google.com/feeds'],
                    file_get_contents(dirname(__FILE__) . '/../config/' . $this->config['google_client_key']),
                    $this->config['google_client_key_password']
                )
            );

            $google_client->getAuth()->refreshTokenWithAssertion();
            $token  = json_decode($google_client->getAccessToken());
            $access_token = $token->access_token;

            $service_request = new Google\Spreadsheet\DefaultServiceRequest($access_token);
            Google\Spreadsheet\ServiceRequestFactory::setInstance($service_request);

            $spreadsheet_service = new Google\Spreadsheet\SpreadsheetService();
            $spreadsheet_feed = $spreadsheet_service->getSpreadsheets();

            $spreadsheet = $spreadsheet_feed->getByTitle('TransmitMailの入力内容を保存するスプレッドシート');
            $worksheet_feed = $spreadsheet->getWorksheets();
            $worksheet = $worksheet_feed->getByTitle('シート1');
            $list_feed = $worksheet->getListFeed();

            $row = [
                'お名前' => $this->post['お名前'],
                '年齢' => $this->post['年齢'],
                'メールアドレス' => $this->post['メールアドレス'],
                '登録日' => date('Y/m/d H:i:s')
            ];

            $list_feed->insert($row);
        }
    }
}
