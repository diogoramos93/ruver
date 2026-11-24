<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Organizer;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AsaasPaymentService
{
    protected string $baseUrl;
    protected ?string $masterWalletId;

    public function __construct()
    {
        $environment = config('services.asaas.env', 'sandbox');
        
        // URLs já corrigidas para a versão nova da API do Asaas!
        $this->baseUrl = $environment === 'production'
            ? 'https://api.asaas.com/v3'
            : 'https://api-sandbox.asaas.com/v3';

        $this->masterWalletId = config('services.asaas.master_wallet_id');
    }

    /**
     * Obter ou Criar o Cliente no Asaas
     */
    public function getOrCreateCustomer(Organizer $organizer, string $name, string $cpfCnpj, string $email): ?string
    {
        $apiKey = $organizer->asaas_api_key ?: config('services.asaas.master_api_key');
        if (empty($apiKey)) {
            return null;
        }

        try {
            $response = Http::withHeaders([
                'access_token' => $apiKey,
                'Content-Type' => 'application/json',
            ])->get("{$this->baseUrl}/customers", [
                'cpfCnpj' => preg_replace('/\D/', '', $cpfCnpj),
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (!empty($data['data'][0]['id'])) {
                    return $data['data'][0]['id'];
                }
            }

            $createResponse = Http::withHeaders([
                'access_token' => $apiKey,
                'Content-Type' => 'application/json',
            ])->post("{$this->baseUrl}/customers", [
                'name' => $name,
                'cpfCnpj' => preg_replace('/\D/', '', $cpfCnpj),
                'email' => $email,
            ]);

            if ($createResponse->successful()) {
                return $createResponse->json('id');
            }
        } catch (\Exception $e) {
            Log::error('Erro ao conectar com Asaas Customer API: ' . $e->getMessage());
        }

        // --- INÍCIO DOS LOGS DETALHADOS (ARMADILHA PARA PEGAR O ERRO) ---
        Log::error('Buscar cliente Asaas', [
            'status' => isset($response) ? $response->status() : 'sem resposta',
            'body' => isset($response) ? $response->body() : 'sem resposta',
        ]);

        Log::error('Criar cliente Asaas', [
            'status' => isset($createResponse) ? $createResponse->status() : 'sem resposta',
            'body' => isset($createResponse) ? $createResponse->body() : 'sem resposta',
        ]);
        // --- FIM DOS LOGS ---

        return null;
    }

    /**
     * Criar Cobrança (PIX, Cartão ou Boleto) com Split de Pagamento
     */
    public function createPayment(Order $order, Organizer $organizer, array $customerData, float $valorTaxaPlataforma, string $billingType = 'PIX', array $creditCardData = []): array
    {
        $apiKey = $organizer->asaas_api_key ?: config('services.asaas.master_api_key');

        if (empty($apiKey)) {
            return $this->generateSimulationPixResponse($order);
        }

        $customerId = $this->getOrCreateCustomer(
            $organizer,
            $customerData['name'] ?? 'Atleta',
            $customerData['cpf'] ?? '00000000000',
            $customerData['email'] ?? 'atleta@email.com'
        );

        if (!$customerId) {
            throw new \Exception("Erro ao criar ou localizar o cliente no Asaas.");
        }

        $split = [];
        if ($this->masterWalletId && $valorTaxaPlataforma > 0) {
            $split[] = [
                'walletId' => $this->masterWalletId,
                'fixedValue' => round($valorTaxaPlataforma, 2)
            ];
        }

        $payload = [
            'customer' => $customerId,
            'billingType' => strtoupper($billingType),
            'value' => round($order->valor_total, 2),
            'dueDate' => now()->addDays(1)->format('Y-m-d'),
            'description' => "Inscrição Evento - Pedido #{$order->id}",
            'externalReference' => (string) $order->id,
        ];

        if (strtoupper($billingType) === 'CREDIT_CARD' && !empty($creditCardData)) {
            $payload['creditCard'] = [
                'holderName' => $creditCardData['holderName'],
                'number' => preg_replace('/\D/', '', $creditCardData['number']),
                'expiryMonth' => $creditCardData['expiryMonth'],
                'expiryYear' => $creditCardData['expiryYear'],
                'ccv' => $creditCardData['ccv'],
            ];
            $payload['creditCardHolderInfo'] = [
                'name' => $customerData['name'],
                'email' => $customerData['email'],
                'cpfCnpj' => preg_replace('/\D/', '', $customerData['cpf']),
                'postalCode' => '88880000', // Valor padrão, pode ajustar futuramente
                'addressNumber' => '1',
                'phone' => preg_replace('/\D/', '', $customerData['phone'] ?? '00000000000'),
            ];
            $payload['remoteIp'] = request()->ip();
        }

        if (!empty($split)) {
            $payload['split'] = $split;
        }

        // FAZ A REQUISIÇÃO PARA O ASAAS
        $response = Http::withHeaders([
            'access_token' => $apiKey,
            'Content-Type' => 'application/json',
        ])->post("{$this->baseUrl}/payments", $payload);

        $paymentData = $response->json();

        // 1. VERIFICAÇÃO CRÍTICA INSPIRADA NO SEU CÓDIGO DE REFERÊNCIA
        if (!empty($paymentData['errors'])) {
            $errorMessage = $paymentData['errors'][0]['description'];
            Log::error("Asaas Recusou Pagamento Pedido #{$order->id}: " . $errorMessage);
            throw new \Exception($errorMessage);
        }

        // 2. Se falhou por outro motivo de servidor (erro 500, fora do ar)
        if (!$response->successful()) {
            Log::error('Erro Asaas HTTP: ' . $response->body());
            throw new \Exception('Ocorreu um erro inesperado ao se comunicar com a operadora de pagamentos.');
        }

        // SE PASSOU DAS TRAVAS ACIMA, FOI APROVADO!
        $paymentId = $paymentData['id'];

        $result = [
            'success' => true,
            'payment_id' => $paymentId,
            'is_simulation' => false,
        ];

        // Busca o QR Code caso seja PIX
        if (strtoupper($billingType) === 'PIX') {
            
            // PEQUENO DELAY (1s) PARA O ASAAS CONSEGUIR GERAR A CHAVE PIX NO BANCO CENTRAL
            sleep(1);

            $pixQrResponse = Http::withHeaders([
                'access_token' => $apiKey,
            ])->get("{$this->baseUrl}/payments/{$paymentId}/pixQrCode");

            if ($pixQrResponse->successful()) {
                $pixData = $pixQrResponse->json();
                $result['qr_code_base64'] = $pixData['encodedImage'] ?? null;
                $result['pix_copia_cola'] = $pixData['payload'] ?? null;
            } else {
                Log::error("Falha ao buscar QR Code Pix. Resposta: " . $pixQrResponse->body());
            }
        } 
        // Busca a URL caso seja Boleto
        elseif (strtoupper($billingType) === 'BOLETO') {
            $result['bank_slip_url'] = $paymentData['bankSlipUrl'] ?? null;
        }

        return $result;
    }

    /**
     * Resposta de Simulação para ambiente local sem API Keys
     */
    protected function generateSimulationPixResponse(Order $order): array
    {
        $copiaColaFake = "00020126580014br.gov.bcb.pix0136ruver-ingresso-simulacao-" . $order->id . "5204000053039865405" . number_format($order->valor_total, 2, '', '') . "5802BR5925RUVER PLATAFORMA DE INGRESSOS6009FLORIANOPOLIS62070503***6304E2CA";

        return [
            'success' => true,
            'payment_id' => 'sim_' . uniqid(),
            'qr_code_base64' => null, 
            'pix_copia_cola' => $copiaColaFake,
            'is_simulation' => true,
        ];
    }
}