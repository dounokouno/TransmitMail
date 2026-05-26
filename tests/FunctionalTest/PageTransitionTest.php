<?php
/**
 * Session impact test
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
    /**
     * 標準的な画面遷移（入力 -> 確認 -> 完了）のテスト
     */
    public function testStandardFlow()
    {
        // 1. 入力画面
        $this->assertEquals($this->topPageTitle, $this->client->getTitle());

        // 値を入力
        $this->findElementAndSetValue('input[type="text"][name="シングルラインインプット"]', 'テストユーザー');
        $this->inputRequiredField();

        // 2. 確認画面へ
        $this->submitInputForm();
        $this->assertEquals($this->confirmPageTitle, $this->client->getTitle());
        $this->assertStringContainsString('テストユーザー', $this->findElementAndGetText('#content table'));

        // 3. 完了画面へ
        $this->submitConfirmForm();
        $this->assertEquals('お問い合わせいただきありがとうございます | TransmitMail サンプル', $this->client->getTitle());
        $this->assertStringContainsString('お問い合わせいただき、ありがとうございます。', $this->findElementAndGetText('#content'));
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
        // TransmitMail は完了画面で session_destroy() するため、
        // リロードするとセッションがなくなり、setPageName() で入力画面（page_name = ''）になる
        $this->assertEquals($this->topPageTitle, $this->client->getTitle());
    }

    /**
     * セッションがない状態で直接アクセスした場合に入力画面に戻るテスト
     */
    public function testDirectAccessRedirectsToInput()
    {
        // セッションがない状態で直接確認画面にアクセスしようとしても入力画面に戻るはず
        // WebDriver は GET のみサポート
        $this->client->request('GET', '/?page_name=confirm');

        // セッション（transmit_mail_input）がないため、入力画面に戻るはず
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
}
