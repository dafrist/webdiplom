from __future__ import annotations

import sqlite3
from datetime import datetime
from functools import wraps
from pathlib import Path

from flask import Flask, flash, g, jsonify, redirect, render_template, request, session, url_for
from werkzeug.security import check_password_hash, generate_password_hash

BASE_DIR = Path(__file__).resolve().parent
DATABASE_DIR = BASE_DIR / "database"
DATABASE_PATH = DATABASE_DIR / "app.db"

app = Flask(__name__)
app.config["SECRET_KEY"] = "change-this-secret-key-in-production"

PROGRAM_SEEDS = [
    ("Развивайка (3,5 часа)", "razvivaika-4plus", 4, 7, "Комплексное занятие: развитие речи, мышления, моторики и навыков общения.", "Развивающие игры, упражнения на внимание и память, логика, мелкая моторика, речевые упражнения и творческие паузы.", 1),
    ("Дошколята", "doshkolyata-5plus", 5, 7, "Математика, логика, внимание, подготовка руки к письму и навыки для школы.", "Программа формирует базу для уверенного старта в школе: счёт, графомоторика, развитие речи и усидчивости.", 2),
    ("Чтение", "reading", 5, 9, "Обучение чтению через понятные шаги: слоги, слова, понимание текста.", "Занятия помогают перейти от букв к словам, развивают технику чтения и понимание прочитанного.", 3),
    ("Каллиграфия", "calligraphy", 6, 10, "Красивый почерк и уверенное письмо.", "Ребёнок учится правильно держать ручку, отрабатывает элементы букв, соединения и аккуратность.", 4),
    ("Логопед", "speech-therapist", 4, 10, "Индивидуальные логопедические занятия.", "Постановка звуков, развитие словаря, связной речи и фонематического слуха.", 5),
    ("Английский язык", "english", 5, 10, "Первое знакомство с английским в игровой форме.", "Слова, фразы, песни, игры и простое общение без страха ошибки.", 6),
    ("Рисование", "drawing", 4, 10, "Творческие занятия для развития фантазии и вкуса.", "Работаем с цветом, формой, разными материалами и поддерживаем индивидуальный стиль ребёнка.", 7),
    ("Подготовка к школе", "school-prep", 5, 7, "Комплексная подготовка к 1 классу.", "Чтение, математика, логика, письмо, внимание и уверенность перед школьной нагрузкой.", 8),
]

NEWS_SEEDS = [
    ("Открыт набор в мини-группы", "mini-groups", "Запускаем новые группы для детей 4–7 лет.", "Группы небольшие, поэтому педагог уделяет внимание каждому ребёнку."),
    ("Интенсивная подготовка к школе", "intensive-school-prep", "Стартует курс интенсивной подготовки к 1 классу.", "Курс включает развитие внимания и памяти, чтение, математику и логику."),
]


def get_db() -> sqlite3.Connection:
    if "db" not in g:
        DATABASE_DIR.mkdir(parents=True, exist_ok=True)
        g.db = sqlite3.connect(DATABASE_PATH)
        g.db.row_factory = sqlite3.Row
    return g.db


@app.teardown_appcontext
def close_db(error: Exception | None = None) -> None:
    db = g.pop("db", None)
    if db is not None:
        db.close()


