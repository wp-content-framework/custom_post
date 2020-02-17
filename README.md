# WP Content Framework (Custom post module)

[![CI Status](https://github.com/wp-content-framework/custom_post/workflows/CI/badge.svg)](https://github.com/wp-content-framework/custom_post/actions)
[![License: GPL v2+](https://img.shields.io/badge/License-GPL%20v2%2B-blue.svg)](http://www.gnu.org/licenses/gpl-2.0.html)
[![PHP: >=5.6](https://img.shields.io/badge/PHP-%3E%3D5.6-orange.svg)](http://php.net/)
[![WordPress: >=4.6](https://img.shields.io/badge/WordPress-%3E%3D4.6-brightgreen.svg)](https://wordpress.org/)

[WP Content Framework](https://github.com/wp-content-framework/core) のモジュールです。

<!-- START doctoc generated TOC please keep comment here to allow auto update -->
<!-- DON'T EDIT THIS SECTION, INSTEAD RE-RUN doctoc TO UPDATE -->
<details>
<summary>Details</summary>

- [要件](#%E8%A6%81%E4%BB%B6)
- [インストール](#%E3%82%A4%E3%83%B3%E3%82%B9%E3%83%88%E3%83%BC%E3%83%AB)
  - [依存モジュール](#%E4%BE%9D%E5%AD%98%E3%83%A2%E3%82%B8%E3%83%A5%E3%83%BC%E3%83%AB)
  - [基本設定](#%E5%9F%BA%E6%9C%AC%E8%A8%AD%E5%AE%9A)
  - [カスタム投稿タイプの追加](#%E3%82%AB%E3%82%B9%E3%82%BF%E3%83%A0%E6%8A%95%E7%A8%BF%E3%82%BF%E3%82%A4%E3%83%97%E3%81%AE%E8%BF%BD%E5%8A%A0)
  - [エクスポートとインポート](#%E3%82%A8%E3%82%AF%E3%82%B9%E3%83%9D%E3%83%BC%E3%83%88%E3%81%A8%E3%82%A4%E3%83%B3%E3%83%9D%E3%83%BC%E3%83%88)
- [Author](#author)

</details>
<!-- END doctoc generated TOC please keep comment here to allow auto update -->

# 要件
- PHP 5.6 以上
- WordPress 4.6 以上

# インストール

``` composer require wp-content-framework/custom_post ```

## 依存モジュール
* [db](https://github.com/wp-content-framework/db)
* [session](https://github.com/wp-content-framework/session)
* [admin](https://github.com/wp-content-framework/admin)
* [api](https://github.com/wp-content-framework/api)

## 基本設定
- configs/config.php

|設定値|説明|
|---|---|
|prior_default|nullableかつdefaultが設定されているカラムの値がない場合にdefaultを優先するかどうか \[default  = false]|

## カスタム投稿タイプの追加
今後追加予定

## エクスポートとインポート
今後追加予定

# Author
- [GitHub (Technote)](https://github.com/technote-space)
- [Blog](https://technote.space)
