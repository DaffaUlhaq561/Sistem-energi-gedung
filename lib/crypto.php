<?php

if (!defined('CRYPTO_AVAILABLE')) {
    define('CRYPTO_AVAILABLE', extension_loaded('sodium'));
}

function crypto_master_key()
{
    // Coba berbagai cara untuk mendapatkan APP_MASTER_KEY
    $keyEnv = getenv('APP_MASTER_KEY');
    if (!$keyEnv && isset($_ENV['APP_MASTER_KEY'])) {
        $keyEnv = $_ENV['APP_MASTER_KEY'];
    }
    if (!$keyEnv && isset($_SERVER['APP_MASTER_KEY'])) {
        $keyEnv = $_SERVER['APP_MASTER_KEY'];
    }
    // Fallback: cek di config.php jika didefinisikan
    if (!$keyEnv && defined('APP_MASTER_KEY')) {
        $keyEnv = constant('APP_MASTER_KEY');
    }
    
    if (!$keyEnv) {
        throw new Exception('APP_MASTER_KEY belum diset. Set via environment variable, $_ENV, $_SERVER, atau define di config.php');
    }
    return hash('sha256', $keyEnv, true);
}

function crypto_encrypt($plaintext)
{
    $key = crypto_master_key();
    if (CRYPTO_AVAILABLE) {
        $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $cipher = sodium_crypto_secretbox($plaintext, $nonce, $key);
        return 'SODIUM:' . base64_encode($nonce . $cipher);
    }
    if (!extension_loaded('openssl')) {
        throw new Exception('Ext-openssl tidak aktif untuk fallback enkripsi.');
    }
    $iv = random_bytes(12);
    $tag = '';
    $cipher = openssl_encrypt($plaintext, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag, '', 16);
    if ($cipher === false || !$tag) {
        throw new Exception('Gagal enkripsi fallback.');
    }
    return 'OSSLGCM:' . base64_encode($iv . $tag . $cipher);
}

function crypto_decrypt($encoded)
{
    $key = crypto_master_key();
    $pref = null;
    $data = $encoded;
    if (strpos($encoded, 'SODIUM:') === 0) {
        $pref = 'SODIUM';
        $data = substr($encoded, 7);
    } elseif (strpos($encoded, 'OSSLGCM:') === 0) {
        $pref = 'OSSLGCM';
        $data = substr($encoded, 8);
    }
    if ($pref === 'SODIUM' || ($pref === null && CRYPTO_AVAILABLE)) {
        if (!CRYPTO_AVAILABLE) {
            throw new Exception('Ext-sodium belum aktif. Aktifkan libsodium di PHP untuk enkripsi.');
        }
        $raw = base64_decode($data, true);
        if ($raw === false || strlen($raw) < (SODIUM_CRYPTO_SECRETBOX_NONCEBYTES + SODIUM_CRYPTO_SECRETBOX_MACBYTES)) {
            throw new Exception('Data terenkripsi tidak valid.');
        }
        $nonce = substr($raw, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $cipher = substr($raw, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $plain = sodium_crypto_secretbox_open($cipher, $nonce, $key);
        if ($plain === false) {
            throw new Exception('Gagal dekripsi.');
        }
        return $plain;
    }
    if ($pref === 'OSSLGCM' || ($pref === null && extension_loaded('openssl'))) {
        $raw = base64_decode($data, true);
        if ($raw === false || strlen($raw) < (12 + 16 + 1)) {
            throw new Exception('Data terenkripsi tidak valid.');
        }
        $iv = substr($raw, 0, 12);
        $tag = substr($raw, 12, 16);
        $cipher = substr($raw, 28);
        $plain = openssl_decrypt($cipher, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag);
        if ($plain === false) {
            throw new Exception('Gagal dekripsi.');
        }
        return $plain;
    }
    throw new Exception('Format enkripsi tidak dikenali.');
}
