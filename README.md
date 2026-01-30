# Project Name

[![CI](../../actions/workflows/ci.yml/badge.svg)](../../actions/workflows/ci.yml)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

Brief description of your project.

## Features

- Feature 1
- Feature 2

## Installation

```bash
# Add installation instructions
```

## Usage

```bash
# Add usage examples
```

## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md) for guidelines.

## License

MIT License - see [LICENSE](LICENSE) for details.

---

# Template Usage Guide

> **Delete this section after creating your repository.**

This is a GitHub template repository with pre-configured tooling for professional projects.

## What's Included

| File/Directory | Description |
|----------------|-------------|
| `.github/ISSUE_TEMPLATE/` | Bug report and feature request templates |
| `.github/PULL_REQUEST_TEMPLATE.md` | Pull request template with checklist |
| `.github/workflows/commitlint.yml` | Conventional commits validation |
| `.github/workflows/release-please.yml` | Automated releases and changelog |
| `.github/workflows/sync.yml` | Sync files to other repositories |
| `.github/sync.yml` | Configuration for repo-file-sync-action |
| `.github/dependabot.yml` | Automated GitHub Actions updates |
| `.coderabbit.yaml` | AI code review configuration |
| `CONTRIBUTING.md` | Contribution guidelines |
| `SECURITY.md` | Security policy |
| `LICENSE` | MIT License (update year and name) |
| `.gitignore` | Common OS/editor ignores |

## Setup Checklist

After creating a repository from this template:

- [ ] Update `README.md` with your project info (delete this section)
- [ ] Update `LICENSE` with your name and year
- [ ] Update `SECURITY.md` with supported versions
- [ ] Configure CodeRabbit (see [CodeRabbit Setup](#coderabbit-setup))
- [ ] Configure branch protection rules (see [Branch Protection](#branch-protection))
- [ ] Add language-specific CI workflow (see [Adding CI Workflows](#adding-ci-workflows))
- [ ] Add language-specific `.gitignore` entries
- [ ] Update `release-type` in release-please workflow (see [Release Please](#release-please))

## CodeRabbit Setup

[CodeRabbit](https://coderabbit.ai) provides AI-powered code reviews on pull requests.

### Installation

1. Go to [github.com/apps/coderabbitai](https://github.com/apps/coderabbitai)
2. Click **Install**
3. Select your account/organization
4. Choose **All repositories** or select specific ones
5. Click **Install**

### Configuration

The `.coderabbit.yaml` file is pre-configured with:

```yaml
language: "en-US"
reviews:
  profile: "assertive"        # thorough reviews
  high_level_summary: true    # summary at top of PR
  auto_review:
    enabled: true
    drafts: false             # skip draft PRs
    base_branches: [main]     # only review PRs to main
```

### Customization Options

| Setting | Options | Description |
|---------|---------|-------------|
| `profile` | `chill`, `assertive`, `hardcore` | Review strictness |
| `high_level_summary` | `true`/`false` | Add summary comment |
| `poem` | `true`/`false` | Add a poem (fun!) |
| `request_changes_workflow` | `true`/`false` | Block PR until resolved |

[Full configuration docs](https://docs.coderabbit.ai/configuration)

## Branch Protection

Recommended settings for the `main` branch:

1. Go to **Settings** > **Branches** > **Add rule**
2. Branch name pattern: `main`
3. Enable:
   - [x] Require a pull request before merging
   - [x] Require approvals (1+)
   - [x] Require status checks to pass (select your CI jobs)
   - [x] Require conversation resolution before merging
   - [x] Do not allow bypassing the above settings

## Adding CI Workflows

This template includes only commit linting (language-agnostic). Add a `ci.yml` workflow for your stack:

### Workflow Structure

```yaml
name: CI

on:
  push:
    branches: [main]
  pull_request:

jobs:
  lint:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      # Add linting steps

  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      # Add test steps

  build:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      # Add build steps
```

### Examples by Language

<details>
<summary><strong>Rust</strong></summary>

```yaml
jobs:
  lint:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: dtolnay/rust-toolchain@stable
        with:
          components: rustfmt, clippy
      - run: cargo fmt --check
      - run: cargo clippy -- -D warnings

  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - run: cargo test

  build:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - run: cargo build --release
```

Add to `.gitignore`:
```
/target/
Cargo.lock  # for libraries only
```

</details>

<details>
<summary><strong>Node.js / TypeScript</strong></summary>

```yaml
jobs:
  lint:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: actions/setup-node@v4
        with:
          node-version: '20'
          cache: 'npm'
      - run: npm ci
      - run: npm run lint
      - run: npm run format:check  # if using prettier

  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: actions/setup-node@v4
        with:
          node-version: '20'
          cache: 'npm'
      - run: npm ci
      - run: npm test

  build:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: actions/setup-node@v4
        with:
          node-version: '20'
          cache: 'npm'
      - run: npm ci
      - run: npm run build
```

Add to `.gitignore`:
```
node_modules/
dist/
.next/
```

Add to `dependabot.yml`:
```yaml
  - package-ecosystem: "npm"
    directory: "/"
    schedule:
      interval: "weekly"
    commit-message:
      prefix: "chore(deps)"
```

</details>

<details>
<summary><strong>Python</strong></summary>

```yaml
jobs:
  lint:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: actions/setup-python@v5
        with:
          python-version: '3.12'
      - run: pip install ruff
      - run: ruff check .
      - run: ruff format --check .

  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: actions/setup-python@v5
        with:
          python-version: '3.12'
      - run: pip install -e ".[test]"
      - run: pytest

  build:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: actions/setup-python@v5
        with:
          python-version: '3.12'
      - run: pip install build
      - run: python -m build
```

Add to `.gitignore`:
```
__pycache__/
*.py[cod]
.venv/
venv/
dist/
*.egg-info/
```

Add to `dependabot.yml`:
```yaml
  - package-ecosystem: "pip"
    directory: "/"
    schedule:
      interval: "weekly"
    commit-message:
      prefix: "chore(deps)"
```

</details>

<details>
<summary><strong>Go</strong></summary>

```yaml
jobs:
  lint:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: actions/setup-go@v5
        with:
          go-version: '1.22'
      - uses: golangci/golangci-lint-action@v4

  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: actions/setup-go@v5
        with:
          go-version: '1.22'
      - run: go test ./...

  build:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: actions/setup-go@v5
        with:
          go-version: '1.22'
      - run: go build ./...
```

Add to `.gitignore`:
```
/bin/
/dist/
```

Add to `dependabot.yml`:
```yaml
  - package-ecosystem: "gomod"
    directory: "/"
    schedule:
      interval: "weekly"
    commit-message:
      prefix: "chore(deps)"
```

</details>

## Release Please

This template includes [release-please](https://github.com/googleapis/release-please) for automated releases.

### How it Works

1. Write commits using [Conventional Commits](https://www.conventionalcommits.org/)
2. Release Please creates/updates a "Release PR" with changelog
3. When you merge the Release PR, it creates a GitHub Release

### Commit Types and Versioning

| Commit Type | Version Bump | Example |
|-------------|--------------|---------|
| `feat:` | Minor (0.1.0 → 0.2.0) | `feat: add dark mode` |
| `fix:` | Patch (0.1.0 → 0.1.1) | `fix: resolve memory leak` |
| `feat!:` or `BREAKING CHANGE:` | Major (0.1.0 → 1.0.0) | `feat!: redesign API` |
| `chore:`, `docs:`, `style:` | No release | `docs: update README` |

### Release Types

Update `release-type` in `.github/workflows/release-please.yml` based on your project:

| Type | Use Case |
|------|----------|
| `simple` | Generic projects (default) |
| `node` | Node.js (updates package.json) |
| `python` | Python (updates setup.py/pyproject.toml) |
| `rust` | Rust (updates Cargo.toml) |
| `go` | Go modules |

[See all release types](https://github.com/googleapis/release-please#release-types)

## Syncing Template Updates

[repo-file-sync-action](https://github.com/BetaHuhn/repo-file-sync-action) keeps all your repositories in sync with this template. When you improve a file here, it automatically creates PRs in your other repos.

### Why Use This?

Without sync:
- You improve an issue template in this repo
- Your 10 other repos still have the old template
- You manually copy the file to each repo (tedious!)

With sync:
- You improve an issue template here
- PRs are automatically created in all your repos
- Just review and merge

### Setup (one-time)

1. **Create a Personal Access Token (PAT)**
   - Go to [github.com/settings/tokens](https://github.com/settings/tokens)
   - Click **Generate new token (classic)**
   - Select scope: `repo` (full control of private repositories)
   - Copy the token

2. **Add the token as a secret**
   - Go to this repository's **Settings** > **Secrets and variables** > **Actions**
   - Click **New repository secret**
   - Name: `GH_PAT`
   - Value: paste your token

### Adding Repositories to Sync

Edit `.github/sync.yml`:

```yaml
group:
  - files:
      - source: .github/ISSUE_TEMPLATE/
        dest: .github/ISSUE_TEMPLATE/
      - source: .github/PULL_REQUEST_TEMPLATE.md
        dest: .github/PULL_REQUEST_TEMPLATE.md
      - source: .coderabbit.yaml
        dest: .coderabbit.yaml
      - source: CONTRIBUTING.md
        dest: CONTRIBUTING.md
    repos: |
      raul3k/rtrim
      raul3k/another-repo
      raul3k/yet-another-repo
```

### Configuration Options

```yaml
group:
  - files:
      - source: file.md           # sync single file
        dest: file.md
      - source: folder/           # sync entire folder
        dest: folder/
      - source: src/config.json
        dest: different/path.json # rename or move
    repos: |
      owner/repo-1
      owner/repo-2

  # You can have multiple groups with different files
  - files:
      - source: special-config.yml
        dest: special-config.yml
    repos: |
      owner/special-repo
```

### How it Works

```
┌─────────────────────┐
│   Template Repo     │
│  (this repository)  │
└──────────┬──────────┘
           │ push to main
           ▼
┌─────────────────────┐
│  Sync Workflow      │
│  Detects changes    │
└──────────┬──────────┘
           │ creates PRs
           ▼
┌─────────────────────┐
│  Target Repos       │
│  repo-1, repo-2...  │
│  ┌───────────────┐  │
│  │ PR: Sync from │  │
│  │ template      │  │
│  └───────────────┘  │
└─────────────────────┘
```

### Triggering Manually

You can also trigger the sync manually:
1. Go to **Actions** > **Sync Files**
2. Click **Run workflow**

### What Gets Synced

By default, this template syncs:

| File | Purpose |
|------|---------|
| `.github/ISSUE_TEMPLATE/` | Issue templates |
| `.github/PULL_REQUEST_TEMPLATE.md` | PR template |
| `.coderabbit.yaml` | CodeRabbit config |
| `CONTRIBUTING.md` | Contribution guidelines |

Modify `.github/sync.yml` to add or remove files.
