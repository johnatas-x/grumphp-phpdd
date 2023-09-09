# Description

This repository adds a task for GrumPHP that launchs [PhpDeprecationDetector](https://github.com/wapmorgan/PhpDeprecationDetector).
During a commit check for deprecated functionality. If a deprecated functionality is detected, it won't pass.


# Installation

Install it using composer:

```composer require --dev johnatas-x/grumphp-phpdd```


# Usage

1) Add the extension in your grumphp.yml file:
```yaml
extensions:
  - GrumphpPhpdd\ExtensionLoader
```

2) Add phpdd to the tasks:
```
tasks:
  phpdd:
    files: []
    target: ~
    after: ~
    exclude: []
    max_size: ~
    file_extensions: []
    skip_checks: []
```

- **files** (array): Directories/files you want to analyze.
- **target** (string): Sets target PHP interpreter version. [default: "8.0"]
- **after** (string): Sets initial PHP interpreter version for checks. [default: "5.3"]
- **exclude** (array): Sets excluded file or directory names for scanning.
- **max_size** (string): Sets max size of php file. If file is larger, it will be skipped. [default: "1mb"]
- **file_extensions** (array): Sets file extensions to be parsed. [default: "php, php5, phtml"]
- **skip_checks** (array): Skip all checks containing any of the given values.