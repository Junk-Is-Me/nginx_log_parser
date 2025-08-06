# Документация по проекту nginx_log_parser

## О проекте

Парсер логов реализован в файле src/commands/LogController.php.

Лог-файл для удобства деплоя лежит в src/access.log.

Для парсинга User-Agent используется библиотека:  
https://github.com/donatj/PhpUserAgent

---

## Основные методы

- actionParseLog(string $filePath)  
  Парсит лог-файл построчно, извлекает данные (IP, дата, URL, user agent), определяет ОС, архитектуру и браузер.  
  Сохраняет данные батчами в таблицу log.

- parseLogFile(string $filePath)  
  Генератор, построчно читает файл и возвращает совпадения с регулярным выражением для формата access.log (Nginx combined).

- saveBatch(array $batchData)  
  Сохраняет массив данных батчем через batchInsert в таблицу log. Использует транзакцию.

---

## Производительность

- Парсинг всего файла (121155 строк) работает с использованием около 128 МБ оперативной памяти.  
- Проверить лимит памяти можно командой:  
  docker exec -it php_yii2 php -i | grep memory_limit

---

## Работа с базой данных

- Для удобства просмотра БД и таблиц в Docker развернут phpMyAdmin.  
- phpMyAdmin доступен по адресу: http://localhost:8081  
- Логин и пароль для доступа: root / root  
- База данных создаётся автоматически через docker-compose.yml.

---

## Работа приложения

- Приложение работает на порту: http://localhost:8080  
Графики и таблицы показывает на странице - http://localhost:8080/site/statistic
---

## Разворачивание через Docker

1. Клонировать репозиторий:  
   git clone https://github.com/Junk-Is-Me/nginx_log_parser.git

2. Перейти в директорию и собрать контейнеры:  
   docker compose up -d --build

3. Задать права:  
   sudo chown -R 33:33 ./src

4. Войти в контейнер:  
   docker exec -it php_yii2 bash

5. Установить зависимости:  
   composer install

6. Выполнить миграции:  
   docker exec -it php_yii2 php yii migrate

7. Запустить парсинг логов:  
   docker exec -it php_yii2 php yii log/parse-log access.log

