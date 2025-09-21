<?php
// Programa A: Patrón Factory — auto-contenido. Ejecutar: php entrega_programa_A_factory.php [tipo]

interface Service {
    public function name(): string;
    public function slug(): string;
    public function priceCents(): int;
    public function durationMinutes(): int;
}
abstract class BaseService implements Service {
    public function __construct(
        protected string $name,
        protected int $priceCents,
        protected int $durationMinutes
    ) {}
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
class MassageService extends BaseService { public function __construct(){ parent::__construct('Masaje Descontracturante', 650000, 75); } }
class HairSpaService extends BaseService { public function __construct(){ parent::__construct('Hair Spa', 800000, 90); } }

class ServiceFactory {
    public static function make(string $type): Service {
        return match (strtolower($type)) {
            'facial' => new FacialService(),
            'massage' => new MassageService(),
            'hair', 'hairspa', 'hair-spa' => new HairSpaService(),
            default => throw new InvalidArgumentException("Tipo no soportado: {$type}"),
        };
    }
}

// --- Demo ---
$type = $argv[1] ?? 'facial';
$service = ServiceFactory::make($type);
echo "Factory → tipo: {$type}\n";
echo "- name: {$service->name()}\n";
echo "- slug: {$service->slug()}\n";
echo "- price_cents: {$service->priceCents()}\n";
echo "- duration_minutes: {$service->durationMinutes()}\n";