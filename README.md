# fake-fortee for phpcon2023 talk "SymfonyとDoctrineで始める安全なモジュラモノリス"

## how to run tests

```shell
composer install
symfony console doctrine:schema:update --force --env=test
vendor/bin/phpunit
```

## how to see application working

Install Symfony command first. The instruction is here: https://symfony.com/download#step-1-install-symfony-cli

```shell
composer install
docker compose up -d
symfony console doctrine:schema:update --force
symfony console app:demo
symfony server:start -d
```

Visit http://127.0.0.1:8000 in your browser.
