<?php
/**
 * Basic test
 *
 * @package    TransmitMail
 * @subpackage PHPUnit with Selenium 2
 * @license    MIT License
 * @copyright  TransmitMail development team
 * @link       https://github.com/dounokouno/TransmitMail
 */

class BasicTest extends TransmitMailFunctionalTest
{
    /**
     * URLを表示しタイトルをテスト
     */
    public function testTitle()
    {
        $this->url('');
        $this->assertEquals($this->topPageTitle, $this->title());
    }

    /**
     * サンプル用フィールドの表示テスト
     */
    public function testSampleFields()
    {
        $this->url('');

        $this->assertEquals('', $this->byCssSelector('input[type="text"][name="シングルラインインプット"]')->value());
        $this->assertEquals('', $this->byCssSelector('textarea[name="マルチラインインプット"]')->value());
        $this->assertEquals('項目1', $this->byCssSelector('input[type="radio"][name="ラジオボタン"]:first-of-type')->value());
        $this->assertEquals('項目1', $this->byCssSelector('input[type="checkbox"][name="チェックボックス[]"]')->value());
        $this->assertEquals('項目1', $this->byCssSelector('select[name="セレクトメニュー"]')->value());
        $this->assertEquals('', $this->byCssSelector('select[name="マルチプルセレクトメニュー[]"]')->value());
        $this->assertEquals('', $this->byCssSelector('input[type="file"][name="ファイル1"]')->value());
        $this->assertEquals('', $this->byCssSelector('input[type="file"][name="ファイル2"]')->value());

        $this->assertEquals('', $this->byCssSelector('input[type="text"][name="入力必須"]')->value());
        $this->assertEquals('入力必須', $this->byCssSelector('input[type="hidden"][name="required[]"]')->value());

        $this->assertEquals('', $this->byCssSelector('input[type="text"][name="メールアドレス"]')->value());
        $this->assertEquals('メールアドレス', $this->byCssSelector('input[type="hidden"][name="email[]"]')->value());

        $this->assertEquals('', $this->byCssSelector('input[type="text"][name="半角文字"]')->value());
        $this->assertEquals('半角文字', $this->byCssSelector('input[type="hidden"][name="hankaku[]"]')->value());

        $this->assertEquals('', $this->byCssSelector('input[type="text"][name="半角英数字"]')->value());
        $this->assertEquals('半角英数字', $this->byCssSelector('input[type="hidden"][name="hankaku_eisu[]"]')->value());

        $this->assertEquals('', $this->byCssSelector('input[type="text"][name="半角英字"]')->value());
        $this->assertEquals('半角英字', $this->byCssSelector('input[type="hidden"][name="hankaku_eiji[]"]')->value());

        $this->assertEquals('', $this->byCssSelector('input[type="text"][name="数字"]')->value());
        $this->assertEquals('数字', $this->byCssSelector('input[type="hidden"][name="num[]"]')->value());

        $this->assertEquals('', $this->byCssSelector('input[type="text"][name="数字＋ハイフン"]')->value());
        $this->assertEquals('数字＋ハイフン', $this->byCssSelector('input[type="hidden"][name="num_hyphen[]"]')->value());

        $this->assertEquals('', $this->byCssSelector('input[type="text"][name="ひらがな"]')->value());
        $this->assertEquals('ひらがな', $this->byCssSelector('input[type="hidden"][name="hiragana[]"]')->value());

        $this->assertEquals('', $this->byCssSelector('input[type="text"][name="全角カタカナ"]')->value());
        $this->assertEquals('全角カタカナ', $this->byCssSelector('input[type="hidden"][name="zenkaku_katakana[]"]')->value());

        $this->assertEquals('', $this->byCssSelector('input[type="text"][name="全角文字を含むか"]')->value());
        $this->assertEquals('全角文字を含むか', $this->byCssSelector('input[type="hidden"][name="zenkaku[]"]')->value());

        $this->assertEquals('', $this->byCssSelector('input[type="text"][name="全て全角文字"]')->value());
        $this->assertEquals('全て全角文字', $this->byCssSelector('input[type="hidden"][name="zenkaku_all[]"]')->value());

        $this->assertEquals('', $this->byCssSelector('input[type="text"][name="3文字以上"]')->value());
        $this->assertEquals('3文字以上 3-', $this->byCssSelector('input[type="hidden"][name="len[]"][value="3文字以上 3-"]')->value());

        $this->assertEquals('', $this->byCssSelector('input[type="text"][name="3文字以下"]')->value());
        $this->assertEquals('3文字以下 -3', $this->byCssSelector('input[type="hidden"][name="len[]"][value="3文字以下 -3"]')->value());

        $this->assertEquals('', $this->byCssSelector('input[type="text"][name="3文字固定"]')->value());
        $this->assertEquals('3文字固定 3-3', $this->byCssSelector('input[type="hidden"][name="len[]"][value="3文字固定 3-3"]')->value());

        $this->assertEquals('', $this->byCssSelector('input[type="text"][name="6文字以上8文字以下"]')->value());
        $this->assertEquals('6文字以上8文字以下 6-8', $this->byCssSelector('input[type="hidden"][name="len[]"][value="6文字以上8文字以下 6-8"]')->value());

        $this->assertEquals('', $this->byCssSelector('input[type="text"][name="一致1"]')->value());
        $this->assertEquals('', $this->byCssSelector('input[type="text"][name="一致2"]')->value());
        $this->assertEquals('一致1 一致2', $this->byCssSelector('input[type="hidden"][name="match[]"][value="一致1 一致2"]')->value());

        $this->assertEquals('', $this->byCssSelector('input[type="text"][name="URL"]')->value());
        $this->assertEquals('URL', $this->byCssSelector('input[type="hidden"][name="url[]"][value="URL"]')->value());

        $this->assertEquals('', $this->byCssSelector('input[type="text"][name="3以下の数字"]')->value());
        $this->assertEquals('3以下の数字 -3', $this->byCssSelector('input[type="hidden"][name="num_range[]"][value="3以下の数字 -3"]')->value());

        $this->assertEquals('', $this->byCssSelector('input[type="text"][name="3以上の数字"]')->value());
        $this->assertEquals('3以上の数字 3-', $this->byCssSelector('input[type="hidden"][name="num_range[]"][value="3以上の数字 3-"]')->value());

        $this->assertEquals('', $this->byCssSelector('input[type="text"][name="ちょうど3の数字"]')->value());
        $this->assertEquals('ちょうど3の数字 3-3', $this->byCssSelector('input[type="hidden"][name="num_range[]"][value="ちょうど3の数字 3-3"]')->value());

        $this->assertEquals('', $this->byCssSelector('input[type="text"][name="1〜12の数字"]')->value());
        $this->assertEquals('1〜12の数字 1-12', $this->byCssSelector('input[type="hidden"][name="num_range[]"][value="1〜12の数字 1-12"]')->value());

        $this->assertEquals('', $this->byCssSelector('input[type="file"][name="ファイルの入力必須"]')->value());
        $this->assertEquals('ファイルの入力必須', $this->byCssSelector('input[type="hidden"][name="file_required[]"][value="ファイルの入力必須"]')->value());

        $this->assertEquals('', $this->byCssSelector('input[type="text"][name="郵便番号"]')->value());
        $this->assertEquals('郵便番号', $this->byCssSelector('input[type="hidden"][name="num_hyphen[]"][value="郵便番号"]')->value());
        $this->assertEquals('郵便番号 8', $this->byCssSelector('input[type="hidden"][name="len[]"][value="郵便番号 8"]')->value());

        $this->assertEquals('', $this->byCssSelector('input[type="text"][name="abcdefghijklnmopqrstuvwxyz"]')->value());

        $this->assertEquals('入力内容を確認する', $this->byCssSelector('input[type="submit"]')->value());
    }

    /**
     * x、yを含む項目名のテスト
     */
    public function testContainsXAndYField()
    {
        $this->url('');

        $selector = 'input[type="text"][name="abcdefghijklnmopqrstuvwxyz"]';
        $hiddenFieldSelector = str_replace('[type="text"]', '[type="hidden"]', $selector);
        $targetNameValue = $this->byCssSelector($selector)->attribute('name');
        $value = 'x、yを含む項目名への入力';

        // 入力フィールドの確認
        $this->assertEquals('', $this->byCssSelector($selector)->value());
        $this->assertInternalType('object', $this->byCssSelector($selector));

        // テストの実行
        $element = $this->byCssSelector($selector);
        $element->value($value);
        $this->inputRequiredField();
        $this->submitInputForm();
        $this->assertEquals($this->confirmPageTitle, $this->title());
        $this->assertContains($value, $this->byCssSelector('#content table')->text());
        $this->assertEquals($value, $this->byCssSelector($hiddenFieldSelector)->value());

        // 入力画面に戻る
        $this->returnInputPage();
        $this->assertEquals($value, $this->byCssSelector($selector)->value());
    }
}