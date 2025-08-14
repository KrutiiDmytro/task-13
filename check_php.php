<?php
echo "PHP Version: " . phpversion() . "\n";
echo "Loaded Extensions:\n";
print_r(get_loaded_extensions());

echo "\nMySQL Extensions:\n";
echo "PDO MySQL: " . (extension_loaded('pdo_mysql') ? 'YES' : 'NO') . "\n";
echo "MySQLi: " . (extension_loaded('mysqli') ? 'YES' : 'NO') . "\n";