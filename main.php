<?php

    // read in the CSV file and store needed data to array
    include('readCSV.php');
    
    $google_url = "http://www.google.ca/search?hl=en&q=";
    
    $crawlable_urls = array();
    $found_emails = array();
    
    // for each name in $charity_names look it up on google.
    foreach($charity_names as $charity) {
        $gurl = $google_url . urlencode($charity);
        
        echo "<b>GOOGLING:</b> " . $charity . "<br>";
        
        $links = get_links($gurl);
        $valid_links = array();
        //$crawlable_urls = array(); //crawled urls for this particular charity
        
        foreach($links as $url) {
            if (
                (!(strpos($url['url'], "google"))) &&  // ignore google
                (!(strpos($url['url'], "facebook"))) &&  // ignore facebook
                (!(strpos($url['url'], "youtube"))) &&  // ignore youtube
                (!(strpos($url['url'], "blogger"))) && // ignore bs
                (!(strpos($url['url'], "chimp"))) && // ignore charity wiki w/ no emails...
                ((strpos($url['url'], "http"))) // must have http
            ) {
                $start_of_url = strpos($url['url'], "http");
                if ($start_of_url !== FALSE) {
                    $end_of_domain = strpos($url['url'], "/", strpos($url['url'], "http")+10);
                    if ($end_of_domain !== FALSE) {
                        $tlink = substr($url['url'], $start_of_url, $end_of_domain-$start_of_url);
                        echo "&nbsp;&nbsp;<b>LINK FOUND:</b> " . $tlink . "<br>";
                        array_push($valid_links, $tlink);
                        break;
                    } else {
                        $tlink = substr($url['url'], $start_of_url);
                        echo "&nbsp;&nbsp;<b>LINK FOUND:</b> " . $tlink . "<br>";
                        array_push($valid_links, $tlink);
                        break;
                    }
                }
            }
        }
        
        foreach($valid_links as $vurl) {
            
            if (!(in_array($vurl, $crawlable_urls)))
                array_push($crawlable_urls, $vurl);
            
            $site_links = get_links($vurl);
            foreach($site_links as $clink) {
                if (((stripos($clink['text'], "contact") !== FALSE) || (stripos($clink['url'], "contact") !== FALSE)) && !(stripos($clink['url'], "mailto") !== FALSE)) {
                    if (substr($clink['url'], 0, 4) == "http") { // valid link
                        if (!(in_array($clink['url'], $crawlable_urls))) {
                            array_push($crawlable_urls, $clink['url']);
                            echo "&nbsp;&nbsp;&nbsp;&nbsp;<b>SUBLINK FOUND:</b> " .  $clink['url'] . "<br>";
                        }
                    } else {
                        if (substr($clink['url'], 0, 1) == "/") {
                            $fixed_url = $vurl . $clink['url'];
                            if (!(in_array($fixed_url, $crawlable_urls))) {
                                array_push($crawlable_urls, $fixed_url);
                                echo "&nbsp;&nbsp;&nbsp;&nbsp;<b>SUBLINK FOUND:</b> " .  $fixed_url . "<br>";
                            }
                        } else {
                            $fixed_url = $vurl . "/" . $clink['url'];
                            if (!(in_array($fixed_url, $crawlable_urls))) {
                                array_push($crawlable_urls, $fixed_url);
                                echo "&nbsp;&nbsp;&nbsp;&nbsp;<b>SUBLINK FOUND:</b> " .  $fixed_url . "<br>";
                            }
                        }
                    }
                }
            }
        }
        echo "<hr>";
		sleep(2);
    }
    
    // start scanning each link in $crawlable_urls for emails
    foreach($crawlable_urls as $source_url) {
        echo "<b>SCANNING URL:</b> " . $source_url . "<br>";
        $source_html = file_get_contents($source_url);
        $email_pattern = '/[a-z0-9_\-\+]+@[a-z0-9\-]+\.([a-z]{2,3})(?:\.[a-z]{2})?/i';
        preg_match_all($email_pattern, $source_html, $source_emails);
        
        foreach($source_emails as $emails) {
            foreach($emails as $email) {
                if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    if (!(in_array($email, $found_emails))) {
                        echo "&nbsp;&nbsp;<b>FOUND EMAIL:</b> " . $email . "<br>";
                        array_push($found_emails, $email);
                        // do something better with the emails here or after this loop
                    }
                }
            }
        }
        echo "<hr>";
    }
    
    echo "<hr>";
    echo "<b>FOUND " . count($found_emails) . " EMAILS</b><br><br><br><br><br>";
    
    function get_links($gurl) {
        $xml = new DOMDocument();
        @$xml->loadHTMLFile($gurl);
        $links = array();
        
        foreach($xml->getElementsByTagName('a') as $link) {
            // gets the link + link title
            $links[] = array('url' => $link->getAttribute('href'), 'text' => $link->nodeValue);
        }
        
        return $links;
    }

?>