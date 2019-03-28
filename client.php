<?php

require __DIR__ . '/vendor/autoload.php';

#format: client.php -c{client_id} -e{email} -n{name}
#usage:  php client.php -c123-34cv -ejohn@dev.pro -nJohn
$options = getopt('c:e:n:');

if(!isset($options['c'],$options['e'],$options['n'])) {
    die('Put all options together!');
}

$client = new SimpleClient($options['c'], $options['e'], $options['n']);
$report = new SimpleReport($client);
$report->make();

Response::json($report->data());


