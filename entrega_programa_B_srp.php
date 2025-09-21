<?php
// Programa B: SRP — auto-contenido. Ejecutar: php entrega_programa_B_srp.php

interface Service {
    public function name(): string;
    public function slug(): string;
    public function priceCents(): int;
    public function durationMinutes(): int;
}
abstract class BaseService implements Service {
    public function __construct(protected string $name, protected int $priceCents, protected int $durationMinutes) {}
    public function name(): string { return $this->name; }
    public function slug(): string {
        $s = strtolower(trim($this->name));
        $s = preg_replace('/[^a-z0-9]+/i', '-', $s);
        return trim($s, '-');
    }
    public function priceCents(): int { return $this->priceCents; }
    public function durationMinutes(): int { return $this->durationMinutes; }
}
class FacialService extends BaseService { public function __construct(){ parent::__construct('Limpieza Facial', 450000, 60); } }

class AppointmentCreator {
    public function create(Service $service, string $customerName, string $startsAtIso): array {
        return [
            'id' => uniqid('apt_', true),
            'customer_name' => $customerName,
            'service' => [
                'name' => $service->name(),
                'slug' => $service->slug(),
                'price_cents' => $service->priceCents(),
                'duration_minutes' => $service->durationMinutes(),
            ],
            'starts_at' => $startsAtIso,
        ];
    }
}
class PriceCalculator {
    public function finalPriceCents(Service $service, float $discountPercent = 0.0, float $taxPercent = 21.0): int {
        $base = $service->priceCents();
        if ($discountPercent > 0) $base = (int) round($base * (1 - $discountPercent/100));
        if ($taxPercent > 0)     $base = (int) round($base * (1 + $taxPercent/100));
        return $base;
    }
}
interface Notifier { public function notify(string $to, string $subject, string $message): void; }
class EmailNotifier implements Notifier {
    public function notify(string $to, string $subject, string $message): void {
        echo "[EMAIL] To: {$to} | Subject: {$subject}\nMessage: {$message}\n";
    }
}
class AppointmentService {
    public function __construct(
        private AppointmentCreator $creator,
        private PriceCalculator $priceCalculator,
        private Notifier $notifier
    ) {}
    public function book(Service $service, string $customerName, string $email, string $startsAtIso, float $discountPercent = 0.0, float $taxPercent = 21.0): array {
        $apt = $this->creator->create($service, $customerName, $startsAtIso);
        $final = $this->priceCalculator->finalPriceCents($service, $discountPercent, $taxPercent);
        $this->notifier->notify($email, "Reserva confirmada: {$service->name()}", "Hola {$customerName}, turno {$startsAtIso}. Total: {$final} centavos.");
        $apt['final_price_cents'] = $final;
        return $apt;
    }
}

// --- Demo ---
$service   = new FacialService();
$creator   = new AppointmentCreator();
$calc      = new PriceCalculator();
$notifier  = new EmailNotifier();
$app       = new AppointmentService($creator, $calc, $notifier);

$result = $app->book(
    service: $service,
    customerName: 'Ana Gómez',
    email: 'ana@example.com',
    startsAtIso: '2025-09-22T15:00:00-03:00',
    discountPercent: 10,
    taxPercent: 21
);

echo "SRP → Reserva creada:\n";
echo json_encode($result, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) . PHP_EOL;