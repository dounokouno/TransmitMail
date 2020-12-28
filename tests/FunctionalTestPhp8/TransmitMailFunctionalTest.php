<?php
/**
 * Part of TransmitMail
 *
 * @package    TransmitMail
 * @subpackage PHPUnit with Selenium 2
 * @license    MIT License
 * @copyright  TransmitMail development team
 * @link       https://github.com/dounokouno/TransmitMail
 */

// use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\AssertionFailedError;
// use BadMethodCallException;
use PHPUnit\Extensions\Selenium2TestCase;
// use PHPUnit\Extensions\Selenium2TestCase\Keys;
// use PHPUnit\Extensions\Selenium2TestCase\SessionCommand\Click;
// use PHPUnit\Extensions\Selenium2TestCase\WebDriverException;
// use PHPUnit\Extensions\Selenium2TestCase\Window;
use PHPUnit\Extensions\Selenium2TestCase\ScreenshotListener;

abstract class TransmitMailFunctionalTest extends Selenium2TestCase
{
    public $tm;
    public $topPageTitle = 'TransmitMail サンプル';
    public $confirmPageTitle = '入力内容の確認 | TransmitMail サンプル';
    public $globalErrorMessage = '入力内容に誤りがあります';
    public $testimage = 'tests/FunctionalTest/testimage01.jpg';
    public $inputPatterns = array();
    public $lenFieldInputPatterns = array();

