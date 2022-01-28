<?php

$env = parse_ini_file('env.ini');
$db=new mysqli("localhost", $env['db_username'], $env['db_password'],$env['db_name']);

$sql="SELECT * FROM tickbyhour WHERE type='BTC'";
$ticks = $db->query($sql);

$money = 1000;
$investment = 0.25;
$btc = 0;
$step = 1;
$buy = true;

echo "<h1>Starting Trader</h1>";

foreach($ticks as $tick){

    if($money <= 0){
        if($btc>0){
            $buy = false;
        }else{
            die("<h1>You broke</h1>");
        }
    }

    if($step%8==0){
        if($buy){
            echo '<br>'.($tick['open']/100000000).'<br>';
            $spend = $investment*$money;
            $money -= $spend;
            $buying = $spend/($tick['open']/100000000);
            $btc += $buying;
            echo "<br>Spent: $spend, Got: $buying, Bal: $money, BTC: $btc";
        }else{
            $earn = $btc * $tick['open']/100000000;
            $money += $earn;
            $btc = 0;
            echo "<br>Earn: $earn, Bal: $money";
        }
        $buy = !$buy;
    }
    $step++;

}

echo "<h1>We made it</h1>";
$earn = $btc * $tick['open']/100000000;
$money += $earn;
echo "<br>Bal: $money";

?>