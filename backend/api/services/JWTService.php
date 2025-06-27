<?php
namespace backend\api\services;

class JWTService {
    private static $secret;
    private static $algorithm = 'HS256';
    
    public static function init() {
        self::$secret = $_ENV['JWT_SECRET'] ?? 'sua_chave_secreta_super_forte_aqui_2025';
    }
    
    public static function encode($payload) {
        self::init();
        
        $payload['iat'] = time();
        $payload['exp'] = time() + 3600; // ← 1 hora fixa (3600 segundos)
    
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $payload = json_encode($payload);
        
        $base64Header = self::base64UrlEncode($header);
        $base64Payload = self::base64UrlEncode($payload);
        
        $signature = hash_hmac('sha256', $base64Header . "." . $base64Payload, self::$secret, true);
        $base64Signature = self::base64UrlEncode($signature);
        
        return $base64Header . "." . $base64Payload . "." . $base64Signature;
    }
    
    public static function decode($jwt) {
        self::init();
        
        $parts = explode('.', $jwt);
        if (count($parts) !== 3) {
            throw new \Exception('Token JWT inválido');
        }
        
        [$header, $payload, $signature] = $parts;
        
        // Verificar assinatura
        $expectedSignature = hash_hmac('sha256', $header . "." . $payload, self::$secret, true);
        $expectedSignature = self::base64UrlEncode($expectedSignature);
        
        if (!hash_equals($signature, $expectedSignature)) {
            throw new \Exception('Assinatura JWT inválida');
        }
        
        $payload = json_decode(self::base64UrlDecode($payload), true);
        
        // Verificar expiração
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            throw new \Exception('Token JWT expirado');
        }
        
        return $payload;
    }
    
    public static function validate($jwt) {
        try {
            $payload = self::decode($jwt);
            return $payload;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    private static function base64UrlEncode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
    
    private static function base64UrlDecode($data) {
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
    }
}
?>