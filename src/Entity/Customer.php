<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use App\Repository\CustomerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: CustomerRepository::class)]
#[
    ApiResource(
        normalizationContext: [
            'groups' => ['customers_read'],
        ]
    )
]

#[ApiFilter(SearchFilter::class)]
#[ApiFilter(OrderFilter::class)]
class Customer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['customers_read', 'invoices_read'])]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['customers_read', 'invoices_read'])]
    private $firstName;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['customers_read', 'invoices_read'])]
    private $lastName;

    #[Groups(['customers_read', 'invoices_read'])]
    #[ORM\Column(type: 'string', length: 255)]
    private $email;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Groups(['customers_read', 'invoices_read'])]
    private $company;

    #[ORM\OneToMany(mappedBy: 'customer', targetEntity: Invoice::class)]
    #[Groups(['customers_read'])]
    private $invoices;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'customers')]
    #[Groups(['customers_read', 'invoices_read'])]
    private $user;

    public function __construct()
    {
        $this->invoices = new ArrayCollection();
    }

    #[Groups('invoices_read')]
    public function getTotalMount(): float
    {
        return array_reduce(
            $this->invoices->toArray(),
            function ($total, $invoice) {
                return $total + $invoice->getAmount();
            },
            0
        );
    }

    #[Groups('invoices_read')]
    public function getUnpaidAmount(): float
    {
        return array_reduce(
            $this->invoices->toArray(),
            function ($total, $invoice) {
                if ($invoice->getStatus() !== 'PAID') {
                    return $total + 0;
                }

                return $total + $invoice->getAmount();
            },
            0
        );
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getCompany(): ?string
    {
        return $this->company;
    }

    public function setCompany(?string $company): self
    {
        $this->company = $company;

        return $this;
    }

    /**
     * @return Collection|Invoice[]
     */
    public function getInvoices(): Collection
    {
        return $this->invoices;
    }

    public function addInvoice(Invoice $invoice): self
    {
        if (!$this->invoices->contains($invoice)) {
            $this->invoices[] = $invoice;
            $invoice->setCustomer($this);
        }

        return $this;
    }

    public function removeInvoice(Invoice $invoice): self
    {
        if ($this->invoices->removeElement($invoice)) {
            // set the owning side to null (unless already changed)
            if ($invoice->getCustomer() === $this) {
                $invoice->setCustomer(null);
            }
        }

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }
}