    /**
     * コンストラクタ
     */
    public function __construct($name = NULL, array $data = array(), $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->tm = new TransmitMail();
        $this->tm->init();

        // 入力パターン
        $this->inputPatterns['kanji'] = '漢字';
        $this->inputPatterns['hiragana'] = 'ひらがな';
        $this->inputPatterns['katakana'] = 'カタカナ';
        $this->inputPatterns['hankakuKatakana'] = 'ﾊﾝｶｸｶﾀｶﾅ';
        $this->inputPatterns['kanjiNum'] = $this->inputPatterns['kanji'] . implode('', range(0, 9));
        $this->inputPatterns['kanjiNumHyphen'] = $this->inputPatterns['kanjiNum'] . '-';
        $this->inputPatterns['kanjiEiji'] = $this->inputPatterns['kanji'] . implode('', range('A', 'Z'));
        $this->inputPatterns['kanjiEijiHyphen'] = $this->inputPatterns['kanjiEiji'] . '-';
        $this->inputPatterns['num'] = implode('', range(0, 9));
        $this->inputPatterns['zenkakuNum'] = mb_convert_kana($this->inputPatterns['num'], 'N');
        $this->inputPatterns['eiji'] = implode('', range('A', 'Z')) . implode('', range('a', 'z'));
        $this->inputPatterns['zenkakuEiji'] = mb_convert_kana($this->inputPatterns['eiji'], 'R');
        $this->inputPatterns['numHyphen'] = $this->inputPatterns['num'] . '-';
        $this->inputPatterns['zenkakuNumHyphen'] = mb_convert_kana($this->inputPatterns['numHyphen'], 'A');
        $this->inputPatterns['eijiHyphen'] = $this->inputPatterns['eiji'] . '-';
        $this->inputPatterns['zenkakuEijiHyphen'] = mb_convert_kana($this->inputPatterns['eijiHyphen'], 'A');
        $this->inputPatterns['eisu'] = $this->inputPatterns['num'] . $this->inputPatterns['eiji'];
        $this->inputPatterns['zenkakuEisu'] = mb_convert_kana($this->inputPatterns['eisu'], 'A');
        $this->inputPatterns['eisuHyphen'] = $this->inputPatterns['eisu'] . '-';
        $this->inputPatterns['zenkakuEisuHyphen'] = mb_convert_kana($this->inputPatterns['eisuHyphen'], 'A');
        $this->inputPatterns['eisuKigo'] = implode('', range('!', '~'));
        $this->inputPatterns['zenkakuEisuKigo'] = mb_convert_kana($this->inputPatterns['eisuKigo'], 'A');

        // 文字数チェックの入力パターン
        $this->lenFieldInputPatterns['eiji1'] = 'a';
        $this->lenFieldInputPatterns['eiji2'] = 'ab';
        $this->lenFieldInputPatterns['eiji3'] = implode('', range('a', 'c'));
        $this->lenFieldInputPatterns['eiji4'] = implode('', range('a', 'd'));
        $this->lenFieldInputPatterns['eiji5'] = implode('', range('a', 'e'));
        $this->lenFieldInputPatterns['eiji6'] = implode('', range('a', 'f'));
        $this->lenFieldInputPatterns['eiji7'] = implode('', range('a', 'g'));
        $this->lenFieldInputPatterns['eiji8'] = implode('', range('a', 'h'));
        $this->lenFieldInputPatterns['eiji9'] = implode('', range('a', 'i'));
        $this->lenFieldInputPatterns['hiragana1'] = 'あ';
        $this->lenFieldInputPatterns['hiragana2'] = 'あい';
        $this->lenFieldInputPatterns['hiragana3'] = 'あいう';
        $this->lenFieldInputPatterns['hiragana4'] = 'あいうえ';
        $this->lenFieldInputPatterns['hiragana5'] = 'あいうえお';
        $this->lenFieldInputPatterns['hiragana6'] = 'あいうえおか';
        $this->lenFieldInputPatterns['hiragana7'] = 'あいうえおかき';
        $this->lenFieldInputPatterns['hiragana8'] = 'あいうえおかきく';
        $this->lenFieldInputPatterns['hiragana9'] = 'あいうえおかきくけ';

        // URLの入力パターン
        $this->urlInputPatterns['exampleCom'] = 'http://example.com';
        $this->urlInputPatterns['exampleComLastCharacterSlash'] = 'http://example.com/';
        $this->urlInputPatterns['exampleComSsl'] = 'https://example.com';
        $this->urlInputPatterns['exampleComWwwSubdomain'] = 'http://www.example.com';
        $this->urlInputPatterns['exampleComPage'] = 'http://example.com/page';
        $this->urlInputPatterns['exampleComParam'] = 'http://example.com/param1=a&param2=_b';
        $this->urlInputPatterns['exampleComHash'] = 'http://example.com/#hash';
        $this->urlInputPatterns['exampleMuseum'] = 'http://example.museum';
        $this->urlInputPatterns['japaneseDomain'] = 'http://ドメイン名例.jp';
        $this->urlInputPatterns['japaneseDomainSubdomain'] = 'http://www.ドメイン名例.jp';
        $this->urlInputPatterns['japaneseDomainJapaneseSubdomain'] = 'http://サブドメイン.ドメイン名例.jp';
        $this->urlInputPatterns['punycodeJapaneseDomain'] = 'http://xn--eckwd4c7cu47r2wf.jp';
        $this->urlInputPatterns['exampleComHyphenSubdomain'] = 'http://sub-domain.example.com';
        $this->urlInputPatterns['exampleComUnserscoreSubdomain'] = 'http://sub_domain.example.com';
        $this->urlInputPatterns['exampleA'] = 'http://example.a';
        $this->urlInputPatterns['example'] = 'http://example';
        $this->urlInputPatterns['exampleOneSlash'] = 'http:/example';
        $this->urlInputPatterns['exampleNoneSlash'] = 'http:example';
        $this->urlInputPatterns['exampleNoneColon'] = 'http//example';
        $this->urlInputPatterns['exampleNoneHttp'] = '://example';
        $this->urlInputPatterns['exampleNoneHttpAndColon'] = '//example';
        $this->urlInputPatterns['exampleComNoneHttpAndColon'] = '//example.com';
        $this->urlInputPatterns['exampleNoneHttpColonAndSlash'] = 'example';
        $this->urlInputPatterns['exampleComNoneHttpColonAndSlash'] = 'example.com';
        $this->urlInputPatterns['exampleSpace'] = 'exam ple';
        $this->urlInputPatterns['exampleComSpace'] = 'exam ple.com';
        $this->urlInputPatterns['exampleZenkakuSpace'] = 'exam　ple';
        $this->urlInputPatterns['exampleComZenkakuSpace'] = 'exam　ple.com';

        // 数字の範囲の入力パターン
        $this->numRangeInputPatterns['0'] = '0';
        $this->numRangeInputPatterns['1'] = '1';
        $this->numRangeInputPatterns['2'] = '2';
        $this->numRangeInputPatterns['3'] = '3';
        $this->numRangeInputPatterns['4'] = '4';
        $this->numRangeInputPatterns['5'] = '5';
        $this->numRangeInputPatterns['6'] = '6';
        $this->numRangeInputPatterns['7'] = '7';
        $this->numRangeInputPatterns['8'] = '8';
        $this->numRangeInputPatterns['9'] = '9';
        $this->numRangeInputPatterns['10'] = '10';
        $this->numRangeInputPatterns['11'] = '11';
        $this->numRangeInputPatterns['12'] = '12';
        $this->numRangeInputPatterns['13'] = '13';
        $this->numRangeInputPatterns['32768'] = '32768';
        $this->numRangeInputPatterns['65536'] = '65536';
        $this->numRangeInputPatterns['2147483648'] = '2147483648';
        $this->numRangeInputPatterns['4294967296'] = '4294967296';

        // 数字の範囲の入力パターン（数字以外）
        $this->numRangeNotNumberInputPatterns['-1'] = '-1';
        $this->numRangeNotNumberInputPatterns['0.1'] = '0.1';
        $this->numRangeNotNumberInputPatterns['-0.1'] = '0.1';
        $this->numRangeNotNumberInputPatterns['a'] = 'a';
        $this->numRangeNotNumberInputPatterns['あ'] = 'あ';
    }

