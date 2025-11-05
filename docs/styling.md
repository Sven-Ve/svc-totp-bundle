# Styling and Customization

## CSS Classes

All templates in SvcTotpBundle use unique CSS classes with the prefix `svc-totp-` to allow easy customization in your host application.

### Page Classes

Each page has a main container class that wraps the entire content:

| Template | Class | Description |
|----------|-------|-------------|
| `totp/manageTotp.html.twig` | `svc-totp-manage-page` | 2FA management page (enable/disable/reset 2FA) |
| `totp/enterTotp.html.twig` | `svc-totp-enter-page` | 2FA code entry page during login |
| `totp/backCodesTotp.html.twig` | `svc-totp-backup-codes-page` | Backup codes display page |
| `admin/users.html.twig` | `svc-totp-admin-page` | Admin user management page |
| `forgot/forget2FA.html.twig` | `svc-totp-forgot-page` | Forgot 2FA request page |

### Component Classes

Reusable components and partials:

| Template | Class | Description |
|----------|-------|-------------|
| `forgot/_forgot2FAbtn.html.twig` | `svc-totp-forgot-button` | "Forgot/lost 2FA code" button |
| `_gen/_help.html.twig` | `svc-totp-help-section` | Help section with action explanations |

## Customization Examples

### Override Page Styles

Create custom styles in your application's CSS file:

```css
/* Customize the 2FA management page */
.svc-totp-manage-page {
    max-width: 800px;
    margin: 0 auto;
    background-color: #f8f9fa;
}

/* Customize the login 2FA entry page */
.svc-totp-enter-page {
    text-align: center;
    padding: 2rem;
}

/* Customize backup codes page */
.svc-totp-backup-codes-page {
    font-family: monospace;
}

/* Customize admin page */
.svc-totp-admin-page {
    padding: 2rem;
}
```

### Override Component Styles

```css
/* Customize the forgot 2FA button */
.svc-totp-forgot-button {
    background-color: #ff6b6b;
    border-color: #ff6b6b;
}

/* Customize the help section */
.svc-totp-help-section {
    margin-top: 2rem;
    padding: 1rem;
    background-color: #e9ecef;
    border-radius: 0.25rem;
}
```

### Dark Mode Support

All pages use Bootstrap classes and can easily be styled for dark mode:

```css
[data-bs-theme="dark"] .svc-totp-manage-page,
[data-bs-theme="dark"] .svc-totp-enter-page,
[data-bs-theme="dark"] .svc-totp-backup-codes-page,
[data-bs-theme="dark"] .svc-totp-forgot-page {
    background-color: #212529;
    color: #f8f9fa;
}

[data-bs-theme="dark"] .svc-totp-admin-page {
    background-color: #1a1d20;
}
```

## Template Override

If you need more extensive customization, you can override the entire template in your application:

```
templates/
└── bundles/
    └── SvcTotpBundle/
        └── totp/
            └── manageTotp.html.twig  # Your custom template
```

When overriding templates, it's recommended to keep the same CSS classes for consistency, but you can modify the structure as needed.

## Bootstrap Dependencies

The templates use Bootstrap 5.3+ classes for layout and styling:
- Form controls (`form-control`, `btn`, etc.)
- Layout (`container`, `row`, `col`, etc.)
- Alerts (`alert`, `alert-success`, `alert-warning`, etc.)
- Tables (`table`, `table-responsive`, etc.)

Make sure your application includes Bootstrap CSS or override these classes in your custom styles.
