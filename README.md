# Web Shop API

## Description

Assessment project for software developer job position at Factory

## Requirements

* [PHP 8.2](https://php.net/releases/8_2_0.php) or greater

> To run this project you must have [Docker](https://docs.docker.com/engine) and [Docker Compose](https://docs.docker.com/compose) installed

## Installation
### Step 1

Replace __UID__ and __GID__ values in .env file with `id -u` and `id -g` output from the console if needed, respectively ( usually 1000:1000 ).

### Step 2

Cd into the project directory and run `make all` command to build docker images, install dependencies and run migrations

```bash
make all
```

### Step 3

Run migrations

```bash
make migrate
```
