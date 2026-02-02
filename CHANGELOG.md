# Changelog

All notable changes to `filament-tree` will be documented in this file.

## 4.0.0 - 2026-02-02

### Filament Tree v4.0.0 Release

This release upgrades Filament Tree to support Filament v5, introducing new features and improvements while maintaining compatibility with existing tree structures.

#### What's New

- Full compatibility with Filament v5
- Enhanced drag-and-drop performance for large trees
- Improved toolbar actions support (expanded from v3.1.0+)
- Updated asset compilation for better integration

#### Breaking Changes

- Minimum required Filament version is now v5.0

#### How to Upgrade

1. Update your `composer.json` to require `"solution-forest/filament-tree": "^4.0"`
2. Run `composer update`
3. Publish updated assets: `php artisan filament:assets`
4. If using custom themes, update your `tailwind.config.js` with the new asset paths
5. Test your tree widgets/pages for any custom overrides that may need adjustment

#### Migration Notes

- Review your model classes for any custom `determine*ColumnName()` methods and ensure they align with the new defaults
- Toolbar actions are now fully supported; update any conditional logic if previously limited
- Run `composer analyse` and `composer test` to verify compatibility

For full details, see the [commit changes](https://github.com/solutionforest/filament-tree/commit/096a6f068095d947e70188855bce4e5dd70d7b74). If you encounter issues, please check the [documentation](https://github.com/solutionforest/filament-tree#readme) or open an issue.

**Full Changelog**: https://github.com/solutionforest/filament-tree/compare/3.1.4...4.0.0

## 3.1.4 - 2026-02-02

### What's Changed in 3.1.4

#### 🔧 Other Changes

- Bump actions/checkout from 5 to 6 (2f2748f)
- Merge pull request #98 from ssulima/ssulima-patch-1 (387d598)

**Full Changelog**: https://github.com/solutionforest/filament-tree/compare/3.1.3...3.1.4

## 3.1.3 - 2025-10-22

### What's Changed

#### Other Changes

* Improve Advanced usage documentation by @inerba in https://github.com/solutionforest/filament-tree/pull/91
* Bump stefanzweifel/git-auto-commit-action from 6 to 7 by @dependabot[bot] in https://github.com/solutionforest/filament-tree/pull/92

#### 🐛 Bug fixes

* fix: Replace invalid askForLivewireComponentLocation method call in https://github.com/solutionforest/filament-tree/issues/93

### New Contributors

* @inerba made their first contribution in https://github.com/solutionforest/filament-tree/pull/91

**Full Changelog**: https://github.com/solutionforest/filament-tree/compare/3.1.2...3.1.3

## 3.1.2 - 2025-09-22

### 🐛 Bug fixes

- Fix tree action label problem

**Full Changelog**: https://github.com/solutionforest/filament-tree/compare/3.1.1...3.1.2

## 3.1.1 - 2025-09-22

<!-- Release notes generated using configuration in .github/release.yml at 3.x -->
### What's Changed

#### Other Changes

* fix: resolve getNodeCollapsedState() method call in tree item template by @cklei-carly in https://github.com/solutionforest/filament-tree/pull/89

**Full Changelog**: https://github.com/solutionforest/filament-tree/compare/3.1.0...3.1.1

## 3.1.0 - 2025-09-12

<!-- Release notes generated using configuration in .github/release.yml at 3.x -->
### What's Changed

#### Other Changes

* Bump actions/checkout from 4 to 5 by @dependabot[bot] in https://github.com/solutionforest/filament-tree/pull/82
* Feature/add toolbar actions by @cklei-carly in https://github.com/solutionforest/filament-tree/pull/87

### New Contributors

* @cklei-carly made their first contribution in https://github.com/solutionforest/filament-tree/pull/87

**Full Changelog**: https://github.com/solutionforest/filament-tree/compare/3.0.2...3.1.0

## 3.0.2 - 2025-08-22

### What's Changed in 3.0.2

#### 🐛 Bug fixes

- fix: update getModel() method signature for Filament v4 compatibility (225c2db)

**Full Changelog**: https://github.com/solutionforest/filament-tree/compare/3.0.1...3.0.2

## 3.0.1 - 2025-08-22

<!-- Release notes generated using configuration in .github/release.yml at 3.x -->
### What's Changed

#### 🐛 Bug fixes

* fix: replace blade component namespace with Filament 4 version by @codelikesuraj in https://github.com/solutionforest/filament-tree/pull/79
* 

#### Other Changes

* Bump aglipanci/laravel-pint-action from 2.5 to 2.6 by @dependabot[bot] in https://github.com/solutionforest/filament-tree/pull/75
* Bump actions/checkout from 4 to 5 by @dependabot[bot] in https://github.com/solutionforest/filament-tree/pull/76

### New Contributors

* @dependabot[bot] made their first contribution in https://github.com/solutionforest/filament-tree/pull/75
* @codelikesuraj made their first contribution in https://github.com/solutionforest/filament-tree/pull/79

**Full Changelog**: https://github.com/solutionforest/filament-tree/compare/3.0.0...3.0.1

## 3.0.0 - 2025-08-13

### Support Filament v4🎉

### Installation

```bash
composer require solution-forest/filament-tree:^3.0.0









```