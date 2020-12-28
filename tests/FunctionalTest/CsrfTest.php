<?php
/**
 * CSRF test
 *
 * @package    TransmitMail
 * @subpackage PHPUnit with Selenium 2
 * @license    MIT License
 * @copyright  TransmitMail development team
 * @link       https://github.com/dounokouno/TransmitMail
 */

class CsrfTest extends TransmitMailFunctionalTest
{
    private $selector = 'input[type="text"][name="csrf_token"]';
    private $errorMessage = '';

    /**
     * コンストラクタ
     */
    public function __construct()
    {
        parent::__construct();
        $this->errorMessage = $this->tm->config['error_csrf'];
    }

    /**
     * CSRF 対策が有効の場合のテスト
     */
    public function testCsrfIsTrue()
    {
        $this->url('');

        // 入力フィールドの確認
        $this->assertNotEquals('', $this->getTargetValue());
        $this->assertInternalType('object', $this->byCssSelector($this->selector));

        // エラーメッセージが表示されていないことの確認
        $this->assertNotContains($this->globalErrorMessage, $this->byCssSelector('#content')->text());
        $this->assertNotContains($this->errorMessage, $this->byCssSelector('#content')->text());

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
        $this->url('');

        // エラーメッセージが表示されていないことの確認
        $this->assertNotContains($this->globalErrorMessage, $this->byCssSelector('#content')->text());
        $this->assertNotContains($this->errorMessage, $this->byCssSelector('#content')->text());

        // 入力フィールドの確認
        $this->assertNotEquals('', $this->getTargetValue());
        $this->assertInternalType('object', $this->byCssSelector($this->selector));

        // テストの実行
        $this->inputSuccessTestForCsrfTest($this->getTargetValue());
    }

    /**
     * 入力エラーの場合のテスト
     */
    private function inputErrorTestForCsrfTest($value)
    {
        $this->url('');
        $element = $this->byCssSelector($this->selector);
        $element->clear();
        $element->value($value);
        $this->inputRequiredField();
        $this->submitInputForm();
        $this->assertContains($this->globalErrorMessage, $this->byCssSelector('#content')->text());
        $this->assertEquals($this->errorMessage, $this->byCssSelector('#content ul li')->text());
    }

    /**
     * 入力エラーにならない場合のテスト
     */
    private function inputSuccessTestForCsrfTest($value)
    {
        $hiddenFieldSelector = str_replace('[type="text"]', '[type="hidden"]', $this->selector);
        $this->url('');
        $this->inputRequiredField();
        $this->submitInputForm();
        $this->assertEquals($this->confirmPageTitle, $this->title());
        $this->assertEquals($value, $this->byCssSelector($hiddenFieldSelector)->value());

        // 入力画面に戻る
        $this->returnInputPage();
        $this->assertEquals($value, $this->byCssSelector($this->selector)->value());
    }

    /**
     * 対象の input の値を取得
     */
    private function getTargetValue()
    {
        return $this->byCssSelector($this->selector)->value();
    }
}
