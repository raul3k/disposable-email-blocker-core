# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## 1.0.0 (2026-01-30)


### Features

* add configuration file support for custom sources ([d192043](https://github.com/raul3k/disposable-email-blocker-core/commit/d1920439c4b8fed97e652499a38e901bcecad6ec))
* add disposable email detection library ([58f579d](https://github.com/raul3k/disposable-email-blocker-core/commit/58f579dadf1e66f2529836aac840f94d63764bf4))
* add domain update script and expand bundled list to 159k domains ([97da955](https://github.com/raul3k/disposable-email-blocker-core/commit/97da9558393370d4de53ac1711885b4d78bad7a4))
* add DomainInfo class for tldts-like domain parsing ([d913a5f](https://github.com/raul3k/disposable-email-blocker-core/commit/d913a5f2995f87d3bc772e4db4bdbf4c0c870b33))
* add fluent builder for DisposableEmailChecker ([5eefc5a](https://github.com/raul3k/disposable-email-blocker-core/commit/5eefc5aa6538ddc7f96018ebbbc7796b24395b73))

## [Unreleased]

### Added
- `PatternChecker` for regex-based detection of suspicious domain patterns
- `WhitelistChecker` decorator to allow specific domains to bypass checks
- `CachedChecker` decorator with configurable cache backends
- `CheckResult` class for detailed check results including confidence and matched checker
- Cache layer with multiple implementations:
  - `ArrayCache` for in-memory caching
  - `FileCache` for file-based caching
  - `Psr6Adapter` for PSR-6 CacheItemPool compatibility
  - `Psr16Adapter` for PSR-16 SimpleCache compatibility
- `ChainChecker::getLastMatchedChecker()` to identify which checker matched
- Batch operations: `checkBatch()` and `isDisposableBatch()`
- `check()` method returning `CheckResult` with detailed information
- GitHub Actions CI/CD workflows for tests and static analysis
- PHPStan configuration (level 8)
- PHP CS Fixer configuration (PSR-12)

### Changed
- Improved error handling in `UrlSource` with proper exceptions instead of error suppression
- Made timeout and redirect limits configurable in `UrlSource`

## [1.0.0] - 2024-XX-XX

### Added
- Initial release
- Core `DisposableEmailChecker` class
- `FileChecker` for file-based domain lists
- `CallbackChecker` for custom checking logic
- `ChainChecker` for combining multiple checkers
- `DomainNormalizer` with PSL support
- Multiple parsers: `TextLineParser`, `JsonArrayParser`, `CallbackParser`
- Source management: `FileSource`, `UrlSource`, `SourceRegistry`
- Bundled disposable domains list
- 5 pre-configured external sources