def init_db() -> None:
    DATABASE_DIR.mkdir(parents=True, exist_ok=True)
    db = sqlite3.connect(DATABASE_PATH)
    db.executescript(
        """
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            email TEXT NOT NULL UNIQUE,
            password_hash TEXT NOT NULL,
            role TEXT NOT NULL DEFAULT 'parent',
            created_at TEXT DEFAULT CURRENT_TIMESTAMP
        );
        CREATE TABLE IF NOT EXISTS programs (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT NOT NULL,
            slug TEXT NOT NULL UNIQUE,
            age_from INTEGER,
            age_to INTEGER,
            short_description TEXT,
            full_description TEXT,
            is_active INTEGER NOT NULL DEFAULT 1,
            sort_order INTEGER DEFAULT 0,
            created_at TEXT DEFAULT CURRENT_TIMESTAMP
        );
        CREATE TABLE IF NOT EXISTS news (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            created_at TEXT DEFAULT CURRENT_TIMESTAMP,
            title TEXT NOT NULL,
            slug TEXT NOT NULL UNIQUE,
            teaser TEXT,
            body TEXT,
            is_published INTEGER NOT NULL DEFAULT 1
        );
        CREATE TABLE IF NOT EXISTS applications (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER,
            parent_name TEXT,
            phone TEXT NOT NULL,
            comment TEXT,
            status TEXT NOT NULL DEFAULT 'new',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id)
        );
        """
    )
    db.execute(
        "INSERT OR IGNORE INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)",
        ("Администратор", "admin@example.com", generate_password_hash("admin123"), "admin"),
    )
    for item in PROGRAM_SEEDS:
        db.execute(
            """INSERT OR IGNORE INTO programs
            (title, slug, age_from, age_to, short_description, full_description, sort_order)
            VALUES (?, ?, ?, ?, ?, ?, ?)""",
            item,
        )
    for item in NEWS_SEEDS:
        db.execute(
            "INSERT OR IGNORE INTO news (title, slug, teaser, body) VALUES (?, ?, ?, ?)",
            item,
        )
    ensure_application_schema(db)
    db.commit()
    db.close()


def ensure_application_schema(db: sqlite3.Connection) -> None:
    db.execute(
        """
        CREATE TABLE IF NOT EXISTS applications (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER,
            parent_name TEXT,
            phone TEXT NOT NULL,
            comment TEXT,
            status TEXT NOT NULL DEFAULT 'new',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id)
        );
        """
    )
    columns = {row[1] for row in db.execute("PRAGMA table_info(applications)").fetchall()}
    if "user_id" not in columns:
        db.execute("ALTER TABLE applications ADD COLUMN user_id INTEGER")
    if "status" not in columns:
        db.execute("ALTER TABLE applications ADD COLUMN status TEXT NOT NULL DEFAULT 'new'")


APPLICATION_STATUSES = {
    "new": "Новая",
    "processing": "В работе",
    "completed": "Завершена",
}


def status_label(status: str | None) -> str:
    return APPLICATION_STATUSES.get(status or "new", "Новая")


@app.template_filter("status_label")
def status_label_filter(status: str | None) -> str:
    return status_label(status)


def current_user():
    user_id = session.get("user_id")
    if not user_id:
        return None
    return get_db().execute("SELECT * FROM users WHERE id = ?", (user_id,)).fetchone()


@app.context_processor
def inject_globals():
    return {"current_user": current_user()}


def login_required(view):
    @wraps(view)
    def wrapped_view(*args, **kwargs):
        if current_user() is None:
            flash("Войдите в личный кабинет.", "error")
            return redirect(url_for("login"))
        return view(*args, **kwargs)
    return wrapped_view


def admin_required(view):
    @wraps(view)
    def wrapped_view(*args, **kwargs):
        user = current_user()
        if user is None:
            flash("Нужен доступ администратора.", "error")
            return redirect(url_for("login"))
        if user["role"] != "admin":
            flash("Админ-раздел доступен только администратору.", "error")
            return redirect(url_for("profile"))
        return view(*args, **kwargs)
    return wrapped_view


def program_icon(slug: str) -> str:
    return {
        "razvivaika-4plus": "ui-icon-chart",
        "doshkolyata-5plus": "ui-icon-users",
        "reading": "ui-icon-book",
        "calligraphy": "ui-icon-pen",
        "speech-therapist": "ui-icon-message",
        "english": "ui-icon-book",
        "drawing": "ui-icon-palette",
        "school-prep": "ui-icon-star",
    }.get(slug, "ui-icon-book")


@app.template_filter("program_icon")
def program_icon_filter(slug: str) -> str:
    return program_icon(slug)


def format_date(value: str | None) -> str:
    if not value:
        return "Дата уточняется"
    text = str(value)
    for fmt in ("%Y-%m-%d %H:%M:%S", "%Y-%m-%d"):
        try:
            parsed = datetime.strptime(text[:19] if fmt.endswith("%S") else text[:10], fmt)
            return parsed.strftime("%d.%m.%Y")
        except ValueError:
            continue
    return text[:10]


