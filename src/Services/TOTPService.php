<?php
class TOTPService {

    private static function base32Decode(string $b32): string {
        $b32 = strtoupper($b32);
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $output = '';
        $bits = 0; $bitsCount = 0;
        for ($i = 0; $i < strlen($b32); $i++) {
            $val = strpos($alphabet, $b32[$i]);
            if ($val === false) continue;
            $bits = ($bits << 5) | $val;
            $bitsCount += 5;
            if ($bitsCount >= 8) {
                $output .= chr(($bits >> ($bitsCount - 8)) & 0xFF);
                $bitsCount -= 8;
            }
        }
        return $output;
    }

    public static function generateSecret(): string {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $secret = '';
        for ($i = 0; $i < 16; $i++) {
            $secret .= $alphabet[random_int(0, 31)];
        }
        return $secret;
    }

    public static function getCode(string $secret, int $time = 0): string {
        $key = self::base32Decode($secret);
        $t = intdiv($time ?: time(), 30);
        $msg = pack('N*', 0) . pack('N*', $t);
        $hash = hash_hmac('sha1', $msg, $key, true);
        $offset = ord($hash[19]) & 0xF;
        $code = (
            ((ord($hash[$offset]) & 0x7F) << 24) |
            ((ord($hash[$offset+1]) & 0xFF) << 16) |
            ((ord($hash[$offset+2]) & 0xFF) << 8) |
            (ord($hash[$offset+3]) & 0xFF)
        ) % 1000000;
        return str_pad($code, 6, '0', STR_PAD_LEFT);
    }

    public static function verify(string $secret, string $code): bool {
        $t = time();
        for ($i = -1; $i <= 1; $i++) {
            if (self::getCode($secret, $t + $i * 30) === $code) return true;
        }
        return false;
    }

    /**
     * Retourne l'URI otpauth (pour l'app authenticator).
     * Le QR code est généré côté client en JavaScript via la librairie qrcode.js
     * — le secret ne transite plus vers un service tiers.
     */
    public static function getOtpauthUri(string $secret, string $email, string $issuer = 'Cabinet SMC'): string {
        $label = rawurlencode("$issuer:$email");
        $params = http_build_query([
            'secret'    => $secret,
            'issuer'    => $issuer,
            'algorithm' => 'SHA1',
            'digits'    => 6,
            'period'    => 30,
        ]);
        return "otpauth://totp/$label?$params";
    }

    /** @deprecated Utiliser getOtpauthUri() + QR côté client */
    public static function getQRUrl(string $secret, string $email, string $issuer = 'Cabinet SMC'): string {
        return self::getOtpauthUri($secret, $email, $issuer);
    }
}
