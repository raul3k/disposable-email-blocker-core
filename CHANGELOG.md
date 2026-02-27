# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.0.1](https://github.com/raul3k/disposable-email-blocker-core/compare/v2.0.0...v2.0.1) (2026-02-27)


### Bug Fixes

* handle edge cases in cache adapters, file reader and decorator chain ([#11](https://github.com/raul3k/disposable-email-blocker-core/issues/11)) ([48b14e6](https://github.com/raul3k/disposable-email-blocker-core/commit/48b14e676053d8061ec0092f54fe69cec233a65f))

## [2.0.0](https://github.com/raul3k/disposable-email-blocker-core/compare/v1.0.0...v2.0.0) (2026-02-26)


### ⚠ BREAKING CHANGES

* namespace changed from Raul3k\BlockDisposable\Core to Raul3k\DisposableBlocker\Core

### Code Refactoring

* unify namespace to DisposableBlocker and fix pre-release issues ([#9](https://github.com/raul3k/disposable-email-blocker-core/issues/9)) ([b0fcadc](https://github.com/raul3k/disposable-email-blocker-core/commit/b0fcadcf70087146d945a2aa93e94f2e4dc55d16))

## 1.0.0 (2026-02-26)


### Features

* add configuration file support for custom sources ([d192043](https://github.com/raul3k/disposable-email-blocker-core/commit/d1920439c4b8fed97e652499a38e901bcecad6ec))
* add disposable email detection library ([58f579d](https://github.com/raul3k/disposable-email-blocker-core/commit/58f579dadf1e66f2529836aac840f94d63764bf4))
* add domain update script and expand bundled list to 159k domains ([97da955](https://github.com/raul3k/disposable-email-blocker-core/commit/97da9558393370d4de53ac1711885b4d78bad7a4))
* add DomainInfo class for tldts-like domain parsing ([d913a5f](https://github.com/raul3k/disposable-email-blocker-core/commit/d913a5f2995f87d3bc772e4db4bdbf4c0c870b33))
* add fluent builder for DisposableEmailChecker ([5eefc5a](https://github.com/raul3k/disposable-email-blocker-core/commit/5eefc5aa6538ddc7f96018ebbbc7796b24395b73))


### Bug Fixes

* resolve critical issues before public release ([#6](https://github.com/raul3k/disposable-email-blocker-core/issues/6)) ([5c05b4a](https://github.com/raul3k/disposable-email-blocker-core/commit/5c05b4a9e198def5eae1082b54a6d3f60cf1ecd4))

## [Unreleased]
