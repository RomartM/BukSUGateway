<?php

// Data encryptions
// https://gist.github.com/tott/7544453

function gw_enc_meta(){
    $enc_meta['ciphering'] = "AES-128-CTR";
    $enc_meta['options'] = 0;
    $enc_meta['encryption_iv'] = '1234567891011121';
    $enc_meta['encryption_key'] = "GeeksforGeeks";
    return $enc_meta;
}

function gw_encrypt_data( $decrypted ) {
    list($ciphering, $options, $encryption_iv, $encryption_key) = array_values(gw_enc_meta());
    return openssl_encrypt($decrypted, $ciphering,
        $encryption_key, $options, $encryption_iv);
}

function gw_decrypt_data( $encrypted ) {
    list($ciphering, $options, $decryption_iv, $decryption_key) = array_values(gw_enc_meta());
    return openssl_decrypt ($encrypted, $ciphering,
        $decryption_key, $options, $decryption_iv);
}