    /**
     * セットアップ
     */
    protected function setUp(): void
    {
        $this->setBrowser('phantomjs');
        // $this->setBrowser('chrome');
        // $this->setDesiredCapabilities(
        //     [
        //         'chromeOptions' => [
        //             'args' => [
        //                 'headless', 'disable-gpu'
        //             ],
        //             'w3c' => false
        //         ]
        //     ]
        // );
        // $this->setDesiredCapabilities(['chromeOptions' => ['w3c' => false]]);
        $this->setBrowserUrl('http://localhost:8000/');
    }

    /**
     * ティアダウン
     */
    protected function tearDown(): void
    {
        $testimage = basename($this->testimage);
        $tmp_files = scandir($this->tm->config['tmp_dir']);

        foreach ($tmp_files as $tmp_file) {
            if (strpos($tmp_file, $testimage)) {
                unlink($this->tm->config['tmp_dir'] . $tmp_file);
            }
        }
    }

    /**
     * テスト失敗時にスクリーンショット画像を保存する
     */
    public function onNotSuccessfulTest($e): void
    {
        if ($e instanceof AssertionFailedError) {
            $listener = new ScreenshotListener('tmp/screenshot');
            $listener->addFailure($this, $e, null);
        }

        parent::onNotSuccessfulTest($e);
    }

    /**
     * 入力必須のフィールドにテキストを入力する
     */
    public function inputRequiredField()
    {
        $element = $this->byCssSelector('input[type="text"][name="入力必須"]');
        $element->clear();
        $element->value('入力必須項目の入力テスト');

        // ファイルの入力必須
        $element = $this->byCssSelector('input[type="file"][name="ファイルの入力必須"]');
        $element->value($this->file($this->testimage));
    }

