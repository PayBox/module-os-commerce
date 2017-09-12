# module-os-commerce

Подключение модуля PayBox

Регистрация
1. Оставьте заявку на сайте [PayBox](https://www.paybox.money);
2. Заполните все поля и следуйте инструкциям.

Установка модуля
1. Скачайте модуль для OS Commerce v3.0.2  из Личного кабинета / Настройки мерчанта;
2. Распакуйте архив в любое удобное Вам место;
3. Cкопировать файлы по адресу относительно корня сайта:

./osCommerce/OM/Core/Site/Admin/Module/Payment/platron.php
./osCommerce/OM/Core/Site/Shop/Module/Payment/platron.php
./osCommerce/OM/Core/Site/Shop/Languages/en_US/modules/payment/platron.xml
./osCommerce/OM/Core/Site/Shop/Languages/ru_RU/modules/payment/platron.xml
./osCommerce/OM/Custom/PlatronResponcer.php
./osCommerce/OM/Custom/PlatronSignature.php
./platron_check.php
./platron_result.php
./platron_refund.php

Настройка модуля
1. После этого в разделе Administration - Modules - Payment нажимаем справа на кнопку "+ install module" выбираем из списка "Платежный гейт Platron" и нажимаем на кнопку справа "install module"
2. в разделе Administration - Modules - Payment выбираем уже установленный модуль "Платежный гейт Platron" и нажимаем  "Edit" задаем конфигурацию модуля:
3. Enable Platron Payment Gate - "true|false" - Platron становится доступным|недоступным для пользователя в списке модулей оплаты:
  1. Идентификатор магазина в системе PayBox
  2. Кодовое слово
  3. Action URL to Platron - Ссылка на точку входа в PayBox
  4. Transaction lifetime - Время в течение которого может выть принята оплата
  5. Check URL - Ссылка на которую приходят check-запросы(в ней надо поменять sitename.ru на адрес своего сайта)
  6. Result URL - Ссылка на которую приходят success и fail-запросы(в ней надо поменять sitename.ru на адрес своего сайта)
  7. Refund URL - Ссылка на которую приходят refund-запросы(в ней надо поменять sitename.ru на адрес своего сайта)
  8. Success URL - Ссылка на которую переходит пользователь, в случае если покупка принята в обработку(в ней надо поменять sitename.ru на адрес своего сайта)
  9. Failure URL - Ссылка на которую переходит пользователь, в случае если покупка не принята в обработку(в ней надо поменять sitename.ru на адрес своего сайта)
  10. Выставите статусы в соответствием с тем в какие должна переводится покупка
