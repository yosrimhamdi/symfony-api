<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\InvoiceRepository;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: InvoiceRepository::class)]
#[
    ApiResource(
        attributes: [
            'pagination_enabled' => true,
            'pagination_items_per_page' => 20,
            'order' => [
                'amount' => 'DESC',
            ],
        ],
        normalizationContext: [
            'groups' => ['invoices_read'],
        ],
        denormalizationContext: [
            'disable_type_enforcement' => true,
        ],
        subresourceOperations: [
            'api_customers_invoices_get_subresource' => [
                'normalization_context' => [
                    'groups' => ['invoices_subresource'],
                ],
            ],
        ],
        itemOperations: [
            'GET',
            'PUT',
            'DELETE',
            'increment' => [
                'method' => 'POST',
                'path' => '/invoices/{id}/increment',
                'controller' =>
                    'App\Controller\InvoiceChronoIncrementController',
                'swagger_context' => [
                    'summary' => 'Increment an invoice',
                    'description' => 'Increment the chrono of an invoice',
                ],
            ],
        ]
    )
]

#[ApiFilter(OrderFilter::class, properties: ['amount', 'sentAt'])]
class Invoice
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['invoices_read', 'customers_read', 'invoices_subresource'])]
    private $id;

    #[ORM\Column(type: 'float')]
    #[Groups(['invoices_read', 'customers_read', 'invoices_subresource'])]
    #[Assert\NotBlank]
    #[
        Assert\Type(
            type: 'numeric',
            message: 'bad request expecting float found string.'
        )
    ]

    private $amount;

    #[Assert\NotBlank]
    #[Assert\DateTime]
    #[ORM\Column(type: 'datetime_immutable')]
    #[Groups(['invoices_read', 'customers_read', 'invoices_subresource'])]
    private $sentAt;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['invoices_read', 'customers_read', 'invoices_subresource'])]
    #[Assert\NotBlank]
    #[Assert\Choice(choices: ['PAID', 'CANCELLED', 'SENT'])]
    private $status;

    #[ORM\ManyToOne(targetEntity: Customer::class, inversedBy: 'invoices')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups('invoices_read')]
    #[Assert\NotBlank]
    private $customer;

    #[ORM\Column(type: 'integer')]
    #[Groups(['invoices_read', 'customers_read', 'invoices_subresource'])]
    #[Assert\NotBlank]
    #[Assert\Type(type: 'integer')]
    private $chrono;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount($amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getSentAt(): ?\DateTimeImmutable
    {
        return $this->sentAt;
    }

    public function setSentAt(\DateTimeImmutable $sentAt): self
    {
        $this->sentAt = $sentAt;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getCustomer(): ?Customer
    {
        return $this->customer;
    }

    public function setCustomer(?Customer $customer): self
    {
        $this->customer = $customer;

        return $this;
    }

    public function getChrono(): ?int
    {
        return $this->chrono;
    }

    public function setChrono($chrono): self
    {
        $this->chrono = $chrono;

        return $this;
    }
}
