# Instalação

## 1. Clonar o Repositório

```bash
git clone https://github.com/victorsato/financial-wallet.git
```

## 2. Instalando dependências do Composer

```bash
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php84-composer:latest \
    composer install --ignore-platform-reqs
```

Referência Laravel Sail: 
> https://laravel.com/docs/11.x/sail

## 3. Iniciar o Sail

```bash
./vendor/bin/sail up
```

## 4. Executar Migrações e Seeds

```bash
sail artisan migrate
```

```bash
sail artisan db:seed
```

## 5. Acessar o Projeto

Com os contêineres em execução, seu projeto Laravel estará acessível no seu navegador em 
> http://localhost.