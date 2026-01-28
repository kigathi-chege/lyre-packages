# Multi-Package Release CLI

`release` is a Bash script for safely releasing multiple packages in a monorepo that uses Git submodules. It supports version bumps, automated detection of changes, and updating submodule pointers.

---

## Features

- Release individual or multiple packages
- Supports `auto` mode to release only changed packages
- Safely updates `composer.json` versions
- Works with Git submodules
- Supports `dry-run` mode for safe previews
- Updates monorepo pointers automatically
- Works from any directory inside the repository

---

## Requirements

- Bash (`#!/usr/bin/env bash`)
- `git`
- `jq` (for updating `composer.json`)

Ensure that `submodule-publish.sh` is executable:

```bash
chmod +x submodule-publish.sh
```

````

---

## Usage

```bash
release [options] <spec> [<spec>...]
```

### Options

| Option          | Description                        |
| --------------- | ---------------------------------- |
| `-n, --dry-run` | Show what would happen, do nothing |
| `-h, --help`    | Show this help message             |

### Specs

Specs define which packages and versions to release. They can be in any of these forms:

- `content` — release the package with auto version detection
- `content 1.4.0` — release `content` with a specific version
- `content 1.4.0 "feat(content): message"` — release with custom commit message
- `content:version=1.4.0,message="feat(content): message"` — key-value style
- `file:version=1.3.2`
- `lyre:version=2.0.0,message="breaking change"`

---

## Examples

Release a single package:

```bash
release content
```

Release multiple packages:

```bash
release content file
```

Release with specific version:

```bash
release content 1.4.0
```

Release with version and custom commit message:

```bash
release content 1.4.0 "feat(content): add new feature"
```

Key-value style release:

```bash
release content:version=1.4.0,message="feat(content): add new feature"
```

Dry-run example:

```bash
release -n content 1.4.0 "feat(content): test dry run"
```

Release all changed packages automatically:

```bash
release auto
```

Release all packages:

```bash
release all
```

Release packages changed since a Git ref:

```bash
release since main
```

---

## How It Works

1. Validates repository and submodules
2. Determines which packages to release
3. Checks out the default branch of each submodule
4. Ensures working tree is clean
5. Updates `composer.json` version safely (if applicable)
6. Commits and pushes changes for each package
7. Calls `submodule-publish.sh` for each released package
8. Updates monorepo submodule pointers in root repository
9. Supports `DRY_RUN` mode to preview actions without making changes

---

## Notes

- The script can be run from **any directory** inside the repo.
- Monorepo pointer updates ensure all released versions are reflected in the main repository.
- Safe `jq` handling prevents leftover temporary files in case of failure.
- Dry-run mode logs all commands instead of executing them.

---

## License

MIT License © nipate

````
