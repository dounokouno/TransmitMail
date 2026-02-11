<?php
/**
 * Part of TransmitMail
 *
 * @package    TransmitMail
 * @subpackage PHPUnit with Symfony panther
 * @license    MIT License
 * @copyright  TransmitMail development team
 * @link       https://github.com/dounokouno/TransmitMail
 */

namespace TransmitMail\Tests;

use Facebook\WebDriver\WebDriverBy;
use Symfony\Component\Panther\PantherTestCase;

abstract class TransmitMailPantherTestCase extends PantherTestCase
{
    protected $client;
    protected $crawler;
    protected $tm;
    protected $topPageTitle = 'TransmitMail サンプル';
    protected $confirmPageTitle = '入力内容の確認 | TransmitMail サンプル';
    protected $errorPageTitle = 'エラー | TransmitMail サンプル';
    protected $globalErrorMessage = '入力内容に誤りがあります';
    protected $testimage = __DIR__ . '/testimage01.png';
    protected $configFile = __DIR__ . '/../../config/config.test.yml';
    protected $tmpDir = __DIR__ . '/../../tmp/tests';
    protected $logDir = __DIR__ . '/../../log/tests';
    protected $routerFile = 'index.test.php';
    protected $inputPatterns = [];
    protected $lenFieldInputPatterns = [];
    protected $urlInputPatterns = [];
    protected $numRangeInputPatterns = [];
    protected $numRangeNotNumberInputPatterns = [];
    protected $templateSyntaxInputPatterns = [];

    protected function setUp(): void
    {
        parent::setUp();

        // ログと一時ディレクトリの作成
        if (!file_exists($this->tmpDir)) {
            mkdir($this->tmpDir, 0777, true);
        }
        if (!file_exists($this->logDir)) {
            mkdir($this->logDir, 0777, true);
        }

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

        // テンプレート構文の入力パターン
        $this->templateSyntaxInputPatterns = [
            '{include:header.html}',
            '{include:header.html? }',
            '{include:http://www.example.com/}',
            '{include:http://www.example.com/? }',
            '{include:ftp://username:password@www.example.com/public_html/index.html}',
            '{include:ftp://username:password@www.example.com/public_html/index.html? }',
            'dummy text {include:header.html}',
            'dummy text {include:header.html? }',
            'dummy text {http://www.example.com/}',
            'dummy text {http://www.example.com/? }',
            'dummy text {ftp://username:password@www.example.com/public_html/index.html}',
            'dummy text {ftp://username:password@www.example.com/public_html/index.html? }',
            'dummy text01 {include:header.html} dummy text02',
            'dummy text01 {include:header.html? } dummy text02',
            'dummy text01 {include:http://www.example.com/} dummy text02',
            'dummy text01 {include:http://www.example.com/? } dummy text02',
            'dummy text01 {include:ftp://username:password@www.example.com/public_html/index.html} dummy text02',
            'dummy text01 {include:ftp://username:password@www.example.com/public_html/index.html? } dummy text02',
            '{$variable}',
            '{$variable }',
            'dummy text {$variable}',
            'dummy text {$variable }',
            'dummy text 01 {$variable} dummy text02',
            'dummy text 01 {$variable } dummy text02'
        ];

        $clientOptions = [
            'webServerDir' => __DIR__ . '/../../',
            'router' => $this->routerFile,
            'chrome_arguments' => [
                '--headless',
                '--no-sandbox',
                '--disable-gpu',
                '--disable-dev-shm-usage',
                '--window-size=1280,720',
                '--disable-extensions',
                '--disable-background-networking',
                '--disable-sync',
                '--metrics-recording-only',
                '--disable-default-apps',
                '--mute-audio',
                '--no-first-run',
                '--remote-debugging-port=0'
            ]
        ];

        $managerOptions = [
            'chromedriver_arguments' => [
                '--log-path=' . $this->logDir . '/chromedriver.log',
                '--verbose',
            ],
        ];

        $this->client = static::createPantherClient($clientOptions, [], $managerOptions);

        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

        $this->tm = new \TransmitMail();
        $this->tm->init($this->configFile);

        $this->crawler = $this->client->request('GET', '/');
    }

