<?php

/**
 * Basic test
 *
 * @package    TransmitMail
 * @subpackage PHPUnit with Symfony panther
 * @license    MIT License
 * @copyright  TransmitMail development team
 * @link       https://github.com/dounokouno/TransmitMail
 */

namespace TransmitMail\Tests;

class BasicTest extends TransmitMailPantherTestCase
{
    /**
     * URLを表示しタイトルをテスト
     */
    public function testTitle()
    {
        $this->assertEquals($this->topPageTitle, $this->client->getTitle());
    }

    /**
     * サンプル用フィールドの表示テスト
     */
    public function testSampleFields()
    {
        $this->assertEquals('', $this->filterAndGetValue('input[type="text"][name="シングルラインインプット"]'));
        $this->assertEquals('', $this->filterAndGetValue('textarea[name="マルチラインインプット"]'));
        $this->assertEquals('項目1', $this->filterAndGetValue('input[type="radio"][name="ラジオボタン"]:first-of-type'));
        $this->assertEquals('項目1', $this->filterAndGetValue('input[type="checkbox"][name="チェックボックス[]"]'));
        $this->assertEquals('項目1', $this->filterAndGetValue('select[name="セレクトメニュー"]'));
        $this->assertEquals('', $this->filterAndGetValue('select[name="マルチプルセレクトメニュー[]"]'));
        $this->assertEquals('', $this->filterAndGetValue('input[type="file"][name="ファイル1"]'));
        $this->assertEquals('', $this->filterAndGetValue('input[type="file"][name="ファイル2"]'));

        $this->assertEquals('', $this->filterAndGetValue('input[type="text"][name="入力必須"]'));
        $this->assertEquals('入力必須', $this->filterAndGetValue('input[type="hidden"][name="required[]"]'));

        $this->assertEquals('', $this->filterAndGetValue('input[type="text"][name="メールアドレス"]'));
        $this->assertEquals('メールアドレス', $this->filterAndGetValue('input[type="hidden"][name="email[]"]'));

        $this->assertEquals('', $this->filterAndGetValue('input[type="text"][name="半角文字"]'));
        $this->assertEquals('半角文字', $this->filterAndGetValue('input[type="hidden"][name="hankaku[]"]'));

        $this->assertEquals('', $this->filterAndGetValue('input[type="text"][name="半角英数字"]'));
        $this->assertEquals('半角英数字', $this->filterAndGetValue('input[type="hidden"][name="hankaku_eisu[]"]'));

        $this->assertEquals('', $this->filterAndGetValue('input[type="text"][name="半角英字"]'));
        $this->assertEquals('半角英字', $this->filterAndGetValue('input[type="hidden"][name="hankaku_eiji[]"]'));

        $this->assertEquals('', $this->filterAndGetValue('input[type="text"][name="数字"]'));
        $this->assertEquals('数字', $this->filterAndGetValue('input[type="hidden"][name="num[]"]'));

        $this->assertEquals('', $this->filterAndGetValue('input[type="text"][name="数字＋ハイフン"]'));
        $this->assertEquals('数字＋ハイフン', $this->filterAndGetValue('input[type="hidden"][name="num_hyphen[]"]'));

        $this->assertEquals('', $this->filterAndGetValue('input[type="text"][name="ひらがな"]'));
        $this->assertEquals('ひらがな', $this->filterAndGetValue('input[type="hidden"][name="hiragana[]"]'));

        $this->assertEquals('', $this->filterAndGetValue('input[type="text"][name="全角カタカナ"]'));
        $this->assertEquals('全角カタカナ', $this->filterAndGetValue('input[type="hidden"][name="zenkaku_katakana[]"]'));

        $this->assertEquals('', $this->filterAndGetValue('input[type="text"][name="全角文字を含むか"]'));
        $this->assertEquals('全角文字を含むか', $this->filterAndGetValue('input[type="hidden"][name="zenkaku[]"]'));

        $this->assertEquals('', $this->filterAndGetValue('input[type="text"][name="全て全角文字"]'));
        $this->assertEquals('全て全角文字', $this->filterAndGetValue('input[type="hidden"][name="zenkaku_all[]"]'));

        $this->assertEquals('', $this->filterAndGetValue('input[type="text"][name="3文字以上"]'));
        $this->assertEquals('3文字以上 3-', $this->filterAndGetValue('input[type="hidden"][name="len[]"][value="3文字以上 3-"]'));

        $this->assertEquals('', $this->filterAndGetValue('input[type="text"][name="3文字以下"]'));
        $this->assertEquals('3文字以下 -3', $this->filterAndGetValue('input[type="hidden"][name="len[]"][value="3文字以下 -3"]'));

        $this->assertEquals('', $this->filterAndGetValue('input[type="text"][name="3文字固定"]'));
        $this->assertEquals('3文字固定 3-3', $this->filterAndGetValue('input[type="hidden"][name="len[]"][value="3文字固定 3-3"]'));

        $this->assertEquals('', $this->filterAndGetValue('input[type="text"][name="6文字以上8文字以下"]'));
        $this->assertEquals('6文字以上8文字以下 6-8', $this->filterAndGetValue('input[type="hidden"][name="len[]"][value="6文字以上8文字以下 6-8"]'));

        $this->assertEquals('', $this->filterAndGetValue('input[type="text"][name="一致1"]'));
        $this->assertEquals('', $this->filterAndGetValue('input[type="text"][name="一致2"]'));
        $this->assertEquals('一致1 一致2', $this->filterAndGetValue('input[type="hidden"][name="match[]"][value="一致1 一致2"]'));

        $this->assertEquals('', $this->filterAndGetValue('input[type="text"][name="URL"]'));
        $this->assertEquals('URL', $this->filterAndGetValue('input[type="hidden"][name="url[]"][value="URL"]'));

        $this->assertEquals('', $this->filterAndGetValue('input[type="text"][name="3以下の数字"]'));
        $this->assertEquals('3以下の数字 -3', $this->filterAndGetValue('input[type="hidden"][name="num_range[]"][value="3以下の数字 -3"]'));

        $this->assertEquals('', $this->filterAndGetValue('input[type="text"][name="3以上の数字"]'));
        $this->assertEquals('3以上の数字 3-', $this->filterAndGetValue('input[type="hidden"][name="num_range[]"][value="3以上の数字 3-"]'));

        $this->assertEquals('', $this->filterAndGetValue('input[type="text"][name="ちょうど3の数字"]'));
        $this->assertEquals('ちょうど3の数字 3-3', $this->filterAndGetValue('input[type="hidden"][name="num_range[]"][value="ちょうど3の数字 3-3"]'));

        $this->assertEquals('', $this->filterAndGetValue('input[type="text"][name="1〜12の数字"]'));
        $this->assertEquals('1〜12の数字 1-12', $this->filterAndGetValue('input[type="hidden"][name="num_range[]"][value="1〜12の数字 1-12"]'));

        $this->assertEquals('', $this->filterAndGetValue('input[type="file"][name="ファイルの入力必須"]'));
        $this->assertEquals('ファイルの入力必須', $this->filterAndGetValue('input[type="hidden"][name="file_required[]"][value="ファイルの入力必須"]'));

        $this->assertEquals('', $this->filterAndGetValue('input[type="text"][name="郵便番号"]'));
        $this->assertEquals('郵便番号', $this->filterAndGetValue('input[type="hidden"][name="num_hyphen[]"][value="郵便番号"]'));
        $this->assertEquals('郵便番号 8', $this->filterAndGetValue('input[type="hidden"][name="len[]"][value="郵便番号 8"]'));

        $this->assertEquals('', $this->filterAndGetValue('input[type="text"][name="abcdefghijklnmopqrstuvwxyz"]'));

        $this->assertEquals('入力内容を確認する', $this->filterAndGetValue('input[type="submit"]'));
    }

