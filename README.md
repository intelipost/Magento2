# Magento2
Novo aplicativo Magento


#Instalação
Para a utilização desses módulos, é recomendável remover os módulos antigos da cotação da Intelipost.
```
composer remove intelipost/magento2-push
composer remove intelipost/magento2-quote
composer remove intelipost/magento2-tracking
```
Após remover os módulos antigos, só instalar o módulo novo (em breve estará no packagist)

```
composer config repositories.intelipost-shipping git https://github.com/intelipost/Magento2.git
composer require intelipost/magento2:dev-master
php bin/magento setup:static-content:deploy pt_BR
php bin/magento setup:upgrade
php bin/magento setup:di:compile
```


