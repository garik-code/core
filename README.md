# Kitrix/Core

## Что такое Kitrix

Kitrix - это программный комплекс позволяющий быстро и удобно реализовать плагины под Битрикс, используя популярную методология MVC. Kitrix это “обертка” над системой управления Битрикс, а не модуль подключаемый через стандартный интерфейс cms. Так как Kitrix работает над cms, он легко может манипулировать ядром Битрикс на лету, используя официальные хуки.

## Kitrix - это менеджер модулей

На Kitrix вы можете с легкостью (в отличии от модулей Bitrix):

- создать свой плагин (psr-4 autoloading, composer)
- добавить свои админ страницы (mvc)
- управлять роутингом на уровне плагина (wildcard, default params, etc..)
- Kitrix автоматически создаст для ваших страниц пункты в админ. меню. По желанию вы можете поменять им название и установить иконку из Font Awesome
- вы можете использовать API других Kitrix плагинов в своих целях, к примеру с помощью плагина Kitrix\Config можно за минуту вынести все настройки вашего плагина в админ панель (все настройки Kitrix плагинов располагаются в одном месте, централизованно в отличии от хаоса который творится в битриксе)
- Kitrix позволяет вам добавлять свои ассеты (css,js) достаточно положить их в папку public (внутри вашего плагина)

## Модульность

сам kitrix также является модульным проектом, его функционал разбит на части и может быть использован частично по необходимости. На данный момент доступны следующие модули (и документация к ним):

- [Конфигуратор] https://github.com/kitrix-org/config

## Kitrix - это Open Source проект (MIT).

Мы предлагаем всем разработчикам которым надоело кусать кактус, участвовать в разработке Kitrix. В наших силах сделать Битрикс удобной и современной платформой для разработки проектов.

## Status

v 0.1.3
В разработке, используйте в production только если уверены в себе и знаете что делаете :)
До версии 1.0 API может поменятся каким угодно образом, документации по этой причине на данный момент нету.