@app.template_filter("format_date")
def format_date_filter(value: str | None) -> str:
    return format_date(value)


@app.route("/")
def index():
    programs = get_db().execute("SELECT * FROM programs WHERE is_active = 1 ORDER BY sort_order, id LIMIT 4").fetchall()
    news = get_db().execute("SELECT * FROM news WHERE is_published = 1 ORDER BY created_at DESC, id DESC LIMIT 3").fetchall()
    return render_template("index.html", active="index", programs=programs, news=news)


@app.route("/about")
def about():
    return render_template("about.html", active="about")


@app.route("/contacts")
def contacts():
    return render_template("contacts.html", active="contacts")


@app.post("/application")
def application():
    parent_name = request.form.get("parent_name", "").strip()
    phone = request.form.get("phone", "").strip()
    comment = request.form.get("comment", "").strip()
    digits = "".join(ch for ch in phone if ch.isdigit())
    if digits.startswith("8"):
        digits = "7" + digits[1:]
    elif digits.startswith("9"):
        digits = "7" + digits
    if len(digits) != 11 or not digits.startswith("7"):
        message = "Укажите телефон в формате +7 (999) 999-99-99."
        if request.headers.get("X-Requested-With") == "XMLHttpRequest":
            return jsonify(ok=False, error=message), 400
        flash(message, "error")
        return redirect(url_for("contacts"))

    db = get_db()
    ensure_application_schema(db)
    user = current_user()
    db.execute(
        "INSERT INTO applications (user_id, parent_name, phone, comment, status) VALUES (?, ?, ?, ?, ?)",
        (user["id"] if user else None, parent_name, phone, comment, "new"),
    )
    db.commit()
    message = "Заявка отправлена. Администратор свяжется с вами в ближайшее время."
    if request.headers.get("X-Requested-With") == "XMLHttpRequest":
        return jsonify(ok=True, message=message)
    flash(message, "success")
    return redirect(url_for("contacts"))


@app.route("/gallery")
def gallery():
    return render_template("gallery.html", active="gallery")


@app.route("/programs")
def programs():
    rows = get_db().execute("SELECT * FROM programs WHERE is_active = 1 ORDER BY sort_order, id").fetchall()
    return render_template("programs.html", active="programs", programs=rows)


@app.route("/news")
def news():
    rows = get_db().execute("SELECT * FROM news WHERE is_published = 1 ORDER BY created_at DESC, id DESC").fetchall()
    return render_template("news.html", active="news", news_list=rows)


@app.route("/register", methods=["GET", "POST"])
def register():
    if request.method == "POST":
        name = request.form.get("name", "").strip()
        email = request.form.get("email", "").strip().lower()
        password = request.form.get("password", "")
        is_ajax = request.headers.get("X-Requested-With") == "XMLHttpRequest"
        if not name or not email or len(password) < 6:
            message = "Заполните имя, email и пароль от 6 символов."
            if is_ajax:
                return jsonify(ok=False, error=message), 400
            flash(message, "error")
        else:
            try:
                db = get_db()
                db.execute("INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)", (name, email, generate_password_hash(password)))
                db.commit()
                message = "Регистрация завершена. Теперь вы можете войти в личный кабинет."
                if is_ajax:
                    return jsonify(ok=True, message=message)
                flash(message, "success")
                return redirect(url_for("login"))
            except sqlite3.IntegrityError:
                message = "Пользователь с таким email уже существует."
                if is_ajax:
                    return jsonify(ok=False, error=message), 409
                flash(message, "error")
    return render_template("register.html", active="profile")


