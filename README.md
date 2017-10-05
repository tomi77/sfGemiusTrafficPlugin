# sfGemiusTrafficPlugin plugin

Easily add [Gemius Traffic](http://www.gemius.pl/) tracking code to your presentation layer.

[![StyleCI](https://styleci.io/repos/49592179/shield?style=plastic&branch=master)](https://styleci.io/repos/49592179)

## Installation

  * Install plugin:

    ~~~sh
    $ composer require tomasz-rup/sf-gemius-traffic-plugin
    ~~~

  * Add the `sfGemiusTrafficFilter` to your filter chain:

    ~~~yaml
    rendering: ~
    security:  ~

    # insert your own filters here
    gemius_traffic:
      class: sfGemiusTrafficFilter

    cache:     ~
    common:    ~
    execution: ~
    ~~~

## Configure

  * Enable plugin in your application's `app.yml` file:

    ~~~yaml
    all:
      gemius_traffic_plugin:
        enabled: true
    ~~~

  * Optionally set the position of Gemius Traffic code:

    ~~~yaml
    all:
      gemius_traffic_plugin:
        insertion: <?php echo sfGemiusTrafficTracker::POSITION_HEAD ?>
    ~~~

    Note: `POSITION_HEAD` is the default position.

  * set the action identifier in `module.yml`:

    ~~~yaml
    all:
      index:
        gemius_traffic:
          params:
            identifier: 4re4r.4s4d55d2r5ff5.g1
    ~~~

    or set the module identifier:

    ~~~yaml
    all:
      gemius_traffic:
        params:
          identifier: 4re4r.4s4d55d2r5ff5.g1
    ~~~

## Insertion positions

* `POSITION_HEAD`

  Insert in `<head>`

* `POSITION_BODY_TOP`

  Insert as first element in `<body>`

* `POSITION_BODY_BOTTOM`

Insert as last element in `<body>`

## Note

Plugin code is fork of `sfGoogleAnalyticsPlugin`.
