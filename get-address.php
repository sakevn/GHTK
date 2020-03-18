<?php
header( 'Content-Type: application/json; charset=UTF-8' );

$matp = isset($_POST['matp']) ? htmlspecialchars($_POST['matp']) : '';
$maqh = isset($_POST['maqh']) ? intval($_POST['maqh']) : '';

function is_serialized( $data, $strict = true ) {
    // if it isn't a string, it isn't serialized.
    if ( ! is_string( $data ) ) {
        return false;
    }
    $data = trim( $data );
    if ( 'N;' == $data ) {
        return true;
    }
    if ( strlen( $data ) < 4 ) {
        return false;
    }
    if ( ':' !== $data[1] ) {
        return false;
    }
    if ( $strict ) {
        $lastc = substr( $data, -1 );
        if ( ';' !== $lastc && '}' !== $lastc ) {
            return false;
        }
    } else {
        $semicolon = strpos( $data, ';' );
        $brace     = strpos( $data, '}' );
        // Either ; or } must exist.
        if ( false === $semicolon && false === $brace ) {
            return false;
        }
        // But neither must be in the first X characters.
        if ( false !== $semicolon && $semicolon < 3 ) {
            return false;
        }
        if ( false !== $brace && $brace < 4 ) {
            return false;
        }
    }
    $token = $data[0];
    switch ( $token ) {
        case 's':
            if ( $strict ) {
                if ( '"' !== substr( $data, -2, 1 ) ) {
                    return false;
                }
            } elseif ( false === strpos( $data, '"' ) ) {
                return false;
            }
        // or else fall through
        case 'a':
        case 'O':
            return (bool) preg_match( "/^{$token}:[0-9]+:/s", $data );
        case 'b':
        case 'i':
        case 'd':
            $end = $strict ? '$' : '';
            return (bool) preg_match( "/^{$token}:[0-9.E+-]+;$end/", $data );
    }
    return false;
}

function maybe_unserialize( $original ) {
    if ( is_serialized( $original ) ) {
        return @unserialize( $original );
    }
    return $original;
}

function devvn_search_in_array($array, $key, $value)
{
    $results = array();

    if (is_array($array)) {
        if (isset($array[$key]) && $array[$key] == $value) {
            $results[] = $array;
        }elseif(isset($array[$key]) && is_serialized($array[$key]) && in_array($value,maybe_unserialize($array[$key]))){
            $results[] = $array;
        }
        foreach ($array as $subarray) {
            $results = array_merge($results, devvn_search_in_array($subarray, $key, $value));
        }
    }

    return $results;
}

function devvn_natorder($a,$b) {
    return strnatcasecmp ( $a['name'], $b['name'] );
}

$result = array('success' => false);

if($matp){
    include 'cities/quan_huyen.php';
    $quan = devvn_search_in_array($quan_huyen,'matp',$matp);
    usort($quan, 'devvn_natorder' );
    if($quan) {
        $result = array(
            'success' => true,
            'data' => $quan
        );
    }
}

if($maqh){
    include 'cities/xa_phuong_thitran.php';
    $id_xa = sprintf("%05d", intval($maqh));
    $xa = devvn_search_in_array($xa_phuong_thitran,'maqh',$id_xa);
    usort($xa, 'devvn_natorder' );
    if($xa) {
        $result = array(
            'success' => true,
            'data' => $xa
        );
    }
}

echo json_encode($result);

die();