## Cache Bundling of CSS, JS along with Sourcemap:

Cache Bundling helps us to perform several levels of optimization, which includes reducing file size, reduce in the number of network calls, deferring of js, etc.

In this, I have also focused on adding Sourcemap which will help to identify errors on [sentry](https://sentry.io/) easily.

As a part of page speed optimization, have used two CSS file one which will contain the first fold of the page and other will have all the CSS which is required for the page. Just to make sure our end-user experience is good.

Below is the list of features that are used to optimzied page load and proper error listing on [sentry](https://sentry.io/).

* First Fold CSS
* Deferring JS
* Backend Option for cache bundling
* Delete caching directory
* Working Logic around cache bundling of CSS and JS along with sourcemap

<br>
<br>

**First Fold CSS**
```
  add_onload_attribute_link_tag()
```

**Deferring JS**
```
  add_defer_attribute_script_tag()
```

**Backend Option for cache bundling**
```
  register_sub_menu()
  submenu_page_callback()
```

**Delete caching directory**
```
  deleteDir()
```

**Working Logic around cache bundling of CSS and JS along with sourcemap**
```
  cache_bundling_functionality()
```
<br>

Above all this functionality I have also added an option to exclude cache bundling based on pages, you just need to add the page URL excluding domain name example: /homepage separated by a comma for multiple page URL.
<br>
<br>
<br>
<br>

## Note: 
For alerting around my cache bundling I have preferred [Slack Notifcation](https://api.slack.com/messaging/webhooks).
And for JS Soucemap integration related alerts I preferred [sentry](https://sentry.io/)


<br>
<br>
**Happy Coding :)**