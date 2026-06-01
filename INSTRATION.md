# TransmitMail 使い方ガイド（下書き）

このドキュメントは、TransmitMail の導入からカスタマイズ、バリデーション設定までの基本的な使い方をまとめたガイドです。

## 1. 導入編

### ファイルのダウンロード
公式サイト（GitHub）から最新の ZIP ファイルをダウンロードし、解凍します。
[https://github.com/dounokouno/TransmitMail](https://github.com/dounokouno/TransmitMail)

### 主要なファイル・フォルダの概要
- **config/**: メール設定や本文テンプレートを格納するフォルダ。
- **lib/**: TransmitMail のコアプログラムが格納されているフォルダ。
- **log/**: ログ出力用フォルダ。
- **tmp/**: 一時保存用フォルダ。
- **index.php**: フォーム実行用メインファイル。
- **input.html**: 入力画面テンプレート。
- **confirm.html**: 確認画面テンプレート（ループ出力用）。
- **finish.html**: 完了画面テンプレート。
- **error.html**: エラー画面テンプレート。

---

## 2. 実践編：基本設定

### config フォルダの設定
1. `config.yml.sample` を `config.yml` にリネームします。
2. `config.yml` を編集し、受信メールアドレスや件名を案件に合わせて変更します。
   ```yaml
   config:
       email: info@example.com
       subject: ［株式会社テスト］お問い合わせ
       auto_reply_subject: ［株式会社テスト］お問い合わせありがとうございます
       auto_reply_name: 株式会社テスト
   ```
3. `mail_body.txt`（管理者宛）と `mail_auto_reply_body.txt`（自動返信）の本文を適宜修正します。

### ファイル添付の有効化（必要な場合）
デフォルトではファイル添付が無効になっています。利用する場合は `lib/TransmitMail.php` （または config 設定）で設定を変更します。
※ `config.yml` に `'file' => true` を追加するか、`lib/TransmitMail.php` 内のデフォルト値を書き換えます。

---

## 3. テンプレートの編集

### input.html（入力画面）
フォームの項目を案件に合わせて追加・修正します。
送信後にページ上部へ戻るのを防ぐため、`form` タグに `action="./#id名"` を追加するのが推奨されます。

### confirm.html（確認画面）
- **ループ出力**: 項目を自動で順番に出力します。
- **個別出力**: `confirm_kobetsu.html` を参考に、特定の項目を好きな順番で表示するようカスタマイズ可能です。

### finish.html（完了画面）
デザインに合わせて装飾します。トップへ戻るリンクなどは `index.php` を指定します。

---

## 4. バリデーションの設定

TransmitMail では、`input.html` 内に `hidden` フィールドを追加することでバリデーションを設定できます。

### 基本的な記述例
```html
<!-- 必須項目 -->
<input type="text" name="お名前" value="{$お名前}">
<input type="hidden" name="required[]" value="お名前">
{if:$required.お名前}
<div class="error">{$required.お名前}</div>
{/if:$required.お名前}
```

### 主なバリデーション種類
- **required[]**: 必須項目
- **email[]**: メールアドレス形式
- **match[]**: 一致チェック（例：`value="メールアドレス mail2"`）
- **hiragana[]**: ひらがな
- **zenkaku_katakana[]**: 全角カタカナ
- **num[]**: 数字
- **hankaku_eisu[]**: 半角英数字
- **url[]**: URL形式

---

## 5. その他・カスタマイズ

### ヘッダー・フッターの共通化
`{include:header.html}` のように記述することで、別ファイルをインクルードできます。

### 確認画面をスキップする場合
`input.html` の送信ボタン部分を以下のように修正します。
```html
<input type="hidden" name="page_name" value="finish">
<input type="submit" value="送信する">
```

### 同一サイトで複数のフォームを設置する場合
フォルダを分けて設置することで、複数の TransmitMail を共存させることが可能です。

---

## 参考リソース
- [TransmitMail Wiki（公式マニュアル）](https://github.com/dounokouno/TransmitMail/wiki)
- [参照元記事](https://jito-site.com/transmitmail/)
