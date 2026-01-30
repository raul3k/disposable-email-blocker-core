# Security Policy

## Supported Versions

| Version | Supported          |
| ------- | ------------------ |
| 1.x     | :white_check_mark: |

## Reporting a Vulnerability

If you discover a security vulnerability, please report it responsibly:

1. **Do NOT** open a public issue
2. Use [GitHub's private vulnerability reporting](../../security/advisories/new) to submit your report
3. Include in your report:
   - Description of the vulnerability
   - Steps to reproduce
   - Potential impact
   - Any suggested fixes (optional)

We will acknowledge receipt and provide updates on the fix timeline.

## Security Considerations

### Domain List Updates

The bundled domain list may become outdated. For production use:

- Regularly update the package to get new domain lists
- Consider using external sources that are updated more frequently
- Implement your own update mechanism if needed

### External Sources

When using `UrlSource` to fetch domain lists:

- Only use trusted sources
- Consider the security implications of fetching remote data
- Use HTTPS URLs when possible
- Be aware that remote sources may be unavailable or compromised

### Input Validation

This library validates email format before checking domains, but:

- Always sanitize user input before passing to this library
- Don't rely solely on this library for email validation
- Use additional validation as needed for your use case

### Caching

When using caching:

- Be aware of cache poisoning risks if using shared caches
- Use appropriate TTL values
- Consider the sensitivity of cached data
