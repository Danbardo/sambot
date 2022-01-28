<?php

// Connect to DB
set_time_limit(600);
$env = parse_ini_file('env.ini');
$db=new mysqli("localhost", $env['db_username'], $env['db_password'],$env['db_name']);

# Create a table if it doesn't already exist
$sql = "CREATE TABLE IF NOT EXISTS tickbyhour (
      id INT AUTO_INCREMENT PRIMARY KEY,
      type VARCHAR(10) NOT NULL,
      open BIGINT(20),
      high BIGINT(20),
      low BIGINT(20),
      close BIGINT(20),
      volume BIGINT(20),
      timestamp BIGINT(20),
      datetime DATETIME,
      created DATETIME DEFAULT CURRENT_TIMESTAMP
      )  ENGINE=INNODB;";
$db->query($sql);


// The API URL
$api = "https://api.btcmarkets.net/v2/market/";
# The types of coin you want to gather data for
$types = ["BTC","ETH","XRP","LTC","POWR","OMG"];

# Loop over all the types
foreach($types as $type){
    // Assume that no data has been collected yet
    $timestamp = 1514725200000;  # Start of 2018
    // Set coin type in URL
    $url = $api . $type . "/AUD/tickByTime/hour";
    //print("Starting :" + type)

    # Check if any data already exists in the table
    $sql = "SELECT max(timestamp) as m from tickbyhour where type = '$type'";
    $query = $db->query($sql);

    if($query->num_rows>0){
        $result = $query -> fetch_assoc();
        if($result['m']>0){
            $timestamp = $result['m'];
        }
    }

    while(true){
        $url.="?indexForward=true&sortForward=true&since=$timestamp";

        $raw_data = file_get_contents($url);
        $data = json_decode($raw_data,true);
        $data = $data['ticks'];
        if(sizeof($ticks)<1){
            break;
        }

        foreach($data as $tick){
            $timestamp = $tick['timestamp'];
            $sql="INSERT INTO tickbyhour
                    (type,open,high,low,close,volume,timestamp,datetime) 
                    VALUES 
                    (
                        '$type',
                        '{$tick['open']}',
                        '{$tick['high']}',
                        '{$tick['low']}',
                        '{$tick['close']}',
                        '{$tick['volume']}',
                        $timestamp,
                        FROM_UNIXTIME($timestamp/1000)
                    )";
            $db->query($sql);
        }
        $timestamp++;
    }
}

?>