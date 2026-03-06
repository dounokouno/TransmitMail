# TransmitMail 使い方ガイド

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

## 2. 実践編：基本設定

ここでは設定ファイルは YAML ファイルを利用します。それ以外のファイル形式を利用する場合は読み替えてください。

### config フォルダの設定

1. `config.yml.sample` を `config.yml` にリネームします。
2. `config.yml` を編集し、受信メールアドレスや件名を案件に合わせて変更します。
3. `mail_body.txt`（管理者宛）と `mail_auto_reply_body.txt`（自動返信）の本文を適宜修正します。

### ファイル添付の有効化（必要な場合）

デフォルトではファイル添付が無効になっています。利用する場合は `config.yml` に `file: true` を追加してください。

## 3. テンプレートの編集

### 入力画面 (input.html)

フォームの項目を案件に合わせて追加、修正します。送信後の画面でフォーム部分を表示したい場合は `form` タグに `action="./#id名"` を追加してください。

### 確認画面 (confirm.html, confirm_kobetsu.html)

- **ループ出力 (confirm.html)**: 項目を自動で順番に出力します。
- **個別出力 (confirm_kobetsu.html)**: 特定の項目を好きな順番で表示するようカスタマイズ可能です。利用する場合は `confirm_kobetsu.html` を `confirm.html` に置き換えて使用してください。

### 完了画面 (finish.html)

フォームのページに戻るリンクなどは `./` や `index.php` を指定します。

## 4. バリデーションの設定

TransmitMail では `input.html` 内に `hidden` フィールドを追加することでバリデーションを設定できます。 TransmitMail に実装されているすべてのバリデーションの設定は `input.html` に書かれているので、そちらを参考にしてください。

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

## 5. その他・カスタマイズ

### ヘッダー・フッターの共通化

`{include:header.html}` のように記述することで、別ファイルをインクルードできます。

---

## 参考

- [TransmitMail Wiki](https://github.com/dounokouno/TransmitMail/wiki)
- [TransmitMail 2 のカスタマイズ例のリンク集 #TransmitMail - Qiita](https://qiita.com/dounokouno/items/c76d6b7053200c476d6d)
- [TransmitMail 2 のカスタマイズ例のリンク集 #TransmitMail - Qiita](https://qiita.com/dounokouno/items/c76d6b7053200c476d6d)
