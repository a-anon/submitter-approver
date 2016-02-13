<?php
require 'vendor/autoload.php';
require 'config.php';

use Facebook\WebDriver\Exception\WebDriverCurlException;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\WebDriverCapabilityType;
use Facebook\WebDriver\WebDriver;
use Facebook\WebDriver\WebDriverBy;

$capabilities = array(WebDriverCapabilityType::BROWSER_NAME => 'firefox');
$web = RemoteWebDriver::create('http://selenium:4444/wd/hub', $capabilities);

$web->get('https://reddit.com/login');

/**
 * @param $web
 * @param $input_id
 * @param $value
 * @return mixed
 */
function write_in_input(RemoteWebDriver $web, $input_id, $value)
{
    $input = $web->findElement(WebDriverBy::id($input_id));
    $input->click();
    $web->getKeyboard()->sendKeys($value);
    return $input;
}

function random_ms_sleep()
{
    for ($i = 0; $i < rand(1337, 3000000); $i++) {
        md5($i);
    }
}

write_in_input($web, 'user_login', REDDIT_USERNAME);
write_in_input($web, 'passwd_login', REDDIT_PASSWORD);

$web->findElement(WebDriverBy::id('passwd_login'))->submit();

$web->wait()->until(function (WebDriver $driver){
    return $driver->getCurrentURL() === 'https://www.reddit.com/';
});

$users = explode("\n", file_get_contents('users.txt'));

foreach ($users as $user) {
    try {
        $web->get('https://www.reddit.com/r/' . SUBREDDIT . '/about/contributors/');

        write_in_input($web, 'name', $user);

        random_ms_sleep();
        sleep(rand(3, 7));
        $web->findElement(WebDriverBy::id('name'))->submit();

        random_ms_sleep();
        sleep(rand(57, 114));

        $status = $web->findElement(WebDriverBy::cssSelector('.status'));
        if (preg_match('/RATELIMIT/', $status->getText())) {
            file_put_contents('users.txt', implode("\n", $users));
            exit;
        }
        array_shift($users);
    } catch (WebDriverCurlException $e) {
        continue;
    }
}

$web->quit();
