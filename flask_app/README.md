# Flask-версия сайта «Умники и Умницы»

Отдельная Python Flask копия сайта с тем же визуальным стилем, шаблонами Jinja2 и SQLite.

## Запуск

```bat
cd flask_app
python -m venv venv
venv\Scripts\activate
pip install -r requirements.txt
python app.py
```

Откройте <http://127.0.0.1:5000>.

При первом запуске автоматически создаётся `database/app.db`, таблицы, стартовые программы, новости и администратор.

## Администратор

- email: `admin@example.com`
- пароль: `admin123`

## Маршруты

- `/`
- `/about`
- `/contacts`
- `/gallery`
- `/programs`
- `/news`
- `/register`
- `/login`
- `/logout`
- `/profile`
- `/admin`

Админ-раздел также содержит управление новостями и программами.
