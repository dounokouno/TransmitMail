<?php
/**
 * CSRF test
 *
 * @package    TransmitMail
 * @subpackage PHPUnit with Symfony panther
 * @license    MIT License
 * @copyright  TransmitMail development team
 * @link       https://github.com/dounokouno/TransmitMail
 */

namespace TransmitMail\Tests;

class CsrfTest extends TransmitMailPantherTestCase
{
    private $selector = 'input[type="text"][name="csrf_token"]';
    private $errorMessage;

    /**
     * セットアップ
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->errorMessage = $this->tm->config['error_csrf'];
    }

    /**
     * CSRF 対策が有効の場合のテスト
     */
    public function testCsrfIsTrue()
    {
        // 入力フィールドの確認
        $this->assertNotEquals('', $this->getTargetValue());
        $this->assertIsObject($this->filter($this->selector));

        // エラーメッセージが表示されていないことの確認
        $this->assertStringNotContainsString($this->globalErrorMessage, $this->findElementAndGetText('#content'));
        $this->assertStringNotContainsString($this->errorMessage, $this->findElementAndGetText('#content'));

        // テストの実行（失敗する場合）
        $this->inputErrorTestForCsrfTest('');
        $this->inputErrorTestForCsrfTest(substr($this->getTargetValue(), -1, 1));
        $this->inputErrorTestForCsrfTest($this->tm->generateToken());

        // テストの実行（成功する場合）
        $this->inputSuccessTestForCsrfTest($this->getTargetValue());
    }

    /**
     * CSRF 対策が無効の場合のテスト
     */
    public function testCsrfIsFalse()
    {
        $this->tm->config['csrf'] = false;

        // エラーメッセージが表示されていないことの確認
        $this->assertStringNotContainsString($this->globalErrorMessage, $this->findElementAndGetText('#content'));
        $this->assertStringNotContainsString($this->errorMessage, $this->findElementAndGetText('#content'));

        // 入力フィールドの確認
        $this->assertNotEquals('', $this->getTargetValue());
        $this->assertIsObject($this->filter($this->selector));

        // テストの実行
        $this->inputSuccessTestForCsrfTest($this->getTargetValue());
    }

    /**
     * 入力エラーの場合のテスト
     */
    private function inputErrorTestForCsrfTest($value)
    {
        $this->findElementAndClear($this->selector);
        $this->findElementAndSetValue($this->selector, $value);
        $this->inputRequiredField();
        $this->submitInputForm();
        $this->assertStringContainsString($this->globalErrorMessage, $this->findElementAndGetText('#content'));
        $this->assertEquals($this->errorMessage, $this->findElementAndGetText('#content ul li'));
    }

    /**
     * 入力エラーにならない場合のテスト
     */
    private function inputSuccessTestForCsrfTest($value)
    {
        $hiddenFieldSelector = str_replace('[type="text"]', '[type="hidden"]', $this->selector);
        $this->inputRequiredField();
        $this->submitInputForm();
        $this->assertEquals($this->confirmPageTitle, $this->client->getTitle());
        $this->assertEquals($value, $this->findElementAndGetValue($hiddenFieldSelector));

        // 入力画面に戻る
        $this->returnInputPage();
        $this->assertEquals($value, $this->findElementAndGetValue($this->selector));
    }

    /**
     * 対象の input の値を取得
     */
    private function getTargetValue()
    {
        $this->crawler = $this->client->getCrawler();
        return $this->findElementAndGetValue($this->selector);
    }
}
