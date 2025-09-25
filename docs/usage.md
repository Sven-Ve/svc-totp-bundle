# Usage

## Breaking Changes

**⚠️ As of version 6.2.0**: The `MfaCrudController` for EasyAdminBundle integration has been removed. If you were using the EasyAdmin integration, please use the built-in admin interface (`svc_totp_user_admin` route) or implement your own admin functionality using the existing controller methods.

## Controller

### Enable/Disable 2FA

call the path "app_totp_manage" in your twig template or controller<br/>
Example:

```html
  <a class="dropdown-item" href="{{ path('svc_totp_manage') }}">
    <i class="fa-solid fa-shield"></i>
    {% trans %}Manage 2FA{% endtrans %}
  </a>
```

You can add the parameter reset=true, then 2FA will be disabled and the shared secret will be deleted (you will need a new secret/QR code and you will have to store it in the authenticator again).<br/>
Example:
```html
  <a class="dropdown-item" href="{{ path('svc_totp_manage', {'reset': true}) }}">
    ...
  </a>
```

### Disable 2FA for other users (if you do not want to use the admin interface)

call the "svc_totp_oth_disable" path in your Twig template or controller and set the parameter id to the current user id <br/>
Example:

```html
{% if user.isTotpAuthenticationEnabled and is_granted("ROLE_ADMIN") %}
  <a href="{{ path('svc_totp_oth_disable', {'id' : user.id}) }}">{% trans %}Disable 2FA{% endtrans %}<a/>
{% endif %}
```

You can add the parameter reset=true, then 2FA will be disabled and the shared secret will be deleted (you will need a new secret/QR code and you will have to store it in the authenticator again).<br/>
Example:
```html
  <a class="dropdown-item" href="{{ path('svc_totp_manage', {'reset': true}) }}">
    ...
  </a>
```

### Clear trusted devices for current/all users (if you do not want to use the admin interface)

call the "svc_totp_cleartd" path in your Twig template or controller. <br/>
Set the "allUsers" parameter to true if you want to delete trusted devices for all users (requires ROLE_ADMIN), otherwise (default) only devices for the current user will be deleted.<br/>
Example:

```html
<a class="dropdown-item" href="{{ path('svc_totp_cleartd', {'allUsers' : true} ) }}">
  <i class="fa-solid fa-folder-minus"></i>
  {% trans %}Clear trusted devices{% endtrans %} ({% trans %}all{% endtrans %})
</a>
```

### Clear trusted devices for other users (if you do not want to use the admin interface)

call the "svc_totp_clear_oth_td" path in your Twig template or controller and set the parameter id to the current user id.<br/>
Example:

```html
{% if user.isTotpAuthenticationEnabled and is_granted("ROLE_ADMIN") %}
  <a href="{{ path('svc_totp_clear_oth_td', {'id' : user.id}) }}">{% trans %}Clear TD{% endtrans %}<a/>
{% endif %}
```

### Call an admin interface

list all user with the possibility to disable/reset 2FA, clear trusted devices and show infos about 2FA by user

Example:<br/>
```html
{% if is_granted("ROLE_ADMIN") %}
  <a href="{{ path('svc_totp_user_admin', {'id' : user.id}) }}">{% trans %}2FA User admin{% endtrans %}<a/>
{% endif %}
```