    protected function tearDown(): void
    {
        $failed = false;
        if (method_exists($this, 'status')) {
            // PHPUnit 10
            $status = $this->status();
            $failed = $status->isFailure() || $status->isError();
        } elseif (method_exists($this, 'hasFailed')) {
            // PHPUnit 9
            $failed = $this->hasFailed();
        }

        if ($failed) {
            // Pantherクライアントが初期化されているか確認
            if ($this->client !== null) {
                try {
                    $screenshotDir = $this->tmpDir . '/screenshot';
                    // スクリーンショット保存ディレクトリが存在しない場合は作成
                    if (!is_dir($screenshotDir)) {
                        // 0777パーミッションで再帰的にディレクトリを作成
                        mkdir($screenshotDir, 0777, true);
                    }

                    $className = (new \ReflectionClass($this))->getShortName();
                    // PHPUnit 8.5では $this->getName(false) でデータセット名を除いたメソッド名を取得できる
                    $methodName = $this->getName(false);

                    $filename = sprintf(
                        '%s/%s-%s-%s-%s.png',
                        rtrim($screenshotDir, '/'),
                        // $screenshotDir,
                        $className,
                        $methodName,
                        date('YmdHis'),
                        uniqid()
                    );

                    $this->client->takeScreenshot($filename);
                } catch (\Exception $e) {
                    error_log("Failed to take screenshot: " . $e->getMessage());
                }
            } else {
                error_log("Panther client is null, cannot take screenshot for test: " . $this->toString());
            }
        }

        parent::tearDown();
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        // tmpディレクトリ内のファイルを削除
        // TODO: test の場合は tmp ディレクトリを別ディレクトリにしたい
        $tmpDir = __DIR__ . '/../../tmp/tests';
        if (is_dir($tmpDir)) {
            $files = glob($tmpDir . '/file_*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
        }
    }

    protected function filter($selector): ?object
    {
        return $this->crawler->filter($selector);
    }

    protected function findElement($selector): ?object
    {
        return $this->client->findElement(WebDriverBy::cssSelector($selector));
    }

    protected function findElementAndGetValue($selector): ?string
    {
        return $this->findElement($selector)->getAttribute('value');
    }

    protected function findElementAndSetValue($selector, $value): void
    {
        $this->findElement($selector)->sendKeys($value);
    }

    protected function findElementAndClear($selector): void
    {
        $this->findElement($selector)->clear();
    }

    protected function findElementAndGetAttr($selector, $attr): ?string
    {
        return $this->findElement($selector)->getAttribute($attr);
    }

    protected function findElementAndGetText($selector): ?string
    {
        return $this->findElement($selector)->getText();
    }

    /**
     * 入力必須のフィールドにテキストを入力する
     */
    protected function inputRequiredField(): void
    {
        $selector = 'input[type="text"][name="入力必須"]';
        $this->findElementAndClear($selector);
        $this->findElementAndSetValue($selector, '入力必須項目の入力テスト');

        // ファイルの入力必須
        $this->findElementAndSetValue('input[type="file"][name="ファイルの入力必須"]', $this->testimage);
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
    protected function inputTest($inputPatterns, $validValues, $selector, $errorMessage, $convertMode = null): void
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
    protected function inputErrorTest($values, $selector, $errorMessage, $convertMode = null): void
    {
        $convertedValues = $this->convert($values, $convertMode);

        for ($i = 0, $size = count($values); $i < $size; ++$i) {
            $this->findElementAndClear($selector);
            $this->findElementAndSetValue($selector, $values[$i]);
            $this->inputRequiredField();
            $this->submitInputForm();
            $this->assertStringContainsString($this->globalErrorMessage, $this->findElementAndGetText('#content'));
            $this->assertEquals($errorMessage, $this->findElementAndGetText('#content ul li'));
            $this->assertEquals($errorMessage, $this->findElementAndGetText('#content table tr td div.error'));
            $this->assertEquals($convertedValues[$i], $this->findElementAndGetValue($selector));
        }
    }

    /**
     * 入力エラーにならない場合のテスト
     *
     * @param array[]  $values      入力パターン
     * @param array[]  $selector    テストする入力フィールドのCSSセレクタ
     * @param string[] $convertMode 入力値の変換をするか、する場合はどのように変換をするか
     */
    protected function inputSuccessTest($values, $selector, $convertMode = null): void
    {
        $convertedValues = $this->convert($values, $convertMode);
        $hiddenFieldSelector = '';
        if (strpos($selector, 'textarea') !== false) {
            $hiddenFieldSelector = str_replace('textarea', 'input[type="hidden"]', $selector);
        } else {
            $hiddenFieldSelector = str_replace('[type="text"]', '[type="hidden"]', $selector);
        }

        for ($i = 0, $size = count($values); $i < $size; ++$i) {
            $this->findElementAndClear($selector);
            $this->findElementAndSetValue($selector, $values[$i]);
            $this->inputRequiredField();
            $this->submitInputForm();
            $this->assertEquals($this->confirmPageTitle, $this->client->getTitle());
            $this->assertStringContainsString($convertedValues[$i], $this->filter('#content table')->text());
            $this->assertEquals($convertedValues[$i], $this->findElementAndGetValue($hiddenFieldSelector));

            // 入力画面に戻る
            $this->returnInputPage();
            $this->assertEquals($convertedValues[$i], $this->findElementAndGetValue($selector));
        }
    }

    /**
     * 入力フォームを submit する
     */
    protected function submitInputForm(): void
    {
        // 送信する前にウィンドウが存在することを確認
        if (!$this->isWindowPresent()) {
            $this->fail("フォーム送信前にブラウザウィンドウが予期せず閉じました。");
        }

        $this->crawler = $this->client->getCrawler();
        $form = $this->filter('form')->form();
        $this->client->submit($form);

        // 送信直後にウィンドウが存在することを確認
        if (!$this->isWindowPresent()) {
            $this->fail("フォーム送信直後にブラウザウィンドウが予期せず閉じました。");
        }

        $this->crawler = $this->client->getCrawler();
    }

    /**
     * ブラウザウィンドウがまだ存在し、応答可能かを確認
     */
    protected function isWindowPresent(): bool
    {
        try {
            // ウィンドウハンドルを取得しようとすることで、ウィンドウの存在を確認
            $this->client->getWindowHandles();
            return true;
        } catch (\Facebook\WebDriver\Exception\NoSuchWindowException $e) {
            return false;
        } catch (\Exception $e) {
            error_log("isWindowPresent の確認中に予期せぬ例外が発生しました: " . get_class($e) . " - " . $e->getMessage());
            return false;
        }
    }

    /**
     * 入力確認画面から入力画面に戻る
     */
    protected function returnInputPage(): void
    {
        $form = $this->filter('form:has(input[type="hidden"][name="page_name"][value="input"])')->first()->form();
        $this->client->submit($form);
        $this->crawler = $this->client->getCrawler();
    }

    /**
     * 文字への変換
     *
     * @param array[]  $values      入力パターン
     * @param string[] $convertMode 入力値の変換をするか、する場合はどのように変換をするか
     */
    private function convert($values, $convertMode = null): array
    {
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

    protected static function createPantherClient(array $options = [], array $kernelOptions = [], array $managerOptions = []): \Symfony\Component\Panther\Client
    {
        if (null !== self::$pantherClient) {
            return self::$pantherClient;
        }

        self::startWebServer($options);

        $arguments = $options['chrome_arguments'] ?? null;

        self::$pantherClients[0] = self::$pantherClient = \Symfony\Component\Panther\Client::createChromeClient(
            null,
            $arguments,
            $managerOptions,
            self::$baseUri
        );

        \Symfony\Component\Panther\ServerExtension::registerClient(self::$pantherClient);

        return self::$pantherClient;
    }
}
