<?php
/**
 * Page transition and Email content test
 *
 * @package    TransmitMail
 * @subpackage PHPUnit with Symfony panther
 * @license    MIT License
 * @copyright  TransmitMail development team
 * @link       https://github.com/dounokouno/TransmitMail
 */

namespace TransmitMail\Tests;

class PageTransitionTest extends TransmitMailPantherTestCase
{
    private $mailpitApiUrl;

    protected function setUp(): void
    {
        parent::setUp();

        // MailPit の API ホストを決定
        // mailpit ホスト名が引ける場合は 'mailpit'、引けない場合は '127.0.0.1'
        $mailpitHost = gethostbyname('mailpit');
        if ($mailpitHost === 'mailpit' || $mailpitHost === false || $mailpitHost === '') {
            // ホスト名が引けない場合は localhost を使用
            $host = '127.0.0.1';
        } else {
            // ホスト名が引ける場合はサービス名を使用
            $host = 'mailpit';
        }
        $this->mailpitApiUrl = 'http://' . $host . ':8025/api/v1';
    }

    /**
     * 標準的な画面遷移（入力 -> 確認 -> 完了）とメール送信のテスト
     */
    public function testStandardFlowAndEmails()
    {
        // 0. MailPit をクリア
        $this->clearMailPitMessages();

        // 1. 入力画面
        $this->assertEquals($this->topPageTitle, $this->client->getTitle());

        // 値を入力
        $userName = 'テストユーザー ' . uniqid();
        $userEmail = 'user-' . uniqid() . '@example.jp';
        $this->findElementAndSetValue('input[type="text"][name="シングルラインインプット"]', $userName);
        $this->findElementAndSetValue('input[type="text"][name="メールアドレス"]', $userEmail);
        $this->inputRequiredField();

        // 2. 確認画面へ
        $this->submitInputForm();
        $this->assertEquals($this->confirmPageTitle, $this->client->getTitle());
        $this->assertStringContainsString($userName, $this->findElementAndGetText('#content table'));

        // 3. 完了画面へ
        $this->submitConfirmForm();
        $this->assertEquals('お問い合わせいただきありがとうございます | TransmitMail サンプル', $this->client->getTitle());
        $this->assertStringContainsString('お問い合わせいただき、ありがとうございます。', $this->findElementAndGetText('#content'));

        // 4. メールの検証
        // 送信処理の直後なので、少し待つ必要がある場合がある
        $messages = [];
        for ($i = 0; $i < 5; $i++) {
            $messages = $this->getMailPitMessages();
            if (count($messages) >= 2) {
                break;
            }
            usleep(500000); // 0.5秒待機
        }

        // 通常、管理者宛てと自動返信の2通が飛ぶはず
        $this->assertGreaterThanOrEqual(2, count($messages), '少なくとも2通のメールが送信されるべき');

        $adminMail = null;
        $autoReplyMail = null;

        foreach ($messages as $msg) {
            if ($msg['To'][0]['Address'] === 'info@example.com') {
                $adminMail = $msg;
            } elseif ($msg['To'][0]['Address'] === $userEmail) {
                $autoReplyMail = $msg;
            }
        }

        $this->assertNotNull($adminMail, '管理者宛てメールが見つかりません');
        $this->assertNotNull($autoReplyMail, '自動返信メールが見つかりません');

        // --- 管理者宛てメールの詳細検証 ---
        $this->assertStringContainsString('［株式会社テスト］お問い合わせ', $adminMail['Subject']);
        // 送信先 (To)
        $this->assertEquals('info@example.com', $adminMail['To'][0]['Address']);
        // 送信元 (From) - TransmitMail のデフォルトでは入力されたメールアドレスになる
        $this->assertEquals($userEmail, $adminMail['From']['Address']);

        $adminMailDetails = $this->getMailDetails($adminMail['ID']);
        // Reply-To の検証
        $this->assertArrayHasKey('ReplyTo', $adminMailDetails);

        // 本文の検証 (MIMEデコード済みの Text を使用)
        $adminMailBody = $adminMailDetails['Text'] ?? '';
        $this->assertStringContainsString($userName, $adminMailBody, '管理者宛てメール本文にユーザー名が含まれるべき');
        $this->assertStringContainsString($userEmail, $adminMailBody, '管理者宛てメール本文にメールアドレスが含まれるべき');


        // --- 自動返信メールの詳細検証 ---
        $this->assertStringContainsString('［株式会社テスト］お問い合わせありがとうございます', $autoReplyMail['Subject']);
        // 送信先 (To) - ユーザーのアドレス
        $this->assertEquals($userEmail, $autoReplyMail['To'][0]['Address']);
        // 送信元 (From) - 管理者のアドレス
        $this->assertEquals('info@example.com', $autoReplyMail['From']['Address']);

        $autoReplyMailDetails = $this->getMailDetails($autoReplyMail['ID']);
        // Reply-To の検証
        $this->assertArrayHasKey('ReplyTo', $autoReplyMailDetails);

        // 本文の検証 (MIMEデコード済みの Text を使用)
        $autoReplyMailBody = $autoReplyMailDetails['Text'] ?? '';
        $this->assertStringContainsString($userName, $autoReplyMailBody, '自動返信メール本文にユーザー名が含まれるべき');
        $this->assertStringContainsString($userEmail, $autoReplyMailBody, '自動返信メール本文にメールアドレスが含まれるべき');
    }