@app.route("/login", methods=["GET", "POST"])
def login():
    if request.method == "POST":
        email = request.form.get("email", "").strip().lower()
        password = request.form.get("password", "")
        is_ajax = request.headers.get("X-Requested-With") == "XMLHttpRequest"
        if not email or not password:
            message = "Заполните e-mail и пароль."
            if is_ajax:
                return jsonify(ok=False, error=message), 400
            flash(message, "error")
            return render_template("login.html", active="profile")
        user = get_db().execute("SELECT * FROM users WHERE email = ?", (email,)).fetchone()
        if user and check_password_hash(user["password_hash"], password):
            session.clear()
            session["user_id"] = user["id"]
            next_url = url_for("admin" if user["role"] == "admin" else "profile")
            if is_ajax:
                return jsonify(ok=True, redirect=next_url)
            flash("Вы вошли в личный кабинет.", "success")
            return redirect(next_url)
        message = "Неверный e-mail или пароль."
        if is_ajax:
            return jsonify(ok=False, error=message), 401
        flash(message, "error")
    return render_template("login.html", active="profile")


@app.route("/logout")
def logout():
    session.clear()
    flash("Вы вышли из аккаунта.", "success")
    return redirect(url_for("index"))


@app.route("/profile")
@login_required
def profile():
    db = get_db()
    ensure_application_schema(db)
    user = current_user()
    applications = db.execute(
        "SELECT * FROM applications WHERE user_id = ? ORDER BY created_at DESC, id DESC",
        (user["id"],),
    ).fetchall()
    recommended_programs = [
        {
            "title": "Подготовка к школе",
            "short_description": "Чтение, математика, логика, письмо и уверенность перед первым классом.",
            "age_from": 5,
            "age_to": 7,
        },
        {
            "title": "Логопед",
            "short_description": "Индивидуальная работа со звуками, словарём, связной речью и фонематическим слухом.",
            "age_from": 4,
            "age_to": 10,
        },
        {
            "title": "Развитие речи",
            "short_description": "Занятия для расширения словаря, грамотной речи и уверенного общения.",
            "age_from": 4,
            "age_to": 8,
        },
        {
            "title": "Каллиграфия",
            "short_description": "Аккуратный почерк, правильная посадка и спокойная подготовка руки к письму.",
            "age_from": 6,
            "age_to": 10,
        },
    ]
    return render_template(
        "profile.html",
        active="profile",
        applications=applications,
        recommended_programs=recommended_programs,
    )


@app.route("/admin")
@admin_required
def admin():
    db = get_db()
    ensure_application_schema(db)
    applications_count = db.execute("SELECT COUNT(*) AS c FROM applications").fetchone()["c"]
    new_applications_count = db.execute("SELECT COUNT(*) AS c FROM applications WHERE status = 'new'").fetchone()["c"]
    programs_count = db.execute("SELECT COUNT(*) AS c FROM programs").fetchone()["c"]
    news_count = db.execute("SELECT COUNT(*) AS c FROM news").fetchone()["c"]
    users_count = db.execute("SELECT COUNT(*) AS c FROM users").fetchone()["c"]
    return render_template(
        "admin/index.html",
        active="profile",
        applications_count=applications_count,
        new_applications_count=new_applications_count,
        programs_count=programs_count,
        news_count=news_count,
        users_count=users_count,
    )


@app.route("/admin/applications")
@admin_required
def admin_applications():
    db = get_db()
    ensure_application_schema(db)
    rows = db.execute(
        """SELECT applications.*, users.email AS user_email
        FROM applications
        LEFT JOIN users ON users.id = applications.user_id
        ORDER BY applications.created_at DESC, applications.id DESC"""
    ).fetchall()
    return render_template(
        "admin/applications_list.html",
        active="profile",
        rows=rows,
        statuses=APPLICATION_STATUSES,
    )


@app.route("/admin/applications/<int:item_id>", methods=["GET", "POST"])
@admin_required
def admin_application_detail(item_id: int):
    db = get_db()
    ensure_application_schema(db)
    if request.method == "POST":
        status = request.form.get("status", "new")
        if status not in APPLICATION_STATUSES:
            flash("Выберите корректный статус заявки.", "error")
        else:
            db.execute("UPDATE applications SET status = ? WHERE id = ?", (status, item_id))
            db.commit()
            flash("Статус заявки обновлён.", "success")
            return redirect(url_for("admin_application_detail", item_id=item_id))
    item = db.execute(
        """SELECT applications.*, users.name AS user_name, users.email AS user_email
        FROM applications
        LEFT JOIN users ON users.id = applications.user_id
        WHERE applications.id = ?""",
        (item_id,),
    ).fetchone()
    if item is None:
        flash("Заявка не найдена.", "error")
        return redirect(url_for("admin_applications"))
    return render_template(
        "admin/application_detail.html",
        active="profile",
        item=item,
        statuses=APPLICATION_STATUSES,
    )


