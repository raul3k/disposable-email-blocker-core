# Disposable Email Blocker - Core

Fast disposable/temporary email detection with full Public Suffix List (PSL) support, pattern matching, caching, and whitelist capabilities.

## Features

- **Fast O(1) lookups** using hash-based domain checking
- **Full PSL support** - correctly handles subdomains and public suffixes
- **IDN/Punycode support** - international domain names are handled correctly
- **Pattern matching** - detect suspicious domain patterns via regex
- **Whitelist support** - allow specific domains to bypass checks
- **Caching layer** - PSR-6/PSR-16 compatible cache adapters
- **Detailed results** - `CheckResult` with matched checker info
- **Batch operations** - efficiently check multiple emails at once
- **Multiple checkers** - file-based, callback-based, or chain multiple checkers
- **Domain parsing** - `DomainInfo` for detailed domain analysis (PSL, subdomain, IDN)
- **Extensible sources** - built-in sources + custom parsers for any format
- **CLI tool** - `bin/update-domains` to fetch and merge domain lists
- **Framework agnostic** - use with any PHP project

## Installation

```bash
composer require raul3k/disposable-email-blocker-core
```

## Quick Start

```php
use Raul3k\DisposableBlocker\Core\DisposableEmailChecker;

$checker = DisposableEmailChecker::create();

// Check if email is disposable
$checker->isDisposable('test@mailinator.com'); // true
$checker->isDisposable('test@gmail.com');      // false

// Safe version (returns false for invalid emails instead of throwing)
$checker->isDisposableSafe('invalid-email');   // false

// Check domain directly
$checker->isDomainDisposable('tempmail.com');  // true
```

## Builder

The fluent builder is the recommended way to compose checkers:

```php
use Raul3k\DisposableBlocker\Core\DisposableEmailChecker;

$checker = DisposableEmailChecker::builder()
    ->withBundledDomains()
    ->withPatternDetection()
    ->withWhitelist(['mycompany.com', 'partner.org'])
    ->withFileCache('/tmp/disposable-cache')
    ->build();

$checker->isDisposable('test@mailinator.com'); // true
$checker->isDisposable('test@mycompany.com');  // false (whitelisted)
```

Available builder methods:

- `withBundledDomains()` - use the bundled ~159k domain list
- `withDomainsFile(string $path)` - use a custom domains file
- `withPatternDetection(?array $patterns = null)` - enable regex pattern matching
- `withChecker(CheckerInterface $checker)` - add any custom checker
- `withCallback(callable $callback)` - add a callback-based checker
- `withWhitelist(array $domains)` - whitelist specific domains
- `withFileCache(string $directory, ?int $ttl = 3600)` - enable file-based caching
- `withCache(CacheInterface $cache, ?int $ttl = 3600)` - use a custom cache
- `withNormalizer(DomainNormalizer $normalizer)` - use a custom normalizer

## Detailed Check Results

Get detailed information about the check result:

```php
use Raul3k\DisposableBlocker\Core\DisposableEmailChecker;

$checker = DisposableEmailChecker::create();

// Check an email
$result = $checker->check('test@mailinator.com');

$result->isDisposable();      // true
$result->isSafe();            // false
$result->getDomain();         // 'mailinator.com'
$result->getOriginalInput();  // 'test@mailinator.com'
$result->getMatchedChecker(); // 'Raul3k\DisposableBlocker\Core\Checkers\FileChecker'
$result->isWhitelisted();     // false
$result->toArray();           // array representation
$result->toJson();            // JSON string

// Check a domain directly
$result = $checker->checkDomain('mailinator.com');
$result->isDisposable(); // true

// Safe versions (return safe result for invalid input instead of throwing)
$result = $checker->checkSafe('invalid-email');
$result->isSafe(); // true
```

## Batch Operations

Check multiple emails efficiently:

```php
use Raul3k\DisposableBlocker\Core\DisposableEmailChecker;

$checker = DisposableEmailChecker::create();

$emails = [
    'user1@gmail.com',
    'user2@mailinator.com',
    'user3@yahoo.com',
];

// Get boolean results
$results = $checker->isDisposableBatch($emails);
// ['user1@gmail.com' => false, 'user2@mailinator.com' => true, 'user3@yahoo.com' => false]

// Get detailed results
$results = $checker->checkBatch($emails);
// ['user1@gmail.com' => CheckResult, 'user2@mailinator.com' => CheckResult, ...]
```

## Domain Info

Parse and inspect domain details using the Public Suffix List:

```php
use Raul3k\DisposableBlocker\Core\DomainInfo;

$info = DomainInfo::parse('user@mail.example.co.uk');

$info->domain();           // 'example.co.uk'
$info->subdomain();        // 'mail'
$info->publicSuffix();     // 'co.uk'
$info->secondLevelDomain(); // 'example'
$info->host();             // 'mail.example.co.uk'
$info->isIcann();          // true
$info->isPrivate();        // false
$info->isKnownSuffix();    // true
$info->isValid();          // true

// IDN support
$info = DomainInfo::parse('пример.рф');
$info->ascii();   // 'xn--e1afmkfd.xn--p1ai'
$info->unicode(); // 'пример.рф'
$info->isIdn();   // true

// Works with emails, domains, and URLs
DomainInfo::parse('user@github.io')->isPrivate();    // true
DomainInfo::parse('https://example.com/path')->domain(); // 'example.com'
```

## Pattern-Based Detection

Detect suspicious domain patterns using regex:

```php
use Raul3k\DisposableBlocker\Core\DisposableEmailChecker;
use Raul3k\DisposableBlocker\Core\Checkers\{ChainChecker, FileChecker, PatternChecker};

// Combine file-based checking with pattern matching
$checker = DisposableEmailChecker::create(
    new ChainChecker([
        new FileChecker(__DIR__ . '/domains.txt'),
        new PatternChecker(), // Uses default patterns
    ])
);

// Default patterns detect:
// - temp*, disposable*, throwaway*, fake*, junk*, spam*
// - 10minutemail, 5minmail, etc.
// - guerrillamail, yopmail, mailinator
// - Suspicious TLDs: .tk, .ml, .ga, .cf, .gq

// Add custom patterns
$patternChecker = new PatternChecker();
$patternChecker->addPattern('/^suspicious-/i');
```

## Whitelist Support

Allow specific domains to bypass disposable checks:

```php
use Raul3k\DisposableBlocker\Core\DisposableEmailChecker;
use Raul3k\DisposableBlocker\Core\Checkers\{FileChecker, WhitelistChecker};

$innerChecker = new FileChecker(__DIR__ . '/domains.txt');
$whitelistChecker = new WhitelistChecker($innerChecker, [
    'company.com',      // Allow company.com and all subdomains
    'partner.org',
]);

$checker = DisposableEmailChecker::create($whitelistChecker);

// Even if mailinator.com is in the list, whitelist takes precedence
$whitelistChecker->addToWhitelist('special-case.mailinator.com');

// Check whitelist status
$whitelistChecker->isWhitelisted('company.com');     // true
$whitelistChecker->isWhitelisted('sub.company.com'); // true (parent is whitelisted)
```

## Caching

Add caching to improve performance for repeated checks:

```php
use Raul3k\DisposableBlocker\Core\DisposableEmailChecker;
use Raul3k\DisposableBlocker\Core\Checkers\{FileChecker, CachedChecker};
use Raul3k\DisposableBlocker\Core\Cache\{ArrayCache, FileCache};

// In-memory cache (single request)
$cache = new ArrayCache();

// File-based cache (persistent)
$cache = new FileCache('/path/to/cache/dir');

// Wrap any checker with caching
$innerChecker = new FileChecker(__DIR__ . '/domains.txt');
$cachedChecker = new CachedChecker($innerChecker, $cache, ttl: 3600);

$checker = DisposableEmailChecker::create($cachedChecker);
```

### PSR-6/PSR-16 Cache Adapters

Use any PSR-compatible cache:

```php
use Raul3k\DisposableBlocker\Core\Cache\{Psr6Adapter, Psr16Adapter};

// PSR-16 (SimpleCache)
$cache = new Psr16Adapter($yourPsr16Cache);

// PSR-6 (CacheItemPool)
$cache = new Psr6Adapter($yourPsr6Pool);

$cachedChecker = new CachedChecker($innerChecker, $cache);
```

## Custom Checkers

### Using a Callback (Redis, Database, API, etc.)

```php
use Raul3k\DisposableBlocker\Core\DisposableEmailChecker;
use Raul3k\DisposableBlocker\Core\Checkers\CallbackChecker;

// Redis example
$checker = DisposableEmailChecker::create(
    new CallbackChecker(fn($domain) => $redis->sismember('disposable_domains', $domain))
);

// Database example
$checker = DisposableEmailChecker::create(
    new CallbackChecker(fn($domain) => DB::table('disposable_domains')
        ->where('domain', $domain)
        ->exists())
);
```

### Using a Custom File

```php
use Raul3k\DisposableBlocker\Core\DisposableEmailChecker;
use Raul3k\DisposableBlocker\Core\Checkers\FileChecker;

$checker = DisposableEmailChecker::create(
    new FileChecker('/path/to/your/domains.txt')
);
```

