# Contributing

Thank you for considering contributing to this project! We welcome contributions from everyone.

## Code of Conduct

Please be respectful and constructive in all interactions.

## Development Setup

1. Fork and clone the repository
2. Install dependencies:
   ```bash
   composer install
   ```
3. Run tests to make sure everything works:
   ```bash
   composer test
   ```

## Code Quality

Before submitting a pull request, ensure your code passes all quality checks:

```bash
# Run all quality checks
composer quality

# Or run them individually:
composer test          # Run tests
composer analyse       # Run PHPStan
composer cs:check      # Check code style
composer cs:fix        # Fix code style issues
```

## Pull Request Process

1. Create a new branch for your feature or fix:
   ```bash
   git checkout -b feat/your-feature-name
   ```

2. Make your changes, ensuring:
   - All tests pass
   - Code style is consistent (run `composer cs:fix`)
   - PHPStan passes at level 8
   - New features have tests
   - Documentation is updated if needed

3. Commit using [Conventional Commits](https://www.conventionalcommits.org/):
   ```text
   feat: add new feature
   fix: resolve bug in component
   docs: update README
   ```

4. Push your branch and create a pull request

5. Fill out the pull request template with:
   - Description of changes
   - Related issue (if any)
   - Testing done
   - Breaking changes (if any)

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

## Coding Standards

- Follow PSR-12 coding style
- Use strict types: `declare(strict_types=1);`
- Add type hints for all parameters and return types
- Write descriptive method and variable names
- Keep methods focused and small
- Add PHPDoc comments for public methods

## Testing

- Write tests for all new features
- Maintain or improve code coverage
- Use descriptive test method names
- Test edge cases and error conditions

## Adding New Checkers

When adding a new checker:

1. Implement `CheckerInterface`
2. Add comprehensive tests
3. Document usage in README
4. Consider performance implications

## Reporting Issues

When reporting issues, please include:

- PHP version
- Package version
- Steps to reproduce
- Expected vs actual behavior
- Relevant code snippets

## Questions?

Feel free to open an issue for any questions or discussions.