    /**
     * 入力のテスト
     *
     * @param array[]  $inputPatterns 入力パターン
     * @param array[]  $validValues   有効な入力パターン
     * @param string[] $selector      テストする入力フィールドのCSSセレクタ
     * @param string[] $errorMessage  エラーメッセージ
     * @param string[] $convertMode   入力値の変換をするか、する場合はどのように変換をするか
     */
    public function inputTest($inputPatterns, $validValues, $selector, $errorMessage, $convertMode = null)
    {
        $invalidValues = array_values(array_diff($inputPatterns, $validValues));
        $this->inputErrorTest($invalidValues, $selector, $errorMessage, $convertMode);
        $this->inputSuccessTest($validValues, $selector, $convertMode);
    }

    /**
     * 入力エラーの場合のテスト
     *
     * @param array[]  $values       入力パターン
     * @param array[]  $selector     テストする入力フィールドのCSSセレクタ
     * @param string[] $errorMessage エラーメッセージ
     * @param string[] $convertMode  入力値の変換をするか、する場合はどのように変換をするか
     */
    public function inputErrorTest($values, $selector, $errorMessage, $convertMode = null)
    {
        $convertedValues = $this->convert($values, $convertMode);

        for ($i = 0, $size = count($values); $i < $size; ++$i) {
            $element = $this->byCssSelector($selector);
            $element->clear();
            $element->value($values[$i]);
            $this->inputRequiredField();
            $this->submitInputForm();
            $this->assertContains($this->globalErrorMessage, $this->byCssSelector('#content')->text());
            $this->assertEquals($errorMessage, $this->byCssSelector('#content ul li')->text());
            $this->assertEquals($errorMessage, $this->byCssSelector('#content table tr td div.error')->text());
            $this->assertEquals($convertedValues[$i], $this->byCssSelector($selector)->value());
        }
    }

    /**
     * 入力エラーにならない場合のテスト
     *
     * @param array[]  $values      入力パターン
     * @param array[]  $selector    テストする入力フィールドのCSSセレクタ
     * @param string[] $convertMode 入力値の変換をするか、する場合はどのように変換をするか
     */
    public function inputSuccessTest($values, $selector, $convertMode = null)
    {
        $convertedValues = $this->convert($values, $convertMode);
        $hiddenFieldSelector = str_replace('[type="text"]', '[type="hidden"]', $selector);

        for ($i = 0, $size = count($values); $i < $size; ++$i) {
            $this->url('');
            $element = $this->byCssSelector($selector);
            $element->value($values[$i]);
            $this->inputRequiredField();
            $this->submitInputForm();
            $this->assertEquals($this->confirmPageTitle, $this->title());
            $this->assertContains($convertedValues[$i], $this->byCssSelector('#content table')->text());
            $this->assertEquals($convertedValues[$i], $this->byCssSelector($hiddenFieldSelector)->value());

            // 入力画面に戻る
            $this->returnInputPage();
            $this->assertEquals($convertedValues[$i], $this->byCssSelector($selector)->value());
        }
    }

    /**
     * 入力フォームを submit する
     */
    public function submitInputForm()
    {
        $this->byCssSelector('form')->submit();
    }

    /**
     * 入力確認画面から入力画面に戻る
     */
    public function returnInputPage()
    {
        $this->byCssSelector('input[type="hidden"][name="page_name"][value="input"]')->submit();
    }

    /**
     * 文字への変換
     *
     * @param array[]  $values      入力パターン
     * @param string[] $convertMode 入力値の変換をするか、する場合はどのように変換をするか
     */
    private function convert($values, $convertMode = null) {
        if (is_null($convertMode)) {
            return $values;
        }

        switch ($convertMode) {
            case 'hankaku':
            case 'hankaku_eisu':
            case 'num_hyphen':
                $subscript = 'a';
                break;

            case 'hankaku_eiji':
                $subscript = 'r';
                break;
 
            case 'num':
                $subscript = 'n';
                break;

            case 'hiragana':
                $subscript = 'cH';
                break;

            case 'zenkaku_katakana':
                $subscript = 'CK';
                break;
        }

        foreach ($values as $value) {
            $results[] = mb_convert_kana($value, $subscript);
        }

        return $results;
    }
}
