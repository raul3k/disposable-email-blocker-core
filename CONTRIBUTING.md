# Contributing

Thank you for your interest in contributing! This document provides guidelines and information about contributing to this project.

## Code of Conduct

Please be respectful and constructive in all interactions.

## How to Contribute

### Reporting Bugs

1. Check if the bug has already been reported in [Issues](../../issues)
2. If not, create a new issue using the **Bug Report** template
3. Provide as much detail as possible

### Suggesting Features

1. Check if the feature has already been suggested in [Issues](../../issues)
2. If not, create a new issue using the **Feature Request** template
3. Explain the use case and benefits

### Pull Requests

1. Fork the repository
2. Create a feature branch from `main`:

   ```bash
   git checkout -b feat/your-feature-name
   ```

3. Make your changes
4. Ensure tests pass (if applicable)
5. Commit using [Conventional Commits](https://www.conventionalcommits.org/):

   ```text
   feat: add new feature
   fix: resolve bug in component
   docs: update README
   ```

6. Push and open a Pull Request

## Commit Message Format

This project uses [Conventional Commits](https://www.conventionalcommits.org/).

Format: `<type>(<scope>): <description>`

### Types

| Type       | Description                                      |
| ---------- | ------------------------------------------------ |
| `feat`     | A new feature                                    |
| `fix`      | A bug fix                                        |
| `docs`     | Documentation only changes                       |
| `style`    | Code style changes (formatting, semicolons, etc.) |
| `refactor` | Code changes that neither fix bugs nor add features |
| `test`     | Adding or updating tests                         |
| `chore`    | Maintenance tasks, dependencies, configs         |

### Examples

```text
feat: add user authentication
feat(api): add pagination support
fix: resolve memory leak in parser
fix(ui): correct button alignment
docs: update installation instructions
chore(deps): update dependencies
```

## Development Setup

<!-- Add your project-specific setup instructions here -->

```bash
# Clone the repository
git clone <repository-url>
cd <repository-name>

# Install dependencies
# ...

# Run tests
# ...
```

## Questions?

Feel free to open an issue for any questions or concerns.
