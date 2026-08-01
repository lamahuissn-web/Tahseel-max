# Tahseel Collector Android Wrapper

This is a minimal Trusted Web Activity wrapper for:

`https://tahseel.meganet.live/ar/admin/mobile-view`

It contains no Tahseel business logic, credentials, customer data, or offline payment support. The installed app uses Chrome's trusted engine and the existing Laravel session.

## Release signing

Release builds require these environment variables:

- `TAHSEEL_KEYSTORE_FILE`
- `TAHSEEL_KEYSTORE_PASSWORD`
- `TAHSEEL_KEY_ALIAS`
- `TAHSEEL_KEY_PASSWORD`

The signing key must never be committed. Every future update must use the same package ID and signing key.

## Build

```bash
gradle --no-daemon :app:assembleRelease
```

GitHub Actions performs the reproducible signed build and uploads the APK artifact.
