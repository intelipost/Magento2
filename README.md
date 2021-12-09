# Intelipost Shipping

Nova versão do módulo da Intelipost para Magento 2.3+

Para o funcionamento desse novo módulo, será necessário remover os módulos antigos da Intelipost.

```
bin/magento module:disable Intelipost_Quote
bin/magento module:disable Intelipost_Push
bin/magento module:disable Intelipost_Tracking
bin/magento module:disable Intelipost_Basic
```
Depois, será preciso fazer a instalação do novo módulo

**Instalação Manual**  
Download o arquivo e coloque na pasta
```
app/code/Intelipost/Shipping
```

Instalação Via Composer  
**EM BREVE**

Depois rodar os comandos de instalação

```
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy pt_BR en_US
```

