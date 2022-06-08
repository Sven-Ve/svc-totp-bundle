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

### Disable 2FA for other users

call the "svc_totp_oth_disable" path in your Twig template or controller and set the parameter id to the current user id <br/>

Example:

```html
{% if user.isTotpAuthenticationEnabled and is_granted("ROLE_ADMIN") %}
  <a href="{{ path('svc_totp_oth_disable', {'id' : user.id}) }}">{% trans %}Disable 2FA{% endtrans %}<a/>
{% endif %}
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

### Clear trusted devices for other users

call the "svc_totp_clear_oth_td" path in your Twig template or controller and set the parameter id to the current user id.

Example:

```html
{% if user.isTotpAuthenticationEnabled and is_granted("ROLE_ADMIN") %}
  <a href="{{ path('svc_totp_clear_oth_td', {'id' : user.id}) }}">{% trans %}Clear TD{% endtrans %}<a/>
{% endif %}
```



