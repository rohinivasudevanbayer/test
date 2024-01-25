<?php
// Workaround for AWS Envorinment settings
if (file_exists(__DIR__ . '/aws-env.config.php')) {
  include __DIR__ . '/aws-env.config.php';
}

foreach(['RDS_DB_NAME', 'RDS_HOSTNAME', 'RDS_PORT', 'RDS_USERNAME', 'RDS_PASSWORD', 'SMTP_HOSTNAME', 'SMTP_SSL', 'SMTP_PORT', 'SMTP_USERNAME', 'SMTP_PASSWORD'] as $key) {
  if (!defined($key) && isset($_SERVER[$key])) {
    define($key, $_SERVER[$key]);
  }
}