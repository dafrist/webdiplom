# «Умники и Умницы» — две версии сайта

В репозитории сохранены два независимых варианта проекта:

- `php_xampp/` — исходная PHP/XAMPP версия сайта.
- `flask_app/` — новая Python Flask версия с SQLite.

## PHP/XAMPP запуск

1. Открыть XAMPP.
2. Запустить Apache и MySQL.
3. Положить проект в `htdocs`.
4. Импортировать `php_xampp/db.sql` в MySQL при первом запуске.
5. Открыть `http://localhost/php_xampp`.

PHP-версия сохранена отдельно и использует прежние PHP-файлы, маршруты, формы и админку.

## Flask запуск

```bat
cd flask_app
python -m venv venv
venv\Scripts\activate
pip install -r requirements.txt
python app.py
```

Открыть сайт: <http://127.0.0.1:5000>.

SQLite база `flask_app/database/app.db` создаётся автоматически при первом запуске `app.py` и не хранится в git.

Данные администратора Flask-версии по умолчанию:

- email: `admin@example.com`
- пароль: `admin123`

## Изображения

Бинарные изображения не добавляются в patch. Для локального запуска Flask-версии скопируйте существующие изображения командами:

```bat
xcopy php_xampp\assets\images flask_app\static\images /E /I /Y
```

или из старой папки проекта, если она ещё лежит рядом:

```bat
xcopy img flask_app\static\images /E /I /Y
```
