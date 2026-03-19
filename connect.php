<?php
$dbFile = __DIR__ . '/diary.db';
try {
    $db = new SQLite3($dbFile);
} catch (Exception $e) {
    die("Unable to open database: " . $e->getMessage());
}

$db->exec("CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT,
    username TEXT UNIQUE,
    email TEXT,
    password TEXT
)");

$db->exec("CREATE TABLE IF NOT EXISTS diary_entries (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER,
    entry TEXT,
    entry_date TEXT,
    mood TEXT DEFAULT 'neutral',
    title TEXT DEFAULT ''
)");

@$db->exec("ALTER TABLE diary_entries ADD COLUMN mood TEXT DEFAULT 'neutral'");
@$db->exec("ALTER TABLE diary_entries ADD COLUMN title TEXT DEFAULT ''");
?>