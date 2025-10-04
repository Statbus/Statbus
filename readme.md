# Statbus

It's Statbus.

## Need Help?

Feel free to open a [discussion](https://github.com/orgs/Statbus/discussions) or reach out on [Discord](https://discord.gg/37R9MkqG86).

## Running your own Statbus Instance

### Requirements

At a minimum, Statbus needs access to your Space Station 13 gameserver database.

### Docker

Statbus comes with its own dockerfile; this is the recommended way to deploy Statbus. The docker image is based on [FrankenPHP](https://frankenphp.dev/). There may or may not be a way to run this as part of a kubernetes cluster.

### Webserver

You can also run Statbus by itself. It will require PHP 8.4, along with the `pdo_mysql`, `gd`, `intl`, `zip`, `opcache` and `imagick` PHP extensions.

### Configuration

There are a few files Statbus requires, relative to the Statbus root directory:

`/.env.local` - Where you set all the various configuration options, including database connection information. See `/.env` for a list of options that can be configured.

`/servers.json` - Required in order to map server ports from your database data to server information.

`/config/packages/prod/statbus.yaml` - See the [Feature Flags](#Feature Flags) section below.

## Feature Flags

`config/packages/statbus.yaml` contains a list of Statbus features that should be enabled. By default, all features are enabled. If a feature is not listed in the configuration file, it is enabled. To override the list of enabled features, create `config/packages/prod/statbus.yaml` and override which features you would like to disable.

The following `config/packages/prod/statbus.yaml` example disables death listings, tickets, public bans, the library, and polls:

```yaml
parameters:
  statbus:
    deaths:
      enabled: false
    tickets:
      enabled: false
    bans:
      public: false
    library: false
    polls: false
```

The following features can be disabled:

- Authentication
  - Discord Authentication
  - OAuth Authentication
- BadgeR (badge rendering tool)
- Manifest (features that require the `manifest` table)
- Deaths
  - Death listing(s)
  - Heatmaps of where deaths occur
- Tickets
  - Public ticket links (requires external database support)
- TGDB
  - Feedback link setting
  - Allow list feature (requires external database support)
  - Known alts
- Bans
  - Public bans
- The library
- Poll results

Refer to `config/packages/statbus.yaml` for the most up to date listing of features.
