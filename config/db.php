<?php

// SQLite only — no external database server. The database is a self-contained file
// under runtime/, created on first migrate.
return [
    'class' => 'yii\db\Connection',
    'dsn' => 'sqlite:' . dirname(__DIR__) . '/runtime/database.sqlite',
];
