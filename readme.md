# Statbus

It's Statbus.

## Need Help?

Feel free to open a [discussion](https://github.com/orgs/Statbus/discussions) or reach out on [Discord](https://discord.gg/37R9MkqG86).

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