@app.route("/admin/news")
@admin_required
def admin_news():
    rows = get_db().execute("SELECT * FROM news ORDER BY created_at DESC, id DESC").fetchall()
    return render_template("admin/news_list.html", active="profile", rows=rows)


@app.route("/admin/news/new", methods=["GET", "POST"])
@app.route("/admin/news/<int:item_id>/edit", methods=["GET", "POST"])
@admin_required
def admin_news_edit(item_id: int | None = None):
    db = get_db()
    item = db.execute("SELECT * FROM news WHERE id = ?", (item_id,)).fetchone() if item_id else None
    if request.method == "POST":
        data = (
            request.form.get("title", "").strip(), request.form.get("slug", "").strip(),
            request.form.get("teaser", "").strip(), request.form.get("body", "").strip(),
            1 if request.form.get("is_published") else 0,
        )
        if not data[0] or not data[1]:
            flash("Укажите заголовок и slug.", "error")
        elif item:
            db.execute("UPDATE news SET title=?, slug=?, teaser=?, body=?, is_published=? WHERE id=?", (*data, item_id))
            db.commit(); return redirect(url_for("admin_news"))
        else:
            db.execute("INSERT INTO news (title, slug, teaser, body, is_published) VALUES (?, ?, ?, ?, ?)", data)
            db.commit(); return redirect(url_for("admin_news"))
    return render_template("admin/news_form.html", active="profile", item=item)


@app.post("/admin/news/<int:item_id>/delete")
@admin_required
def admin_news_delete(item_id: int):
    db = get_db(); db.execute("DELETE FROM news WHERE id = ?", (item_id,)); db.commit()
    return redirect(url_for("admin_news"))


@app.route("/admin/programs")
@admin_required
def admin_programs():
    rows = get_db().execute("SELECT * FROM programs ORDER BY sort_order, id").fetchall()
    return render_template("admin/programs_list.html", active="profile", rows=rows)


@app.route("/admin/programs/new", methods=["GET", "POST"])
@app.route("/admin/programs/<int:item_id>/edit", methods=["GET", "POST"])
@admin_required
def admin_programs_edit(item_id: int | None = None):
    db = get_db()
    item = db.execute("SELECT * FROM programs WHERE id = ?", (item_id,)).fetchone() if item_id else None
    if request.method == "POST":
        data = (
            request.form.get("title", "").strip(), request.form.get("slug", "").strip(),
            request.form.get("age_from") or None, request.form.get("age_to") or None,
            request.form.get("short_description", "").strip(), request.form.get("full_description", "").strip(),
            1 if request.form.get("is_active") else 0, request.form.get("sort_order") or 0,
        )
        if not data[0] or not data[1]:
            flash("Укажите название и slug.", "error")
        elif item:
            db.execute("""UPDATE programs SET title=?, slug=?, age_from=?, age_to=?, short_description=?,
                full_description=?, is_active=?, sort_order=? WHERE id=?""", (*data, item_id))
            db.commit(); return redirect(url_for("admin_programs"))
        else:
            db.execute("""INSERT INTO programs (title, slug, age_from, age_to, short_description, full_description, is_active, sort_order)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)""", data)
            db.commit(); return redirect(url_for("admin_programs"))
    return render_template("admin/programs_form.html", active="profile", item=item)


@app.post("/admin/programs/<int:item_id>/delete")
@admin_required
def admin_programs_delete(item_id: int):
    db = get_db(); db.execute("DELETE FROM programs WHERE id = ?", (item_id,)); db.commit()
    return redirect(url_for("admin_programs"))


if __name__ == "__main__":
    init_db()
    app.run(debug=True)
