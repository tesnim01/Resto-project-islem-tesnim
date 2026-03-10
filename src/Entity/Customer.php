<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity]
#[ORM\Table(name: 'customer')]
class Customer extends User
{
    #[ORM\OneToMany(targetEntity: Order::class, mappedBy: 'customer', cascade: ['persist', 'remove'])]
    private Collection $orders;

    #[ORM\OneToMany(targetEntity: Review::class, mappedBy: 'customer', cascade: ['persist', 'remove'])]
    private Collection $reviews;

    public function __construct()
    {
        parent::__construct();
        $this->orders = new ArrayCollection();
        $this->reviews = new ArrayCollection();
    }

    /**
     * @return Collection<int, Order>
     */
    public function getOrders(): Collection
    {
        return $this->orders;
    }

    public function addOrder(Order $order): static
    {
        if (!$this->orders->contains($order)) {
            $this->orders->add($order);
            $order->setCustomer($this);
        }
        return $this;
    }

    public function removeOrder(Order $order): static
    {
        if ($this->orders->removeElement($order)) {
            if ($order->getCustomer() === $this) {
                $order->setCustomer(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, Review>
     */
    public function getReviews(): Collection
    {
        return $this->reviews;
    }

    public function addReview(Review $review): static
    {
        if (!$this->reviews->contains($review)) {
            $this->reviews->add($review);
            $review->setCustomer($this);
        }
        return $this;
    }

    public function removeReview(Review $review): static
    {
        if ($this->reviews->removeElement($review)) {
            if ($review->getCustomer() === $this) {
                $review->setCustomer(null);
            }
        }
        return $this;
    }

    public function getRoles(): array
    {
        return ['ROLE_CUSTOMER'];
    }

    public function makeOrder(): void
    {
        // Business logic for making an order
    }

    public function giveFeedback(): void
    {
        // Business logic for giving feedback
    }
}
