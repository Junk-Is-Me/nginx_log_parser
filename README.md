# Документация по проекту nginx_log_parser

## О проекте

Парсер логов реализован в файле `src/commands/LogController.php`.

Лог-файл для удобства деплоя лежит в `src/access.log`.

Для парсинга User-Agent используется библиотека:  
https://github.com/donatj/PhpUserAgent

---

## Основные методы

- **actionParseLog(string $filePath)**  
  Парсит лог-файл построчно, извлекает данные (IP, дата, URL, user agent), определяет ОС, архитектуру и браузер.  
  Сохраняет данные батчами в таблицу `log`.

- **parseLogFile(string $filePath)**  
  Генератор, построчно читает файл и возвращает совпадения с регулярным выражением для формата access.log (Nginx combined).

- **saveBatch(array $batchData)**  
  Сохраняет массив данных батчем через `batchInsert` в таблицу `log`. Использует транзакцию.

---

## Производительность

- Парсинг всего файла (121155 строк) работает с использованием около 128 МБ оперативной памяти.  
- Проверить лимит памяти можно командой:  
  ```bash
  docker exec -it php_yii2 php -i | grep memory_limit
