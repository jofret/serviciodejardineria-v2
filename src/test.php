<?php
echo "MAIL_HOST desde getenv: " . getenv('MAIL_HOST') . "\n";
echo "MAIL_HOST desde \$_ENV: " . ($_ENV['MAIL_HOST'] ?? 'no existe') . "\n";
echo "MAIL_HOST desde \$_SERVER: " . ($_SERVER['MAIL_HOST'] ?? 'no existe') . "\n";
?>