### Chaining Multiple Checkers

```php
use Raul3k\DisposableBlocker\Core\DisposableEmailChecker;
use Raul3k\DisposableBlocker\Core\Checkers\{ChainChecker, FileChecker, PatternChecker, CallbackChecker};

$checker = DisposableEmailChecker::create(
    new ChainChecker([
        new FileChecker('/path/to/domains.txt'),
        new PatternChecker(),
        new CallbackChecker(fn($domain) => $redis->sismember('extra_domains', $domain)),
    ])
);

// After checking, you can see which checker matched
$result = $checker->check('test@tempmail.com');
$result->getMatchedChecker(); // 'Raul3k\DisposableBlocker\Core\Checkers\PatternChecker'
```

## Working with Sources

Sources provide lists of disposable domains. The library includes several pre-configured sources.

### Available Built-in Sources

| Source | Format | Size |
|--------|--------|------|
| `disposable-email-domains` | Text | ~5k |
| `burner-email-providers` | Text | ~27k |
| `mailchecker` | Text | ~56k |
| `ivolo-disposable` | JSON | ~122k |
| `fakefilter` | Text | ~10k |

### Fetching from Sources

```php
use Raul3k\DisposableBlocker\Core\Sources\SourceRegistry;

$registry = new SourceRegistry();

// List available sources
$sources = $registry->list();
// ['disposable-email-domains', 'burner-email-providers', 'mailchecker', ...]

// Fetch domains from a source
$source = $registry->get('disposable-email-domains');
foreach ($source->fetch() as $domain) {
    echo $domain . "\n";
}
```

### Adding Custom Sources

```php
use Raul3k\DisposableBlocker\Core\Sources\{SourceRegistry, UrlSource, FileSource};
use Raul3k\DisposableBlocker\Core\Parsers\{TextLineParser, JsonArrayParser};

$registry = new SourceRegistry();

// Remote text file (one domain per line)
$registry->register(new UrlSource(
    url: 'https://example.com/domains.txt',
    name: 'my-text-source',
    parser: new TextLineParser()
));

// Remote JSON array
$registry->register(new UrlSource(
    url: 'https://example.com/domains.json',
    name: 'my-json-source',
    parser: new JsonArrayParser()
));

// JSON with nested path
$registry->register(new UrlSource(
    url: 'https://api.example.com/data.json',
    name: 'my-nested-json',
    parser: new JsonArrayParser('response.data.domains')
));

// Local file
$registry->register(new FileSource(
    path: '/path/to/local-domains.txt',
    name: 'my-local-source'
));
```

### Updating the Bundled Domain List

Use the CLI tool to fetch domains from all sources and update the bundled list:

```bash
# Update from all sources
./bin/update-domains

# Preview without writing
./bin/update-domains --dry-run

# Fetch from specific sources only
./bin/update-domains --source=disposable-email-domains --source=mailchecker

# Custom output path
./bin/update-domains --output=storage/domains.txt

# Show detailed progress
./bin/update-domains --verbose

# See all options
./bin/update-domains --help
```

You can customize sources via a `disposable-blocker.php` config file in your project root:

```php
<?php
use Raul3k\DisposableBlocker\Core\Sources\UrlSource;

return [
    'sources' => [
        new UrlSource(
            url: 'https://example.com/my-domains.txt',
            name: 'my-custom-source'
        ),
    ],
    'exclude_sources' => ['fakefilter'],
    'output_path' => __DIR__ . '/storage/disposable_domains.txt',
];
```

## Domain Normalization

The library normalizes domains using the Public Suffix List to correctly extract registrable domains:

```php
use Raul3k\DisposableBlocker\Core\DomainNormalizer;

$normalizer = new DomainNormalizer();

// Extract domain from email
$normalizer->normalizeFromEmail('user@sub.example.com'); // 'example.com'

// Normalize domain
$normalizer->normalizeDomain('sub.example.com');   // 'example.com'
$normalizer->normalizeDomain('sub.example.co.uk'); // 'example.co.uk'

// Handle IDN
$normalizer->normalizeDomain('пример.рф'); // 'xn--e1afmkfd.xn--p1ai'
```

## Framework Integration

For Laravel integration, see:
- [raul3k/disposable-email-blocker-laravel](https://github.com/raul3k/disposable-email-blocker-laravel)

## Development

```bash
# Install dependencies
composer install

# Run tests
composer test

# Run tests with coverage
composer test:coverage

# Static analysis
composer analyse

# Code style check
composer cs:check

# Fix code style
composer cs:fix

# Run all quality checks
composer quality
```

## License

MIT License. See [LICENSE](LICENSE) for details.
