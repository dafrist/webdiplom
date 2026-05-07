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
- `/admin/applications`
- `/admin/news`
- `/admin/programs`

Админ-раздел содержит dashboard со статистикой, список заявок с просмотром и сменой статуса, а также управление новостями и программами.
