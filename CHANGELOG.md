# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.0.0] - 2025-01-27

### Added
- Composer package support with PSR-4 autoloading
- Laravel service provider and facade for seamless integration
- Namespaced `MomoLog\MomoLog` class with static methods
- Configuration system with environment variable support
- Comprehensive documentation and examples
- MIT license
- PHP 7.4+ compatibility with modern features

### Changed
- Converted from procedural to object-oriented architecture
- Maintained backward compatibility with global helper functions
- Improved error handling and environment detection
- Enhanced performance with better async handling

### Removed
- Old procedural constants (replaced with configuration system)

## [1.1.0] - Previous Version

### Added
- Fire-and-forget async mode for minimal performance impact
- Automatic environment detection (dev/production)
- Multiple data type support with intelligent formatting
- Stack trace and caller information
- Memory usage tracking
- SQL query debugging support
- Fatal error catching with shutdown function

### Fixed
- Improved cURL async handling
- Better error suppression for production use