    /**
     * x、yを含む項目名のテスト
     */
    public function testContainsXAndYField()
    {
        $selector = 'input[type="text"][name="abcdefghijklnmopqrstuvwxyz"]';
        $hiddenFieldSelector = str_replace('[type="text"]', '[type="hidden"]', $selector);
        $value = 'x、yを含む項目名への入力';

        // 入力フィールドの確認
        $this->assertEquals('', $this->filterAndGetValue($selector));
        $this->assertIsObject($this->filter($selector));

        // テストの実行
        $this->filterAndSetValue($selector, $value);
        $this->inputRequiredField();
        $this->submitInputForm();
        $this->assertEquals($this->confirmPageTitle, $this->client->getTitle());
        $this->assertStringContainsString($value, $this->filterAndGetText('#content table'));
        $this->assertEquals($value, $this->filterAndGetValue($hiddenFieldSelector));

        // 入力画面に戻る
        $this->returnInputPage();
        $this->assertEquals($value, $this->filterAndGetValue($selector));
    }

    /**
     * テンプレートエンジンの構文の入力テスト
     */
    public function testTemplateSyntaxes()
    {
        $selectors = [
            'text' => 'input[type="text"][name="シングルラインインプット"]',
            'textarea' => 'textarea[name="マルチラインインプット"]'
        ];

        // 入力エラーの場合
        foreach ($this->templateSyntaxInputPatterns as $value) {
            $this->filterAndSetValue($selectors['text'], $value);
            $this->filterAndSetValue($selectors['textarea'], $value);
            $this->submitInputForm();

            $this->client->waitForVisibility($selectors['text']);
            $this->assertEquals($value, $this->filterAndGetValue($selectors['text']));
            $this->filterAndClear($selectors['text']);

            $this->client->waitForVisibility($selectors['textarea']);
            $this->assertEquals($value, $this->filterAndGetValue($selectors['textarea']));
            $this->filterAndClear($selectors['textarea']);
        }

        // 成功の場合
        $this->inputSuccessTest($this->templateSyntaxInputPatterns, $selectors['text']);
        $this->inputSuccessTest($this->templateSyntaxInputPatterns, $selectors['textarea']);
    }
}
