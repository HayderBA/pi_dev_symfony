<?php

namespace App\Entity;

use App\Repository\EvenementRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EvenementRepository::class)]
#[ORM\Table(name: 'evenement')]
class Evenement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'idEvenement')]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'Le titre est obligatoire')]
    #[Assert\Length(min: 3, max: 100)]
    private ?string $titre = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'date')]
    #[Assert\NotBlank(message: 'La date est obligatoire')]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'La localisation est obligatoire')]
    private ?string $localisation = null;

    #[ORM\Column(type: 'float', nullable: true, options: ['default' => 0])]
    private ?float $popularityScore = 0;

    #[ORM\Column(type: 'integer', nullable: true, options: ['default' => 0])]
    private ?int $predictedAttendance = 0;

    #[ORM\Column(type: 'float', nullable: true, options: ['default' => 50])]
    private ?float $dynamicPrice = 50;

    #[ORM\Column(type: 'float', nullable: true, options: ['default' => 50])]
    private ?float $basePrice = 50;

    #[ORM\Column(type: 'integer', nullable: true, options: ['default' => 100])]
    private ?int $maxCapacity = 100;

    #[ORM\Column(length: 50, nullable: true, options: ['default' => 'standard'])]
    private ?string $venueLayout = 'standard';

    #[ORM\Column(type: 'boolean', nullable: true, options: ['default' => false])]
    private ?bool $isDynamicPriceActive = false;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $seatCategories = null;

    #[ORM\OneToMany(mappedBy: 'evenement', targetEntity: Reservation::class, cascade: ['remove'])]
    private Collection $reservations;

    public function __construct()
    {
        $this->reservations = new ArrayCollection();
        $this->seatCategories = [
            'VIP' => [
                'rows' => ['A', 'B'],
                'label' => 'VIP',
                'color' => '#D4AF37',
                'hoverColor' => '#f6d365',
                'priceModifier' => 20,
            ],
            'Standard' => [
                'rows' => ['C', 'D', 'E', 'F'],
                'label' => 'Standard',
                'color' => '#cfeec6',
                'hoverColor' => '#6BBF59',
                'priceModifier' => 0,
            ],
            'Reduit' => [
                'rows' => ['G', 'H'],
                'label' => 'Reduit',
                'color' => '#d9ecff',
                'hoverColor' => '#7db7f0',
                'priceModifier' => -10,
            ],
        ];
    }

    public function getId(): ?int { return $this->id; }
    public function getTitre(): ?string { return $this->titre; }
    public function getDescription(): ?string { return $this->description; }
    public function getDate(): ?\DateTimeInterface { return $this->date; }
    public function getLocalisation(): ?string { return $this->localisation; }
    public function getPopularityScore(): ?float { return $this->popularityScore; }
    public function getPredictedAttendance(): ?int { return $this->predictedAttendance; }
    public function getDynamicPrice(): ?float { return $this->dynamicPrice ?? $this->basePrice ?? 0; }
    public function getBasePrice(): ?float { return $this->basePrice ?? 0; }
    public function getMaxCapacity(): ?int { return $this->maxCapacity ?? 0; }
    public function getVenueLayout(): ?string { return $this->venueLayout; }
    public function getReservations(): Collection { return $this->reservations; }
    public function getIsDynamicPriceActive(): ?bool { return $this->isDynamicPriceActive; }
    public function getSeatCategories(): ?array { return $this->seatCategories; }

    public function setTitre(string $titre): self { $this->titre = trim($titre); return $this; }
    public function setDescription(?string $description): self { $this->description = $description !== null ? trim($description) : null; return $this; }
    public function setDate(\DateTimeInterface $date): self { $this->date = $date; return $this; }
    public function setLocalisation(string $localisation): self { $this->localisation = trim($localisation); return $this; }
    public function setPopularityScore(?float $popularityScore): self { $this->popularityScore = $popularityScore; return $this; }
    public function setPredictedAttendance(?int $predictedAttendance): self { $this->predictedAttendance = $predictedAttendance; return $this; }
    public function setDynamicPrice(?float $dynamicPrice): self { $this->dynamicPrice = $dynamicPrice; return $this; }
    public function setBasePrice(?float $basePrice): self { $this->basePrice = $basePrice; return $this; }
    public function setMaxCapacity(?int $maxCapacity): self { $this->maxCapacity = $maxCapacity; return $this; }
    public function setVenueLayout(?string $venueLayout): self { $this->venueLayout = $venueLayout; return $this; }
    public function setIsDynamicPriceActive(?bool $isDynamicPriceActive): self { $this->isDynamicPriceActive = $isDynamicPriceActive; return $this; }
    public function setSeatCategories(?array $seatCategories): self { $this->seatCategories = $seatCategories; return $this; }

    public function getPrix(): ?float
    {
        return $this->getBasePrice();
    }

    public function setPrix(?float $prix): self
    {
        $this->basePrice = $prix;
        if ($this->dynamicPrice === null || $this->dynamicPrice <= 0) {
            $this->dynamicPrice = $prix;
        }

        return $this;
    }

    public function getCurrentReservationsCount(): int
    {
        return $this->reservations->count();
    }

    public function getOccupancyRate(): float
    {
        $capacity = max(1, (int) $this->getMaxCapacity());

        return round(($this->getCurrentReservationsCount() / $capacity) * 100, 2);
    }

    public function getStatus(): string
    {
        if (!$this->date) {
            return 'A venir';
        }

        $today = new \DateTimeImmutable('today');
        $eventDate = \DateTimeImmutable::createFromInterface($this->date);

        if ($eventDate < $today) {
            return 'Passe';
        }

        if ($eventDate->format('Y-m-d') === $today->format('Y-m-d')) {
            return 'Aujourd hui';
        }

        return 'A venir';
    }

    public function getStatusColor(): string
    {
        return match ($this->getStatus()) {
            'Passe' => 'danger',
            'Aujourd hui' => 'success',
            default => 'primary',
        };
    }

    public function getPlacesRestantes(): int
    {
        return max(0, (int) $this->getMaxCapacity() - $this->getCurrentReservationsCount());
    }

    public function isComplet(): bool
    {
        return $this->getPlacesRestantes() <= 0;
    }

    public function updateDynamicPrice(): void
    {
        $basePrice = (float) ($this->basePrice ?? 0);
        $occupancyRate = $this->getOccupancyRate();

        if ($occupancyRate > 70) {
            $this->dynamicPrice = $basePrice + 5;
            $this->isDynamicPriceActive = true;
            return;
        }

        if ($occupancyRate < 50) {
            $this->dynamicPrice = $basePrice;
            $this->isDynamicPriceActive = false;
            return;
        }

        if ($this->dynamicPrice === null || $this->dynamicPrice <= 0) {
            $this->dynamicPrice = $basePrice;
        }
    }

    public function isDynamicPriceActiveBool(): bool
    {
        return (bool) $this->isDynamicPriceActive;
    }

    public function getSeatCategory(string $seatNumber): string
    {
        $row = strtoupper(substr(trim($seatNumber), 0, 1));

        foreach ($this->getSeatCategories() ?? [] as $key => $configuration) {
            if (in_array($row, $configuration['rows'] ?? [], true)) {
                return $key;
            }
        }

        return 'Standard';
    }

    public function getSeatPrice(string $seatNumber): float
    {
        $this->updateDynamicPrice();

        $category = $this->getSeatCategory($seatNumber);
        $modifier = (float) (($this->getSeatCategories()[$category]['priceModifier'] ?? 0));

        return round(max(0, $this->getDynamicPrice() + $modifier), 2);
    }

    public function getCategoryColor(string $category): string
    {
        return (string) (($this->getSeatCategories()[$category]['color'] ?? '#cfeec6'));
    }

    public function getRowPrices(): array
    {
        $rows = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];
        $prices = [];

        foreach ($rows as $row) {
            $category = $this->getSeatCategory($row . '1');
            $prices[$row] = [
                'category' => $category,
                'label' => $this->getSeatCategories()[$category]['label'] ?? $category,
                'price' => $this->getSeatPrice($row . '1'),
                'color' => $this->getCategoryColor($category),
            ];
        }

        return $prices;
    }

    public function getPopularityBadge(): ?string
    {
        if ($this->isComplet()) {
            return 'Complet';
        }

        if ($this->getPlacesRestantes() < 20) {
            return '⚡ Presque complet';
        }

        if ($this->getOccupancyRate() > 50) {
            return '🔥 Tres demande';
        }

        return null;
    }

    public function getRecommendations(array $allEvents, int $limit = 2): array
    {
        $recommendations = [];

        foreach ($allEvents as $event) {
            if (!$event instanceof self || $event->getId() === $this->getId()) {
                continue;
            }

            $reason = null;

            if ($event->getLocalisation() === $this->getLocalisation()) {
                $reason = 'Meme lieu';
            } elseif (
                $event->getDate()
                && $this->getDate()
                && $event->getDate()->format('Y-m') === $this->getDate()->format('Y-m')
            ) {
                $reason = 'Meme mois';
            }

            if ($reason !== null) {
                $recommendations[] = [
                    'event' => $event,
                    'reason' => $reason,
                ];
            }
        }

        shuffle($recommendations);

        return array_slice($recommendations, 0, $limit);
    }

    public function getSeatsGrid(): array
    {
        $rows = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];
        $seatsPerRow = 10;
        $grid = [];

        $reservedSeats = [];
        foreach ($this->reservations as $reservation) {
            if ($reservation->getSeatNumber()) {
                $reservedSeats[] = $reservation->getSeatNumber();
            }
        }

        $maxRows = min(8, max(1, (int) ceil(($this->getMaxCapacity() ?: 1) / $seatsPerRow)));

        for ($i = 0; $i < $maxRows; ++$i) {
            $row = $rows[$i];
            $grid[$row] = [];

            for ($j = 1; $j <= $seatsPerRow; ++$j) {
                $seatNumber = $row . $j;
                $category = $this->getSeatCategory($seatNumber);
                $grid[$row][$j] = [
                    'number' => $seatNumber,
                    'isReserved' => in_array($seatNumber, $reservedSeats, true),
                    'category' => $category,
                    'price' => $this->getSeatPrice($seatNumber),
                    'color' => $this->getCategoryColor($category),
                ];
            }
        }

        return $grid;
    }

    public function getAvailableSeats(): array
    {
        $available = [];
        foreach ($this->getSeatsGrid() as $seats) {
            foreach ($seats as $seat) {
                if (!$seat['isReserved']) {
                    $available[] = $seat['number'];
                }
            }
        }

        return $available;
    }

    public function getNextAvailableSeat(): ?string
    {
        $available = $this->getAvailableSeats();

        return $available[0] ?? null;
    }

    public function getSolidarityReservationsCount(): int
    {
        $count = 0;

        foreach ($this->reservations as $reservation) {
            if ($reservation->hasSolidarityContribution()) {
                ++$count;
            }
        }

        return $count;
    }

    public function getSolidarityAmountCollected(): float
    {
        $total = 0.0;

        foreach ($this->reservations as $reservation) {
            $total += $reservation->getSolidarityAmount() ?? 0;
        }

        return round($total, 2);
    }

    public function getCalendarEvent(): array
    {
        $this->updateDynamicPrice();

        return [
            'id' => $this->id,
            'title' => $this->titre,
            'start' => $this->date?->format('Y-m-d'),
            'url' => '/evenement/' . $this->id,
            'location' => $this->localisation,
            'badge' => $this->getPopularityBadge(),
        ];
    }

    public function getGoogleCalendarLink(): string
    {
        $start = $this->date?->format('Ymd');
        $end = $this->date?->format('Ymd');

        return 'https://calendar.google.com/calendar/render?action=TEMPLATE&text=' . urlencode((string) $this->titre)
            . '&dates=' . $start . '/' . $end
            . '&location=' . urlencode((string) $this->localisation);
    }

    public function getIcalContent(): string
    {
        $start = $this->date?->format('Ymd');
        $end = $this->date?->format('Ymd');

        return "BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-//GrowMind//Evenement//FR\r\nBEGIN:VEVENT\r\nUID:event-{$this->id}@growmind.local\r\nDTSTART;VALUE=DATE:{$start}\r\nDTEND;VALUE=DATE:{$end}\r\nSUMMARY:{$this->titre}\r\nLOCATION:{$this->localisation}\r\nEND:VEVENT\r\nEND:VCALENDAR\r\n";
    }
}
