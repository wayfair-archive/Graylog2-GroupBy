<?php
require_once './lib/ElasticSearchClient.php';
require_once './lib/config.php';

/*
 * shorten_url - Returns the encoded parameters
 * serialized and
 * @param $params the array to be encoded
 * @return string the encoded values in string format
 */
function shorten_url($params) {
    return urlencode(gzcompress(serialize($params),9));
}

/*
 * unshorten_url - Returns the params encoded in the short url
 * @param string $value the string to be decoded
 * @return array the decoded string
 */
function unshorten_url($value) {
    return unserialize(gzuncompress(urldecode($value)));
}

/*
 * print_rows - Returns the row with each of the strings in the url
 * @param array $strings the array of strings to be printed in the row
 * @param int $numcols the number of columns to output
 * @return the string of rows
 */
function print_row($strings, $numcols) {
    $result = "<tr>";
    while (is_int($numcols) &&  $numcols > 0) {
        foreach ($strings as $string) {
            $result .= "<td>".$string."</td>";
            $numcols--;
            unset($string);
        }
    }
    $result .= "</tr>";
    return $result;
}

function generate_query($hostname, $start, $stop, $words, $debug) {
    $query = array(
        'query' => array(
            'filtered' => array(
                'query' => array(
                    'wildcard' => array(
                        'host' => $hostname)),
                'filter' => array(
                    'and' => array(
                        array(
                            'range' => array(
                                'created_at' => array(
                                    'from' => strtotime($start),
                                    'to' => strtotime($stop)
                                ))),
                        array(
                            'query' => array(
                                'text' => array(
                                    'message' => array(
                                        'query' => join(' ', $words),
                                        'operator' => 'and'
                                    ))),),
                        array(
                            'limit' => array('value' => 400)
                        )
                    )
                )),),
        'sort' => array(
            'created_at' => array(
                'order' => 'desc')),
        'size' => 20000
        );

    if ($debug === TRUE) {
        print_r($query);
    }
    return $query;
}

function do_query($query, $debug) {
    $transport = new ElasticSearchTransportHTTP(ELASTICSEARCH_HOST, ELASTICSEARCH_PORT);
    $search = new ElasticSearchClient($transport, "graylog2", "message");
    $results = $search->search($query);

    if ($debug === TRUE) {
        @print_r($results['error']);
    }
    return $results;
}

?>
