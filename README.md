# sfGemiusTrafficPlugin plugin #

Easily add [Gemius Traffic](http://www.gemius.pl/) tracking code to your presentation layer.

## Installation ##

  * Install plugin:

        $symfony plugin:install sfGemiusTrafficPlugin

  * Add the `sfGemiusTrafficFilter` to your filter chain:

        rendering: ~
        security:  ~

        # insert your own filters here
        gemius_traffic:
          class: sfGemiusTrafficFilter

        cache:     ~
        common:    ~
        execution: ~

## Configure ##

  * Enable plugin in your application's `app.yml` file:

        all:
          gemius_traffic_plugin:
            enabled:   true

  * Optionally set the position of Gemius Traffic code:

        all:
          gemius_traffic_plugin:
            insertion: <?php echo sfGemiusTrafficTracker::POSITION_HEAD ?>

    Note: POSITION_HEAD is the default position.

  * set the action identifier in `module.yml`:

        all:
          index:
            gemius_traffic:
              params:
                identifier: 4re4r.4s4d55d2r5ff5.g1

    or set the module identifier:

        all:
          gemius_traffic:
            params:
              identifier: 4re4r.4s4d55d2r5ff5.g1

## Note ##

Plugin code is fork of sfGoogleAnalyticsPlugin.
