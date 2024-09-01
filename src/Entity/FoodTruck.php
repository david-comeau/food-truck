<?php

declare(strict_types=1);

namespace App\Entity;

class FoodTruck
{
    private ?string $applicant = null;
    private ?string $fooditems = null;
    private ?string $address = null;
    private ?string $latitude = null;
    private ?string $longitude = null;

    public function getApplicant(): ?string
    {
        return $this->applicant;
    }

    public function setApplicant(string $applicant): void
    {
        $this->applicant = $applicant;
    }

    public function getFoodItems(): ?string
    {
        return $this->fooditems;
    }

    public function setFoodItems(string $fooditems): void
    {
        $this->fooditems = $fooditems;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): void
    {
        $this->address = $address;
    }

    public function getLatitude(): ?string
    {
        return $this->latitude;
    }

    public function setLatitude(string $latitude): void
    {
        $this->latitude = $latitude;
    }

    public function getLongitude(): ?string
    {
        return $this->longitude;
    }

    public function setLongitude(string $longitude): void
    {
        $this->longitude = $longitude;
    }
}
