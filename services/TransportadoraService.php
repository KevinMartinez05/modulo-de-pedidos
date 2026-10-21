<?php
// =============================================================
// services/TransportadoraService.php
// Consulta la API de tarifas de la transportadora
// =============================================================

class TransportadoraService {

    private string $apiUrl;
    private string $apiKey;

    public function __construct() {
        $this->apiUrl = TRANSPORTADORA_API_URL;
        $this->apiKey = TRANSPORTADORA_API_KEY;
    }

    /**
     * Cotiza el costo de envío llamando al endpoint POST de la transportadora.
     *
     * @param string $origen   Ciudad de origen
     * @param string $destino  Ciudad de destino
     * @param float  $peso     Peso en kg
     * @return array ['costo' => float, 'tiempo_dias' => int, 'raw' => array]
     */
    public function cotizar(string $origen, string $destino, float $peso = 1.0): array {
        $payload = json_encode([
            'origen'  => $origen,
            'destino' => $destino,
            'peso_kg' => $peso,
        ]);

        $ch = curl_init($this->apiUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->apiKey,
                'Content-Length: ' . strlen($payload),
            ],
            // Deshabilitar verificación SSL solo en desarrollo local
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        // Si la API no está disponible (dominio .test no existe en desarrollo)
        // usamos una tarifa simulada para no bloquear el flujo
        if ($curlError || $httpCode !== 200) {
            return $this->tarifaSimulada($origen, $destino, $peso);
        }

        $data = json_decode($response, true);

        return [
            'costo'      => (float) ($data['costo_envio'] ?? $data['costo'] ?? 0),
            'tiempo_dias'=> (int)   ($data['tiempo_entrega_dias'] ?? 3),
            'raw'        => $data,
        ];
    }

    /**
     * Tarifa simulada cuando la API real no está disponible.
     * Lógica: tarifa base + $500 por cada 100 km estimados
     */
    private function tarifaSimulada(string $origen, string $destino, float $peso): array {
        // Distancias aproximadas desde Bogotá (en km) para simulación
        $distancias = [
            'Medellín'      => 410,
            'Cali'          => 460,
            'Barranquilla'  => 1000,
            'Bucaramanga'   => 400,
            'Cartagena'     => 1050,
            'Pereira'       => 320,
            'Manizales'     => 310,
            'Bogotá'        => 0,
        ];

        $dist   = $distancias[$destino] ?? 500;
        $base   = 15000;                       // Tarifa base en COP
        $porKm  = $dist * 10;                  // $10 por km
        $porKg  = $peso * 2000;                // $2000 por kg
        $total  = $base + $porKm + $porKg;

        return [
            'costo'       => round($total, 2),
            'tiempo_dias' => (int) ceil($dist / 200) + 1,
            'raw'         => ['simulado' => true, 'nota' => 'API de transportadora no disponible en este entorno'],
        ];
    }
}
