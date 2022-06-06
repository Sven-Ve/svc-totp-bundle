# Usage

## Controller

### Enable/Disable 2FA

call the path "app_totp_manage" in your twig template or controller:

Example:

```html
  <a class="dropdown-item" href="{{ path('svc_totp_manage') }}">
    <i class="fa-solid fa-shield"></i>
    {% trans %}Manage 2FA{% endtrans %}
  </a>
```

### Clear trusted devices for current/all users

call the "svc_totp_cleartd" path in your Twig template or controller. <br/>
Set the "allUsers" parameter to true if you want to delete trusted devices for all users (requires ROLE_ADMIN), otherwise (default) only devices for the current user will be deleted.

Example:

```html
<a class="dropdown-item" href="{{ path('svc_totp_cleartd', {'allUsers' : true} ) }}">
  <i class="fa-solid fa-folder-minus"></i>
  {% trans %}Clear trusted devices{% endtrans %} ({% trans %}all{% endtrans %})
</a>
```