    /**
     * 入力画面に戻った際のデータ保持テスト
     */
    public function testDataPersistenceWhenBack()
    {
        // 1. 入力画面で値を入力
        $inputValue = '戻っても残っているかテスト';
        $this->findElementAndSetValue('input[type="text"][name="シングルラインインプット"]', $inputValue);
        $this->inputRequiredField();

        // 2. 確認画面へ
        $this->submitInputForm();
        $this->assertEquals($this->confirmPageTitle, $this->client->getTitle());

        // 3. 入力画面に戻る
        $this->returnInputPage();
        $this->assertEquals($this->topPageTitle, $this->client->getTitle());

        // 値が保持されているか確認
        $this->assertEquals($inputValue, $this->findElementAndGetValue('input[type="text"][name="シングルラインインプット"]'));
    }

    /**
     * 多重送信防止のテスト
     * 完了画面でリロードした際に入力画面に戻ることを確認
     */
    public function testPreventDoubleSubmission()
    {
        // 1. 完了画面まで進む
        $this->inputRequiredField();
        $this->submitInputForm();
        $this->submitConfirmForm();
        $this->assertEquals('お問い合わせいただきありがとうございます | TransmitMail サンプル', $this->client->getTitle());

        // 2. リロードする
        $this->client->getWebDriver()->navigate()->refresh();

        // 3. 入力画面に戻っていることを確認（セッションが破棄されているため）
        $this->assertEquals($this->topPageTitle, $this->client->getTitle());
    }

    /**
     * セッションがない状態で直接アクセスした場合に入力画面に戻るテスト
     */
    public function testDirectAccessRedirectsToInput()
    {
        // セッションがない状態で直接確認画面にアクセスしようとしても入力画面に戻るはず
        $this->client->request('GET', '/?page_name=confirm');
        $this->assertEquals($this->topPageTitle, $this->client->getTitle());
    }

    /**
     * 確認画面のフォームを送信する
     */
    protected function submitConfirmForm(): void
    {
        $form = $this->filter('form:has(input[type="hidden"][name="page_name"][value="finish"])')->first()->form();
        $this->client->submit($form);
        $this->crawler = $this->client->getCrawler();
    }

    /**
     * MailPit のメッセージを全削除
     */
    private function clearMailPitMessages()
    {
        $ch = curl_init($this->mailpitApiUrl . '/messages');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_exec($ch);
        curl_close($ch);
    }

    /**
     * MailPit のメッセージ一覧を取得
     */
    private function getMailPitMessages()
    {
        $content = @file_get_contents($this->mailpitApiUrl . '/messages');
        if ($content === false) {
            return [];
        }
        $data = json_decode($content, true);
        return $data['messages'] ?? [];
    }

    /**
     * 指定したIDのメール詳細を取得
     */
    private function getMailDetails($id)
    {
        $content = file_get_contents($this->mailpitApiUrl . '/message/' . $id);
        return json_decode($content, true);
    }
}
