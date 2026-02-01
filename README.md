# WPZylos Hooks

[![PHP Version](https://img.shields.io/badge/php-%5E8.0-blue)](https://php.net)
[![License](https://img.shields.io/badge/license-MIT-green)](LICENSE)
[![GitHub](https://img.shields.io/badge/GitHub-WPDiggerStudio-181717?logo=github)](https://github.com/WPDiggerStudio/wpzylos-hooks)

WordPress hook management with plugin-scoped custom hooks for WPZylos framework.

📖 **[Full Documentation](https://wpzylos.com)** | 🐛 **[Report Issues](https://github.com/WPDiggerStudio/wpzylos-hooks/issues)**

---

## ✨ Features

- **Plugin-scoped Hooks** — Automatically prefixed actions and filters
- **Hook Manager** — Centralized hook registration
- **Fluent API** — Chainable hook methods
- **Priority Control** — Easy priority management
- **Bulk Registration** — Register multiple hooks at once

---

## 📋 Requirements

| Requirement | Version |
| ----------- | ------- |
| PHP         | ^8.0    |
| WordPress   | 6.0+    |

---

## 🚀 Installation

```bash
composer require wpdiggerstudio/wpzylos-hooks
```

---

## 📖 Quick Start

```php
use WPZylos\Framework\Hooks\HookManager;

$hooks = new HookManager($context);

// Add WordPress action
$hooks->action('init', [$this, 'initialize']);

// Add WordPress filter
$hooks->filter('the_content', [$this, 'modifyContent']);

// Plugin-scoped hooks (auto-prefixed)
$hooks->pluginAction('activated', [$this, 'onActivate']);
$hooks->pluginFilter('settings', [$this, 'filterSettings']);
```

---

## 🏗️ Core Features

### WordPress Hooks

```php
// Actions
$hooks->action('wp_enqueue_scripts', [$this, 'enqueueAssets']);
$hooks->action('admin_menu', [$this, 'registerMenu'], 20);

// Filters
$hooks->filter('the_title', [$this, 'filterTitle']);
$hooks->filter('body_class', [$this, 'addBodyClasses'], 10, 2);
```

### Plugin-Scoped Hooks

```php
// Creates: myplugin_user_created
$hooks->pluginAction('user_created', [$listener, 'onUserCreated']);

// Creates: myplugin_settings filter
$hooks->pluginFilter('settings', [$this, 'filterSettings']);
```

### Triggering Hooks

```php
// Trigger plugin action
$hooks->doPluginAction('user_created', $user);

// Apply plugin filter
$settings = $hooks->applyPluginFilter('settings', $defaults);
```

---

## 📦 Related Packages

| Package                                                                | Description             |
| ---------------------------------------------------------------------- | ----------------------- |
| [wpzylos-core](https://github.com/WPDiggerStudio/wpzylos-core)         | Application foundation  |
| [wpzylos-events](https://github.com/WPDiggerStudio/wpzylos-events)     | PSR-14 event dispatcher |
| [wpzylos-scaffold](https://github.com/WPDiggerStudio/wpzylos-scaffold) | Plugin template         |

---

## 📖 Documentation

For comprehensive documentation, tutorials, and API reference, visit **[wpzylos.com](https://wpzylos.com)**.

---

## ☕ Support the Project

If you find this package helpful, consider buying me a coffee! Your support helps maintain and improve the WPZylos ecosystem.

<a href="https://www.paypal.com/donate/?hosted_button_id=66U4L3HG4TLCC" target="_blank">
  <img src="https://img.shields.io/badge/Donate-PayPal-blue.svg?style=for-the-badge&logo=paypal" alt="Donate with PayPal" />
</a>

---

## 📄 License

MIT License. See [LICENSE](LICENSE) for details.

---

## 🤝 Contributing

Contributions are welcome! Please see [CONTRIBUTING.md](CONTRIBUTING.md) for guidelines.

---

**Made with ❤️ by [WPDiggerStudio](https://github.com/WPDiggerStudio)**
