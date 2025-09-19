# Instalação

## 1. Clonar o Repositório

```bash
git clone https://github.com/victorsato/financial-wallet.git
```

## 2. Adicionar o Laravel Sail

```bash
composer require laravel/sail --dev
```

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