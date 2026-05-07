<?php
// ---------- НАСТРОЙКИ ----------
// Укажите email администратора центра
$ADMIN_EMAIL = "info@example.com"; // TODO: заменить на реальный адрес
$PROJECT_NAME = "Умники и Умницы";
// -------------------------------

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /');
    exit;
}

mb_internal_encoding("UTF-8");

function field($name) {
    return isset($_POST[$name]) ? trim((string)$_POST[$name]) : '';
}

function field_array($name) {
    return isset($_POST[$name]) && is_array($_POST[$name]) ? $_POST[$name] : [];
}

$form_type = field('form_type');
if ($form_type === '') {
    $form_type = 'unknown';
}

// Собираем общие поля
$now = date('Y-m-d H:i:s');

// В зависимости от типа формы собираем данные
$lines = [];
$csv_row = [];

$lines[] = "Дата и время отправки: " . $now;
$lines[] = "Тип формы: " . $form_type;
$lines[] = "-----------------------------";

switch ($form_type) {
    case 'application':
        $child_name = field('child_name');
        $child_birthdate = field('child_birthdate');
        $child_age = field('child_age');
        $child_attendance = field('child_attendance');

        $interests = field_array('interests');
        $format_type = field('format_type');
        $days = field_array('days');
        $time_slots = field_array('time_slots');

        $parent_name = field('parent_name');
        $parent_phone = field('parent_phone');
        $parent_email = field('parent_email');
        $contact_method = field('contact_method');
        $comment = field('comment');

        $lines[] = "ФИО ребёнка: " . $child_name;
        $lines[] = "Дата рождения: " . $child_birthdate;
        $lines[] = "Возраст (лет): " . $child_age;
        $lines[] = "Посещает сейчас: " . $child_attendance;
        $lines[] = "Направления: " . implode(', ', $interests);
        $lines[] = "Формат занятий: " . $format_type;
        $lines[] = "Дни недели: " . implode(', ', $days);
        $lines[] = "Время: " . implode(', ', $time_slots);
        $lines[] = "Имя родителя: " . $parent_name;
        $lines[] = "Телефон: " . $parent_phone;
        $lines[] = "Email: " . $parent_email;
        $lines[] = "Способ связи: " . $contact_method;
        $lines[] = "Комментарий: " . $comment;

        $csv_row = [
            $now,
            $form_type,
            $child_name,
            $child_birthdate,
            $child_age,
            $child_attendance,
            implode(', ', $interests),
            $format_type,
            implode(', ', $days),
            implode(', ', $time_slots),
            $parent_name,
            $parent_phone,
            $parent_email,
            $contact_method,
            $comment
        ];
        $csv_header = [
            "datetime",
            "form_type",
            "child_name",
            "child_birthdate",
            "child_age",
            "child_attendance",
            "interests",
            "format_type",
            "days",
            "time_slots",
            "parent_name",
            "parent_phone",
            "parent_email",
            "contact_method",
            "comment"
        ];
        $csv_file = __DIR__ . '/../storage/applications_' . date('Y-m') . '.csv';
        $mail_subject = "Новая заявка на запись с сайта ({$PROJECT_NAME})";
        break;

    case 'trial':
        $csv_header = [
            "datetime",
            "form_type",
            "child_name",
            "child_age",
            "trial_program",
            "trial_days",
            "parent_phone",
            "comment"
        ];
        $csv_file = __DIR__ . '/../storage/trial_' . date('Y-m') . '.csv';
        $mail_subject = "Запрос на пробное занятие ({$PROJECT_NAME})";

        $child_name = field('child_name');
        $child_age = field('child_age');
        $trial_program = field('trial_program');
        $trial_days = field('trial_days');
        $parent_phone = field('parent_phone');
        $comment = field('comment');

        $lines[] = "Имя ребёнка: " . $child_name;
        $lines[] = "Возраст: " . $child_age;
        $lines[] = "Программа: " . $trial_program;
        $lines[] = "Предпочитаемые дни: " . $trial_days;
        $lines[] = "Телефон: " . $parent_phone;
        $lines[] = "Комментарий: " . $comment;

        $csv_row = [
            $now,
            $form_type,
            $child_name,
            $child_age,
            $trial_program,
            $trial_days,
            $parent_phone,
            $comment
        ];
        break;

    case 'question':
        $csv_header = [
            "datetime",
            "form_type",
            "parent_name",
            "parent_contact",
            "child_age",
            "question_to",
            "question_text"
        ];
        $csv_file = __DIR__ . '/../storage/questions_' . date('Y-m') . '.csv';
        $mail_subject = "Вопрос педагогу с сайта ({$PROJECT_NAME})";

        $parent_name = field('parent_name');
        $parent_contact = field('parent_contact');
        $child_age = field('child_age');
        $question_to = field('question_to');
        $question_text = field('question_text');

        $lines[] = "Имя родителя: " . $parent_name;
        $lines[] = "Контакт: " . $parent_contact;
        $lines[] = "Возраст ребёнка: " . $child_age;
        $lines[] = "Адресат вопроса: " . $question_to;
        $lines[] = "Вопрос:";
        $lines[] = $question_text;

        $csv_row = [
            $now,
            $form_type,
            $parent_name,
            $parent_contact,
            $child_age,
            $question_to,
            $question_text
        ];
        break;

    case 'feedback':
        $csv_header = [
            "datetime",
            "form_type",
            "parent_name",
            "child_name",
            "program_name",
            "rating",
            "feedback_text"
        ];
        $csv_file = __DIR__ . '/../storage/feedback_' . date('Y-m') . '.csv';
        $mail_subject = "Новый отзыв с сайта ({$PROJECT_NAME})";

        $parent_name = field('parent_name');
        $child_name = field('child_name');
        $program_name = field('program_name');
        $rating = field('rating');
        $feedback_text = field('feedback_text');

        $lines[] = "Имя родителя: " . $parent_name;
        $lines[] = "Имя ребёнка: " . $child_name;
        $lines[] = "Занятия: " . $program_name;
        $lines[] = "Оценка: " . $rating;
        $lines[] = "Отзыв:";
        $lines[] = $feedback_text;

        $csv_row = [
            $now,
            $form_type,
            $parent_name,
            $child_name,
            $program_name,
            $rating,
            $feedback_text
        ];
        break;

    case 'mini_test':
        $csv_header = [
            "datetime",
            "form_type",
            "age_group",
            "priority",
            "format",
            "parent_phone"
        ];
        $csv_file = __DIR__ . '/../storage/mini_test_' . date('Y-m') . '.csv';
        $mail_subject = "Мини-анкета с сайта ({$PROJECT_NAME})";

        $age_group = field('age_group');
        $priority = field('priority');
        $format = field('format');
        $parent_phone = field('parent_phone');

        $lines[] = "Возраст ребёнка: " . $age_group;
        $lines[] = "Приоритет: " . $priority;
        $lines[] = "Формат: " . $format;
        $lines[] = "Телефон: " . $parent_phone;

        $csv_row = [
            $now,
            $form_type,
            $age_group,
            $priority,
            $format,
            $parent_phone
        ];
        break;

    case 'quick_contact':
    default:
        $csv_header = [
            "datetime",
            "form_type",
            "parent_name",
            "parent_phone",
            "question_text"
        ];
        $csv_file = __DIR__ . '/../storage/quick_' . date('Y-m') . '.csv';
        $mail_subject = "Быстрая заявка с сайта ({$PROJECT_NAME})";

        $parent_name = field('parent_name');
        $parent_phone = field('parent_phone');
        $question_text = field('question_text');

        $lines[] = "Имя родителя: " . $parent_name;
        $lines[] = "Телефон: " . $parent_phone;
        $lines[] = "Комментарий:";
        $lines[] = $question_text;

        $csv_row = [
            $now,
            $form_type,
            $parent_name,
            $parent_phone,
            $question_text
        ];
        break;
}

// Сохраняем в CSV
if (!is_dir(__DIR__ . '/../storage')) {
    mkdir(__DIR__ . '/../storage', 0775, true);
}

$is_new_file = !file_exists($csv_file) || filesize($csv_file) === 0;

$fp = fopen($csv_file, 'a');
if ($fp) {
    if ($is_new_file) {
        fputcsv($fp, $csv_header, ';');
    }
    fputcsv($fp, $csv_row, ';');
    fclose($fp);
}

// Отправляем письмо администратору
if (filter_var($ADMIN_EMAIL, FILTER_VALIDATE_EMAIL)) {
    $body = implode("\n", $lines);
    $subject_encoded = '=?UTF-8?B?' . base64_encode($mail_subject) . '?=';
    $headers = "Content-Type: text/plain; charset=UTF-8\r\n" .
               "From: noreply@" . ($_SERVER['HTTP_HOST'] ?? 'example.com') . "\r\n";

    @mail($ADMIN_EMAIL, $subject_encoded, $body, $headers);
}

// Перенаправляем на страницу благодарности
header('Location: /thankyou.html');
exit;
